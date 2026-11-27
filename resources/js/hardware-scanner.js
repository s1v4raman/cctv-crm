import { BarcodeDetector as BarcodeDetectorPolyfill } from 'barcode-detector';

if (typeof window !== 'undefined' && !window.BarcodeDetectorPolyfill) {
    window.BarcodeDetectorPolyfill = BarcodeDetectorPolyfill;
}

const SCAN_FORMATS = ['ean_13', 'ean_8', 'code_128', 'code_39', 'qr_code', 'data_matrix'];

/**
 * Universal Barcode Sanitizer for Hardware Scanners & Mobile Cameras:
 * Strips non-printable ASCII control characters, ISO/IEC 15424 AIM symbology identifiers
 * (e.g. ]C1, ]c1, ]C0, ]e0, ]d1, ]Q3, etc.), GS1 AIs (like (21) for S/N), and common text prefixes.
 */
export function cleanBarcode(rawCode) {
    if (!rawCode) return '';
    let val = String(rawCode).trim();
    // 1. Strip non-printable ASCII control characters (FNC1, GS, RS, etc.) while preserving newlines/tabs
    val = val.replace(/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/g, '').trim();
    // 2. Strip ISO/IEC 15424 AIM Symbology Identifier (e.g. ]C1, ]c1, ]C0, ]e0, ]d1, ]Q3, etc.)
    val = val.replace(/^[\]\[][a-zA-Z0-9]{2}[:\s=_-]*/i, '').trim();
    // 3. Strip GS1 Application Identifier (21) for serial number if present
    val = val.replace(/^\(21\)[:\s=_-]*/i, '').trim();
    return val.trim();
}

if (typeof window !== 'undefined') {
    window.cleanBarcode = cleanBarcode;
}

/**
 * Thin forwarding helper (JS no longer duplicates server regex parsing).
 */
export function parseDeviceBarcode(rawCode) {
    const raw = cleanBarcode(rawCode);
    return { raw, format: null };
}

export default function hardwareScannerModalComponent(config = {}) {
    const detectIsMobile = () => (
        window.innerWidth < 768 ||
        /Android|iPhone|iPad|iPod|webOS|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)
    );

    return {
        modalId: config.modalId || 'hardwareScannerModal',
        routes: config.routes || {},
        isLocalEnv: !!config.isLocalEnv,

        isOpen: false,
        isMobile: detectIsMobile(),
        activeTab: 'mobile',
        gunInput: '',
        lookupInput: '',
        isLoading: false,
        hasResult: false,
        resultData: null,
        lastQueriedCode: '',
        lastScanMethod: 'mobile',
        lastScannedViaMobile: false,

        // Multi-code session & camera state
        collectedCodes: [],
        recentReadMap: {},
        scanSource: 'manual',
        useDirectCamera: false,
        facingMode: 'environment',
        videoDevices: [],
        selectedCameraId: '',
        singleScanAutoStop: true,
        cameraInterval: null,
        sessionDetector: null,
        mobileStream: null,
        videoTrack: null,
        torchSupported: false,
        torchOn: false,
        cameraError: '',
        cameraStatus: 'Point camera at CCTV barcode, QR, or S/N sticker...',

        // Mobile Phone Remote Scanner State (QR Sync)
        sessionToken: '',
        mobileUrl: '',
        mobileQrUrl: '',
        mobileStatus: 'waiting',
        pollInterval: null,
        customHostAndPort: '',

        // Review / Register form state
        registerSerial: '',
        serialCandidates: [],
        registerName: '',
        registerMac: '',
        possibleMacs: [],
        registerProductId: '',
        registerEan: '',
        registerRawScan: '',
        installationJobId: '',
        learnAliasToken: '',
        learnAliasType: 'model_code',

        // Customer / Project searchable select state
        targetQuery: '',
        targetResults: [],
        targetLoading: false,
        selectedTargetId: '',
        selectedTargetLabel: '',
        targetDropdownOpen: false,
        searchTargetTimeout: null,

        // Save status feedback
        saveError: '',
        saveSuccess: '',
        isSaving: false,

        initModal() {
            this.checkDevice();
            window.__hardwareScannerInstance = this;

            // Automatically clean AIM symbology identifiers (]C1, ]c1, etc.) on any input/paste
            this.$watch('lookupInput', (val) => {
                const c = cleanBarcode(val);
                if (c !== val) this.lookupInput = c;
            });
            this.$watch('registerSerial', (val) => {
                const c = cleanBarcode(val);
                if (c !== val) this.registerSerial = c;
            });
            this.$watch('gunInput', (val) => {
                const c = cleanBarcode(val);
                if (c !== val) this.gunInput = c;
            });

            window.addEventListener('open-hardware-scanner', (e) => {
                const detail = e.detail || {};
                this.openModal(detail.tab || 'auto', detail);
            });
        },

        cleanBarcode(str) {
            return cleanBarcode(str);
        },

        checkDevice() {
            this.isMobile = detectIsMobile();
        },

        openModal(tab = 'auto', context = {}) {
            this.isOpen = true;
            this.saveError = '';
            this.saveSuccess = '';
            this.checkDevice();
            document.body.classList.add('overflow-hidden');

            if (context && typeof context === 'object') {
                if (context.lead_id) {
                    this.selectedTargetId = String(context.lead_id);
                    if (context.site_label) {
                        this.selectedTargetLabel = context.site_label;
                        this.targetQuery = context.site_label;
                    }
                }
                if (context.work_order_id) {
                    this.installationJobId = String(context.work_order_id);
                }
            }

            const targetTab = (tab && ['mobile', 'scanner_gun', 'lookup', 'register'].includes(tab)) ? tab : 'mobile';
            this.switchTab(targetTab);
        },

        closeModal() {
            this.isOpen = false;
            document.body.classList.remove('overflow-hidden');
            this.stopCamera();
            if (this.pollInterval) {
                clearInterval(this.pollInterval);
                this.pollInterval = null;
            }
        },

        switchTab(t) {
            if (this.activeTab === 'mobile' && t !== 'mobile') {
                this.stopCamera();
            }
            this.activeTab = t;

            if (t === 'mobile') {
                if (this.isMobile) {
                    this.$nextTick(() => this.startMobileCamera(false));
                } else {
                    this.$nextTick(() => this.initMobileCam());
                }
            } else if (t === 'scanner_gun') {
                this.$nextTick(() => {
                    if (this.$refs.gunInput) this.$refs.gunInput.focus();
                });
            }
        },

        async getDetector() {
            if (this.sessionDetector) return this.sessionDetector;
            const DetectorClass = ('BarcodeDetector' in window) ? window.BarcodeDetector : BarcodeDetectorPolyfill;
            this.sessionDetector = new DetectorClass({ formats: SCAN_FORMATS });
            return this.sessionDetector;
        },

        async startMobileCamera(isUserGesture = true) {
            const video = document.getElementById(`${this.modalId}-phone-video`);
            const fallbackInput = document.getElementById(`${this.modalId}-phone-capture-fallback`);
            this.cameraError = '';
            if (!video) return;

            if (!window.isSecureContext && !['localhost', '127.0.0.1'].includes(window.location.hostname)) {
                this.cameraError = 'Live camera requires HTTPS or localhost. Please allow camera permissions or use Scanner Gun.';
                return;
            }

            if (!(navigator.mediaDevices && navigator.mediaDevices.getUserMedia)) {
                this.cameraError = 'Camera API unavailable in this browser. Please use Scanner Gun.';
                return;
            }

            try {
                this.stopCamera();

                // 1. Enumerate and filter out IR/Infrared cameras
                if (navigator.mediaDevices.enumerateDevices) {
                    try {
                        const devs = await navigator.mediaDevices.enumerateDevices();
                        this.videoDevices = devs.filter(d => d.kind === 'videoinput');
                        const isIR = (d) => /ir\b|infrared|hello|depth|face/i.test(d.label || '');
                        const nonIR = this.videoDevices.filter(d => !isIR(d));
                        const usable = nonIR.length > 0 ? nonIR : this.videoDevices;

                        if (!this.selectedCameraId && usable.length > 0) {
                            const preferred = this.facingMode === 'environment'
                                ? usable.find(d => /back|rear|environment/i.test(d.label || ''))
                                : usable.find(d => /webcam|integrated|rgb|color|hd|usb/i.test(d.label || ''));
                            this.selectedCameraId = (preferred || usable[0]).deviceId;
                        }
                    } catch (e) {}
                }

                let stream = null;
                const attempts = [];
                if (this.selectedCameraId) {
                    attempts.push({ video: { deviceId: { exact: this.selectedCameraId } }, audio: false });
                    attempts.push({ video: { deviceId: this.selectedCameraId }, audio: false });
                }
                attempts.push({ video: { facingMode: { ideal: this.facingMode || 'environment' } }, audio: false });
                attempts.push({ video: true, audio: false });

                let lastErr = null;
                for (const constraint of attempts) {
                    try {
                        stream = await navigator.mediaDevices.getUserMedia(constraint);
                        if (stream) break;
                    } catch (err) {
                        lastErr = err;
                    }
                }

                if (!stream) {
                    throw lastErr || new Error('Could not access camera');
                }

                // Inspect active track to ensure it is not an IR / Windows Hello depth camera
                const isIR = (d) => /ir\b|infrared|hello|depth|face/i.test(d.label || '');
                const activeTrack = stream.getVideoTracks()[0];
                const activeLabel = activeTrack ? (activeTrack.label || '') : '';
                if (navigator.mediaDevices.enumerateDevices) {
                    try {
                        const freshDevs = await navigator.mediaDevices.enumerateDevices();
                        this.videoDevices = freshDevs.filter(d => d.kind === 'videoinput');
                        if (isIR({ label: activeLabel }) && this.videoDevices.length > 0) {
                            const realColorCam = this.videoDevices.find(d => !isIR(d) && /webcam|integrated|rgb|color|hd|usb|back|rear/i.test(d.label || ''))
                                              || this.videoDevices.find(d => !isIR(d));
                            if (realColorCam && realColorCam.deviceId) {
                                try {
                                    const colorStream = await navigator.mediaDevices.getUserMedia({
                                        video: { deviceId: { exact: realColorCam.deviceId }, width: { ideal: 1280 }, height: { ideal: 720 } },
                                        audio: false
                                    });
                                    if (colorStream) {
                                        stream.getTracks().forEach(t => t.stop());
                                        stream = colorStream;
                                        this.selectedCameraId = realColorCam.deviceId;
                                    }
                                } catch (swapErr) {}
                            }
                        }
                    } catch (e) {}
                }

                this.mobileStream = stream;
                this.videoTrack = stream.getVideoTracks()[0] || null;
                this.torchSupported = !!(this.videoTrack && typeof this.videoTrack.getCapabilities === 'function' && this.videoTrack.getCapabilities().torch);
                this.torchOn = false;

                video.muted = true;
                video.defaultMuted = true;
                video.playsInline = true;
                video.setAttribute('playsinline', '');
                video.setAttribute('webkit-playsinline', '');
                video.setAttribute('autoplay', '');
                video.srcObject = stream;
                video.classList.remove('hidden');

                // Wait for loadeddata / canplay before calling play()
                await new Promise((resolve) => {
                    if (video.readyState >= 2 && video.videoWidth > 0) {
                        resolve();
                    } else {
                        const onReady = () => {
                            video.removeEventListener('loadeddata', onReady);
                            video.removeEventListener('canplay', onReady);
                            resolve();
                        };
                        video.addEventListener('loadeddata', onReady);
                        video.addEventListener('canplay', onReady);
                        setTimeout(resolve, 800);
                    }
                });

                try {
                    await video.play();
                } catch (playErr) {
                    console.warn('Video play error:', playErr);
                }
                this.cameraStatus = this.singleScanAutoStop
                    ? 'Point camera at barcode or QR code (auto-stops on scan)...'
                    : 'Scanning... Point at EAN, S/N, and MAC, then tap Done.';

                const detector = await this.getDetector();

                // ~8 reads per second (125ms)
                this.cameraInterval = setInterval(async () => {
                    if (!this.isOpen || this.activeTab !== 'mobile' || !this.mobileStream) {
                        this.stopCamera();
                        return;
                    }
                    if (video.readyState < 2) return;

                    try {
                        const barcodes = await detector.detect(video);
                        if (barcodes && barcodes.length > 0) {
                            let added = false;
                            for (const b of barcodes) {
                                if (b.rawValue && this.recordSessionCode(b.rawValue, b.format || null, 'camera')) {
                                    added = true;
                                }
                            }
                            if (added && this.singleScanAutoStop) {
                                await this.finishCameraSession();
                            }
                        }
                    } catch (e) {}
                }, 125);
            } catch (err) {
                if (err && (err.name === 'NotAllowedError' || err.name === 'PermissionDeniedError')) {
                    this.cameraError = 'Camera permission denied. Allow camera access in browser settings or use Scanner Gun.';
                } else {
                    this.cameraError = 'Unable to start camera: ' + (err?.message || 'Unknown error');
                }
            }
        },

        stopCamera() {
            if (this.cameraInterval) {
                clearInterval(this.cameraInterval);
                this.cameraInterval = null;
            }
            if (this.mobileStream) {
                this.mobileStream.getTracks().forEach(t => t.stop());
                this.mobileStream = null;
            }
            this.videoTrack = null;
            this.torchSupported = false;
            this.torchOn = false;
        },

        async toggleTorch() {
            if (!this.videoTrack || !this.torchSupported) return;
            try {
                const next = !this.torchOn;
                await this.videoTrack.applyConstraints({ advanced: [{ torch: next }] });
                this.torchOn = next;
            } catch (e) {}
        },

        async switchCameraFacing() {
            this.selectedCameraId = '';
            this.facingMode = this.facingMode === 'environment' ? 'user' : 'environment';
            await this.startMobileCamera(true);
        },

        async switchCameraDevice(deviceId) {
            this.selectedCameraId = deviceId;
            await this.startMobileCamera(true);
        },

        enableDirectCamera() {
            this.useDirectCamera = true;
            this.singleScanAutoStop = true;
            this.$nextTick(() => this.startMobileCamera(true));
        },

        disableDirectCamera() {
            this.useDirectCamera = false;
            this.stopCamera();
            this.$nextTick(() => this.initMobileCam());
        },

        recordSessionCode(rawValue, format = null, source = 'camera') {
            const raw = cleanBarcode(rawValue);
            if (!raw) return false;

            const key = `${format || 'unknown'}::${raw}`;
            const now = Date.now();
            if (this.recentReadMap[key] && (now - this.recentReadMap[key]) < 1500) {
                return false;
            }
            this.recentReadMap[key] = now;

            this.beep();
            if (navigator.vibrate) {
                navigator.vibrate([100, 40, 100]);
            }

            const alreadyExists = this.collectedCodes.some(c => c.raw === raw && (c.format || null) === (format || null));
            if (!alreadyExists) {
                this.collectedCodes.push({
                    id: `${now}-${Math.random().toString(36).slice(2, 7)}`,
                    raw,
                    format: format || null,
                    source,
                });
            }

            this.scanSource = source;
            this.lastScanMethod = source === 'scanner_gun' ? 'scanner_gun' : 'mobile';
            this.lastScannedViaMobile = source !== 'scanner_gun';
            return true;
        },

        removeCollectedCode(idx) {
            this.collectedCodes.splice(idx, 1);
        },

        async finishCameraSession() {
            this.stopCamera();
            if (this.collectedCodes.length === 0) {
                this.switchTab('register');
                return;
            }
            await this.resolveSessionCodes();
            this.switchTab('register');
        },

        async resolveSessionCodes() {
            if (this.collectedCodes.length === 0) return;

            this.isLoading = true;
            this.saveError = '';
            try {
                const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                const url = this.routes.lookupPost || '/api/equipment/lookup-serial';
                const res = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrf,
                    },
                    body: JSON.stringify({
                        scans: this.collectedCodes.map(c => ({ raw: c.raw, format: c.format })),
                    }),
                });
                const data = await res.json();
                this.applyLookupResult(data);
            } catch (e) {
                this.resultData = { found: false, error: e.message };
                this.hasResult = true;
            } finally {
                this.isLoading = false;
            }
        },

        applyLookupResult(data) {
            this.resultData = data;
            this.hasResult = true;
            if (!data) return;

            this.serialCandidates = (Array.isArray(data.serial_candidates) ? data.serial_candidates : []).map(cleanBarcode).filter(Boolean);
            this.registerSerial = cleanBarcode(data.serial || data.serial_number || this.serialCandidates[0] || '');
            this.registerMac = data.mac || data.mac_address || '';
            this.possibleMacs = Array.isArray(data.possible_mac) ? data.possible_mac : [];
            this.registerEan = data.ean || '';
            this.registerRawScan = JSON.stringify(this.collectedCodes);

            if (data.product) {
                const brandPrefix = data.product.brand ? `${data.product.brand} ` : '';
                const mdlSuffix = data.product.model_no ? ` (${data.product.model_no})` : '';
                this.registerName = `${brandPrefix}${data.product.name}${mdlSuffix}`.trim();
                this.registerProductId = String(data.product.id);
                this.learnAliasToken = '';
            } else {
                this.registerName = data.equipment_name || '';
                this.registerProductId = data.product_id ? String(data.product_id) : '';
            }
        },

        confirmPossibleMac(macValue) {
            this.registerMac = macValue;
            this.possibleMacs = this.possibleMacs.filter(m => m !== macValue);
        },

        selectSerialCandidate(sn) {
            this.registerSerial = cleanBarcode(sn);
        },

        selectCandidate(cand) {
            if (!cand) return;
            const brandPrefix = cand.brand ? `${cand.brand} ` : '';
            const mdlSuffix = cand.model_no ? ` (${cand.model_no})` : '';
            this.registerProductId = String(cand.id || '');
            this.registerName = `${brandPrefix}${cand.name}${mdlSuffix}`.trim();

            if (this.registerEan) {
                this.learnAliasToken = this.registerEan;
                this.learnAliasType = 'ean';
            } else if (this.resultData && Array.isArray(this.resultData.unknown_tokens) && this.resultData.unknown_tokens.length > 0) {
                this.learnAliasToken = this.resultData.unknown_tokens[0];
                this.learnAliasType = 'model_code';
            }
        },

        async processGunScan() {
            const code = cleanBarcode(this.gunInput);
            if (!code) {
                if (this.$refs.gunInput) this.$refs.gunInput.focus();
                return;
            }
            this.recordSessionCode(code, null, 'scanner_gun');
            this.lastQueriedCode = code;
            this.lookupInput = code;
            this.gunInput = '';
            await this.resolveSessionCodes();
            if (this.registerSerial && this.registerMac) {
                this.switchTab('register');
            }
        },

        async executeLookup() {
            const code = cleanBarcode(this.lookupInput);
            if (!code) return;
            this.lookupInput = code;
            this.lastQueriedCode = code;
            this.recordSessionCode(code, null, this.scanSource || 'manual');
            await this.resolveSessionCodes();
        },

        async renderQrDataUrl(url) {
            if (!url) return;
            try {
                if (window.QRCode && typeof window.QRCode.toDataURL === 'function') {
                    this.mobileQrUrl = await window.QRCode.toDataURL(url, { width: 240, margin: 1 });
                }
            } catch (e) {}
        },

        async initMobileCam() {
            if (this.pollInterval) {
                clearInterval(this.pollInterval);
                this.pollInterval = null;
            }

            // Reuse active pairing if already created in this session
            if (!this.sessionToken) {
                this.mobileStatus = 'waiting';
                this.mobileQrUrl = '';
                try {
                    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                    const pairUrl = this.routes.createPairing || '/scanner/pairings';
                    const pairRes = await fetch(pairUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrf,
                        },
                        body: JSON.stringify({}),
                    });
                    if (!pairRes.ok) return;
                    const pairing = await pairRes.json();
                    this.sessionToken = pairing.token;
                    const baseUrl = pairing.url || `${window.location.origin}/mobile-scanner/${this.sessionToken}`;
                    if (!this.customHostAndPort) {
                        try {
                            this.customHostAndPort = new URL(baseUrl).host;
                        } catch (e) {
                            this.customHostAndPort = window.location.host;
                        }
                    }
                    const sep = baseUrl.includes('?') ? '&' : '?';
                    this.mobileUrl = `${baseUrl}${sep}target=hardware&label=Hardware+Barcode`;
                    await this.renderQrDataUrl(this.mobileUrl);
                } catch (err) {
                    return;
                }
            }

            const token = this.sessionToken;
            this.pollInterval = setInterval(async () => {
                if (!this.isOpen) return;
                try {
                    const res = await fetch(`/api/mobile-scanner/poll/${token}`, {
                        headers: { 'Accept': 'application/json' },
                    });
                    if (!res.ok) return;
                    const data = await res.json();
                    if (data && data.status === 'scanned') {
                        const items = Array.isArray(data.codes) && data.codes.length > 0
                            ? data.codes
                            : (data.code ? [{ code: data.code }] : []);
                        if (items.length === 0) return;

                        this.mobileStatus = 'scanned';
                        for (const item of items) {
                            const scannedCode = cleanBarcode(item.code);
                            if (scannedCode) {
                                this.recordSessionCode(scannedCode, item.format || null, 'phone_pairing');
                            }
                        }
                        await this.resolveSessionCodes();
                    } else if (data && data.status === 'connected') {
                        this.mobileStatus = 'connected';
                    }
                } catch (e) {}
            }, 1000);
        },

        async updateQrUrl() {
            const host = (this.customHostAndPort || '').trim().replace(/^https?:\/\//i, '');
            if (!host || !this.sessionToken) return;
            this.customHostAndPort = host;
            this.mobileUrl = `${window.location.protocol}//${host}/mobile-scanner/${this.sessionToken}?target=hardware&label=Hardware+Barcode`;
            await this.renderQrDataUrl(this.mobileUrl);
        },

        prefillAndRegister() {
            this.switchTab('register');
        },

        async searchTargets(q = '') {
            this.targetLoading = true;
            try {
                const params = new URLSearchParams();
                if (q && q.trim() !== '') params.set('q', q.trim());
                const url = this.routes.targets || '/scanner/targets';
                const res = await fetch(`${url}?${params.toString()}`, {
                    headers: { 'Accept': 'application/json' },
                });
                if (res.ok) {
                    const data = await res.json();
                    this.targetResults = Array.isArray(data.items) ? data.items : [];
                    this.$nextTick(() => {
                        const sel = document.getElementById(`hw_scanner_lead_id_${this.modalId}`);
                        if (sel && sel._crmSearchable && typeof sel._crmSearchable.syncOptions === 'function') {
                            sel._crmSearchable.syncOptions();
                        }
                    });
                }
            } catch (e) {
                this.targetResults = [];
            } finally {
                this.targetLoading = false;
            }
        },

        onTargetSearchInput() {
            this.targetDropdownOpen = true;
            clearTimeout(this.searchTargetTimeout);
            this.searchTargetTimeout = setTimeout(() => {
                this.searchTargets(this.targetQuery);
            }, 200);
        },

        selectTarget(item) {
            if (!item) return;
            this.selectedTargetId = String(item.id);
            this.selectedTargetLabel = item.label;
            this.targetQuery = item.label;
            this.targetDropdownOpen = false;
        },

        async saveAndScanNext() {
            this.saveError = '';
            this.saveSuccess = '';
            if (!this.selectedTargetId || !this.registerSerial || !this.registerName) {
                this.saveError = 'Please complete Serial Number, Model, and Customer Site before saving.';
                return;
            }

            this.isSaving = true;
            try {
                const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                const form = document.getElementById(`${this.modalId}-register-form`);
                const formData = form ? new FormData(form) : new FormData();
                formData.set('serial_number', this.registerSerial);
                formData.set('mac_address', this.registerMac || '');
                formData.set('equipment_name', this.registerName);
                formData.set('product_id', this.registerProductId || '');
                formData.set('ean', this.registerEan || '');
                formData.set('scan_source', this.scanSource || 'manual');
                formData.set('raw_scan', JSON.stringify(this.collectedCodes));
                formData.set('lead_id', this.selectedTargetId);
                if (this.installationJobId) {
                    formData.set('installation_job_id', this.installationJobId);
                }
                if (this.learnAliasToken) {
                    formData.set('learn_alias_token', this.learnAliasToken);
                    formData.set('learn_alias_type', this.learnAliasType);
                }

                const storeUrl = this.routes.store || '/equipment';
                const res = await fetch(storeUrl, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrf,
                    },
                    body: formData,
                });

                const data = await res.json();
                if (!res.ok) {
                    const errs = data.errors ? Object.values(data.errors).flat().join(' ') : (data.message || 'Validation failed.');
                    this.saveError = errs;
                    return;
                }

                this.saveSuccess = `Saved ${this.registerSerial}. Ready for next device!`;
                this.resetDeviceFieldsForNextScan();
                this.switchTab(this.lastScanMethod === 'scanner_gun' ? 'scanner_gun' : 'mobile');
            } catch (e) {
                this.saveError = e.message || 'Unable to save device.';
            } finally {
                this.isSaving = false;
            }
        },

        resetDeviceFieldsForNextScan() {
            this.collectedCodes = [];
            this.recentReadMap = {};
            this.hasResult = false;
            this.resultData = null;
            this.registerSerial = '';
            this.serialCandidates = [];
            this.registerMac = '';
            this.possibleMacs = [];
            this.registerEan = '';
            this.registerName = '';
            this.registerProductId = '';
            this.registerRawScan = '';
            this.learnAliasToken = '';
            this.lookupInput = '';
            this.gunInput = '';
        },

        beep() {
            try {
                const ctx = new (window.AudioContext || window.webkitAudioContext)();
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();
                osc.connect(gain);
                gain.connect(ctx.destination);
                osc.frequency.value = 880;
                gain.gain.value = 0.15;
                osc.start();
                setTimeout(() => { osc.stop(); ctx.close(); }, 140);
            } catch (e) {}
        },

        async uploadImage(event) {
            const file = event.target.files[0];
            if (!file) return;
            event.target.value = '';

            const img = new Image();
            const objectUrl = URL.createObjectURL(file);
            img.onload = async () => {
                try {
                    const detector = await this.getDetector();
                    const barcodes = await detector.detect(img);
                    if (barcodes && barcodes.length > 0) {
                        for (const b of barcodes) {
                            if (b.rawValue) {
                                this.recordSessionCode(b.rawValue, b.format || null, 'camera');
                            }
                        }
                        await this.resolveSessionCodes();
                    } else {
                        this.cameraError = 'Could not detect barcode clearly. Try again.';
                    }
                } catch (e) {
                    this.cameraError = 'Failed to decode image.';
                } finally {
                    URL.revokeObjectURL(objectUrl);
                }
            };
            img.src = objectUrl;
        },

        /**
         * Cleanup candidate: kept unused so legacy callers do not error.
         */
        inferEquipmentName() {
            return '';
        },
    };
}
