<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <a href="{{ route('projects.index') }}" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                </a>
                <h2 class="text-xl font-extrabold text-slate-900 dark:text-white font-heading tracking-tight">Project Creation Wizard</h2>
                <span class="text-xs font-mono font-bold px-2 py-0.5 rounded bg-blue-50 dark:bg-blue-950 text-blue-600 dark:text-blue-400 border border-blue-200 dark:border-blue-800">
                    {{ $nextCode }}
                </span>
            </div>
            <span class="text-xs text-slate-400">3-Step Guided Setup</span>
        </div>
    </x-slot>

    <script>
        window.SAVED_PRODUCTS_MAP = @json($savedProductsMap ?? []);
        window.SAVED_VENDORS_SET = @json($savedVendorsSet ?? []);
    </script>

    @php
        $preselectedSite = null;
        $siteIdToLoad = old('site_id', request('site_id'));
        if ($siteIdToLoad) {
            $preselectedSite = \App\Models\Site::find($siteIdToLoad);
        }
    @endphp

    <div class="py-6 px-4 sm:px-6 lg:px-8 max-w-5xl mx-auto"
         x-data="{
             step: {{ (int) old('current_step', request('step', session('new_technician_id') ? 2 : ($errors->any() ? 3 : ($preselectedSite ? 2 : 1)))) }},
             selectedSite: @json($preselectedSite),
             siteSearch: '',
             siteResults: [],
             siteModalOpen: false,
             newSite: { name: '', client_name: '', client_phone: '', address: '', city: 'Coimbatore', latitude: '', longitude: '' },
             
             goStep(targetStep) {
                 const s = parseInt(targetStep, 10);
                 if (!isNaN(s) && s >= 1 && s <= 3) {
                     this.step = s;
                 }
             },

             // Step 2 Info
             title: @js(old('title', '')),
             project_type: '{{ old('project_type', 'hardware_cctv') }}',
             lead_technician_id: '{{ session('new_technician_id', old('lead_technician_id', '')) }}',
             budget: @js(old('budget', '')),
             po_number: @js(old('po_number', '')),
             start_date: '{{ old('start_date', now()->toDateString()) }}',
             deadline: '{{ old('deadline', '') }}',
             priority: '{{ old('priority', 'medium') }}',
             description: @js(old('description', '')),

             // Dynamic Templates by Project Type
             templates: {
                 hardware_cctv: {
                     nameSuffix: 'CCTV Surveillance & Security',
                     requirements: {
                         cctv_count: 4,
                         nvr_channels: '8',
                         cabling_metres: 150,
                         power_supply: '8-Port PoE Gigabit Switch',
                         display_screen: '32-inch Full HD LED',
                         storage_hdd: '4TB Surveillance SATA HDD'
                     },
                     materials: [
                         { item_name: 'Dome IP Camera 4MP IR', quantity: 2, unit: 'nos', source: 'warehouse', shop_name: '', notes: 'Indoor dome' },
                         { item_name: 'Bullet IP Camera 4MP Outdoor', quantity: 2, unit: 'nos', source: 'warehouse', shop_name: '', notes: 'Outdoor weatherproof' },
                         { item_name: '8-Channel 4K NVR', quantity: 1, unit: 'nos', source: 'warehouse', shop_name: '', notes: 'H.265+' },
                         { item_name: '4TB Surveillance HDD', quantity: 1, unit: 'nos', source: 'warehouse', shop_name: '', notes: 'Seagate SkyHawk' },
                         { item_name: 'CAT6 UTP Cable Roll (305m)', quantity: 1, unit: 'roll', source: 'warehouse', shop_name: '', notes: 'Pure copper' },
                         { item_name: '8-Port PoE Switch Gigabit', quantity: 1, unit: 'nos', source: 'warehouse', shop_name: '', notes: '120W Total PoE' },
                         { item_name: 'PVC Conduit Pipes 20mm', quantity: 15, unit: 'nos', source: 'local_purchase', shop_name: 'Local Electricals', notes: 'Heavy gauge' }
                     ]
                 },
                 networking: {
                     nameSuffix: 'Network Cabling & Wi-Fi Systems',
                     requirements: {
                         network_drops: 24,
                         wifi_aps: 3,
                         rack_size: '9U Wall Mount Server Rack',
                         switch_spec: '24-Port Gigabit Managed PoE+ Switch',
                         cabling_metres: 450,
                         router_gateway: 'Dual-WAN Gigabit Security Gateway Router'
                     },
                     materials: [
                         { item_name: 'Dual-Band Wi-Fi 6 Access Point', quantity: 3, unit: 'nos', source: 'warehouse', shop_name: '', notes: 'Ceiling mount PoE' },
                         { item_name: '24-Port Gigabit Managed Switch', quantity: 1, unit: 'nos', source: 'warehouse', shop_name: '', notes: 'L2+ managed' },
                         { item_name: '9U Wall Mount Network Server Rack', quantity: 1, unit: 'nos', source: 'warehouse', shop_name: '', notes: 'With PDU & tray' },
                         { item_name: '24-Port Cat6 Patch Panel', quantity: 1, unit: 'nos', source: 'warehouse', shop_name: '', notes: '110 punch down' },
                         { item_name: 'Cat6 UTP 305m Cable Roll', quantity: 2, unit: 'roll', source: 'warehouse', shop_name: '', notes: 'Solid copper' },
                         { item_name: 'Dual Faceplate + Cat6 Keystone I/O', quantity: 12, unit: 'set', source: 'warehouse', shop_name: '', notes: 'Workstation drops' },
                         { item_name: '1-Metre Cat6 Patch Cords (Molded)', quantity: 24, unit: 'nos', source: 'warehouse', shop_name: '', notes: 'Rack patching' }
                     ]
                 },
                 software_web: {
                     nameSuffix: 'Webpage & Web Application',
                     requirements: {
                         app_architecture: 'Full-Stack Responsive Web App',
                         tech_stack: 'PHP Laravel + Tailwind + MySQL',
                         core_modules: 'Auth, Role Permissions, CRM, Billing & REST API',
                         hosting_infra: 'Cloud VPS (Ubuntu / Nginx / SSL)',
                         domain_ssl: 'Custom Domain + Cloudflare Wildcard SSL',
                         delivery_timeline: '4 Sprints (Design, Core, Integration, QA)'
                     },
                     materials: [
                         { item_name: 'UI/UX Wireframes & Interactive Prototype', quantity: 1, unit: 'set', source: 'warehouse', shop_name: '', notes: 'Figma mockups' },
                         { item_name: 'Responsive Frontend Web Application Module', quantity: 1, unit: 'nos', source: 'warehouse', shop_name: '', notes: 'Mobile & desktop ready' },
                         { item_name: 'Backend Core Engine & Database Schema', quantity: 1, unit: 'nos', source: 'warehouse', shop_name: '', notes: 'REST API & database' },
                         { item_name: 'User Authentication & Role Permissions Module', quantity: 1, unit: 'nos', source: 'warehouse', shop_name: '', notes: 'Admin, staff, client portals' },
                         { item_name: 'Third-Party Payment & SMS Gateway Integration', quantity: 1, unit: 'nos', source: 'warehouse', shop_name: '', notes: 'API webhooks' },
                         { item_name: 'Cloud Production Server Deployment & SSL', quantity: 1, unit: 'nos', source: 'warehouse', shop_name: '', notes: 'Automated backups' },
                         { item_name: 'User Manual & Technical Handover Training', quantity: 1, unit: 'nos', source: 'warehouse', shop_name: '', notes: 'Admin training session' }
                     ]
                 },
                 access_control: {
                     nameSuffix: 'Biometric Access Control & Intercom',
                     requirements: {
                         doors_count: 2,
                         auth_method: 'Face Recognition + Fingerprint + RFID Card',
                         lock_type: '600lbs Heavy-Duty Electromagnetic (EM) Lock',
                         exit_device: 'No-Touch Infrared Exit Sensor Switch',
                         controller_type: 'Standalone IP Biometric Terminal',
                         power_backup: '12V 5A Power Supply with 7Ah Battery'
                     },
                     materials: [
                         { item_name: 'Face & Fingerprint Access Terminal', quantity: 2, unit: 'nos', source: 'warehouse', shop_name: '', notes: 'Optical sensor' },
                         { item_name: '600 lbs Electromagnetic Lock (EM Lock)', quantity: 2, unit: 'nos', source: 'warehouse', shop_name: '', notes: 'With ZL bracket' },
                         { item_name: 'No-Touch Infrared Exit Button', quantity: 2, unit: 'nos', source: 'warehouse', shop_name: '', notes: 'Stainless steel plate' },
                         { item_name: '12V 5A Access Control Power Supply Unit', quantity: 1, unit: 'nos', source: 'warehouse', shop_name: '', notes: 'UPS enclosure' },
                         { item_name: '12V 7Ah Sealed Lead Acid Battery', quantity: 1, unit: 'nos', source: 'warehouse', shop_name: '', notes: '4-hour backup' },
                         { item_name: 'RFID Proximity Smart Cards (125kHz)', quantity: 50, unit: 'nos', source: 'warehouse', shop_name: '', notes: 'Printed keycards' },
                         { item_name: '4-Core Shielded Access Cable (100m)', quantity: 1, unit: 'roll', source: 'warehouse', shop_name: '', notes: 'Lock & reader wiring' }
                     ]
                 },
                 hardware_attendance: {
                     nameSuffix: 'Terminal Camera & Attendance System',
                     hardware_specs: {
                         terminal_count: 2,
                         camera_count: 4,
                         device_brand: 'Hikvision MinMoe / ZKTeco ProFace X',
                         terminal_ip: '192.168.1.50 - 192.168.1.55',
                         attendance_sync_mode: 'face_recognition'
                     },
                     requirements: {
                         terminal_users: 500,
                         communication: 'TCP/IP Ethernet + Wi-Fi + USB Drive',
                         hrms_software: 'Desktop Software + Automated Excel/Payroll Sync',
                         mounting_location: 'Main Reception / Employee Entry Turnstile',
                         power_battery: 'Inbuilt Rechargeable Lithium Backup Battery'
                     },
                     materials: [
                         { item_name: 'AI Face & Fingerprint Attendance Terminal', quantity: 1, unit: 'nos', source: 'warehouse', shop_name: '', notes: 'High-speed matching' },
                         { item_name: 'Heavy-Duty Metallic Wall Mounting Enclosure', quantity: 1, unit: 'nos', source: 'warehouse', shop_name: '', notes: 'Tamper proof' },
                         { item_name: 'Regulated 12V DC Linear Power Adapter', quantity: 1, unit: 'nos', source: 'warehouse', shop_name: '', notes: 'Surge protected' },
                         { item_name: 'CAT6 High-Speed Network Cable', quantity: 20, unit: 'mtr', source: 'warehouse', shop_name: '', notes: 'Switch link' },
                         { item_name: 'Attendance Management & Payroll Desktop Software License', quantity: 1, unit: 'nos', source: 'warehouse', shop_name: '', notes: 'Multi-user license' }
                     ]
                 },
                 hybrid: {
                     nameSuffix: 'Hybrid Smart Integrated System',
                     hardware_specs: {
                         terminal_count: 4,
                         camera_count: 16,
                         device_brand: 'Hikvision AI + Uniview 4K',
                         terminal_ip: '172.16.1.10 - 172.16.1.25',
                         attendance_sync_mode: 'face_recognition'
                     },
                     requirements: {
                         hardware_endpoints: 16,
                         software_platform: 'Central Web Dashboard & Mobile Alert App',
                         edge_gateway: 'Edge IoT Controller Unit',
                         server_storage: 'On-Premises High Availability Micro-Server',
                         alert_channels: 'Real-Time WhatsApp, Email & SMS Alerts'
                     },
                     materials: [
                         { item_name: 'IoT Edge Gateway Multi-Protocol Hub', quantity: 1, unit: 'nos', source: 'warehouse', shop_name: '', notes: 'Industrial grade' },
                         { item_name: 'Smart Surveillance & Environmental Sensors', quantity: 8, unit: 'nos', source: 'warehouse', shop_name: '', notes: 'Sensor array' },
                         { item_name: 'Managed Industrial PoE Switch', quantity: 1, unit: 'nos', source: 'warehouse', shop_name: '', notes: 'DIN-rail mount' },
                         { item_name: 'Custom Web Portal & Central Dashboard License', quantity: 1, unit: 'nos', source: 'warehouse', shop_name: '', notes: 'Web & mobile UI' },
                         { item_name: 'Micro-Server Compute Unit with RAID Storage', quantity: 1, unit: 'nos', source: 'warehouse', shop_name: '', notes: 'Local logging' }
                     ]
                 }
             },

             req: {
                 cctv_count: 4,
                 nvr_channels: '8',
                 cabling_metres: 150,
                 power_supply: '8-Port PoE Gigabit Switch',
                 display_screen: '32-inch Full HD LED',
                 storage_hdd: '4TB Surveillance SATA HDD',
                 special_requirements: ''
             },

             hardware_specs: {
                 terminal_count: 0,
                 camera_count: 0,
                 device_brand: '',
                 terminal_ip: '',
                 attendance_sync_mode: 'face_recognition'
             },

             materials: @json(old('materials', [])),
             oldRequirements: @json(old('requirements', [])),
             oldHardwareSpecs: @json(old('hardware_specs', [])),

             init() {
                 if (this.materials && this.materials.length > 0) {
                     // Materials were already restored from previous submission
                     if (this.oldRequirements && Object.keys(this.oldRequirements).length > 0) {
                         this.req = Object.assign({}, this.req, this.oldRequirements);
                     }
                     if (this.oldHardwareSpecs && Object.keys(this.oldHardwareSpecs).length > 0) {
                         this.hardware_specs = Object.assign({}, this.hardware_specs, this.oldHardwareSpecs);
                     }
                 } else {
                     this.applyTemplate(this.project_type, false);
                 }
             },

             applyTemplate(type, forceTitle = false) {
                 const tpl = this.templates[type] || this.templates['hardware_cctv'];
                 this.req = Object.assign({}, tpl.requirements, { special_requirements: this.req.special_requirements || '' });
                 this.hardware_specs = Object.assign({
                     terminal_count: 0,
                     camera_count: 0,
                     device_brand: '',
                     terminal_ip: '',
                     attendance_sync_mode: 'face_recognition'
                 }, tpl.hardware_specs || {});
                 this.materials = JSON.parse(JSON.stringify(tpl.materials));
                 if (forceTitle || !this.title || this.title.includes('Surveillance') || this.title.includes('System') || this.title.includes('Cabling') || this.title.includes('Application')) {
                     if (this.selectedSite) {
                         this.title = `${this.selectedSite.name} - ${tpl.nameSuffix}`;
                     } else {
                         this.title = `${tpl.nameSuffix}`;
                     }
                 }
             },

             addMaterialRow() {
                 this.materials.push({ item_name: '', quantity: 1, unit: 'nos', source: 'warehouse', shop_name: '', notes: '', is_custom: true });
             },
             removeMaterialRow(index) {
                 this.materials.splice(index, 1);
             },
             normalizeKey(str) {
                 return (str || '').toString().trim().toLowerCase().replace(/\s+/g, ' ');
             },
             isNewProduct(name) {
                 const key = this.normalizeKey(name);
                 if (!key) return false;
                 return !window.SAVED_PRODUCTS_MAP || !(key in window.SAVED_PRODUCTS_MAP);
             },
             isNewVendor(name) {
                 const key = this.normalizeKey(name);
                 if (!key) return false;
                 return !window.SAVED_VENDORS_SET || !window.SAVED_VENDORS_SET.includes(key);
             },
             onProductSelect(mat) {
                 const key = this.normalizeKey(mat.item_name);
                 const match = window.SAVED_PRODUCTS_MAP ? window.SAVED_PRODUCTS_MAP[key] : null;
                 if (match && match.unit) {
                     const u = match.unit.toLowerCase();
                     if (['nos','mtr','box','pkt','roll','set'].includes(u)) {
                         mat.unit = u;
                     }
                 }
             },

             async searchSites() {
                 if (this.siteSearch.length < 1) {
                     this.siteResults = [];
                     return;
                 }
                 try {
                     const res = await fetch(`/sites/search?q=${encodeURIComponent(this.siteSearch)}`);
                     this.siteResults = await res.json();
                 } catch (e) {}
             },
             pickSite(site) {
                 this.selectedSite = site;
                 this.siteResults = [];
                 this.siteSearch = '';
                 if (!this.title) {
                     this.title = `${site.name} - CCTV Surveillance System`;
                 }
             },
             async createSite() {
                 if (!this.newSite.name || !this.newSite.address) {
                     alert('Site Name and Address are required.');
                     return;
                 }
                 try {
                     const res = await fetch('{{ route('sites.store') }}', {
                         method: 'POST',
                         headers: {
                             'Content-Type': 'application/json',
                             'Accept': 'application/json',
                             'X-CSRF-TOKEN': '{{ csrf_token() }}'
                         },
                         body: JSON.stringify(this.newSite)
                     });
                     const data = await res.json();
                     if (data.success && data.site) {
                         this.pickSite(data.site);
                         this.siteModalOpen = false;
                     }
                 } catch (e) {
                     alert('Error creating site');
                 }
             },
             draftSaved: false,
             saveDraft() {
                 const form = document.getElementById('projectWizardForm');
                 if (form) {
                     let actionInput = document.getElementById('wizardActionInput');
                     if (!actionInput) {
                         actionInput = document.createElement('input');
                         actionInput.type = 'hidden';
                         actionInput.name = 'action';
                         actionInput.id = 'wizardActionInput';
                         form.appendChild(actionInput);
                     }
                     actionInput.value = 'save_draft';
                     form.submit();
                 }
             }
         }">

        {{-- Validation / Session Errors Alert --}}
        @if(session('error') || $errors->any())
            <div class="p-4 mb-5 rounded-2xl border border-rose-200 dark:border-rose-900 bg-rose-50 dark:bg-rose-950/50 text-rose-800 dark:text-rose-200 text-sm">
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-rose-600 dark:text-rose-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <div>
                        <div class="font-bold text-sm">Action could not be completed:</div>
                        @if(session('error'))
                            <div class="mt-1 text-xs">{{ session('error') }}</div>
                        @endif
                        @if($errors->any())
                            <ul class="mt-1.5 list-disc list-inside text-xs space-y-0.5">
                                @foreach($errors->all() as $err)
                                    <li>{{ $err }}</li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        {{-- Draft Saved Alert (stays on page) --}}
        <div x-show="draftSaved" x-cloak class="p-3 mb-4 rounded border border-emerald-300 bg-emerald-50 text-emerald-800 text-xs font-semibold">
            Draft saved successfully.
        </div>

        {{-- Plain text step line ("Step 2 of 3: Project Info") --}}
        <div class="mb-5 text-sm font-semibold text-slate-700 dark:text-slate-300">
            <span x-show="step === 1">Step 1 of 3: Site Selection</span>
            <span x-show="step === 2">Step 2 of 3: Project Info</span>
            <span x-show="step === 3">Step 3 of 3: Materials BOM</span>
        </div>

        <form id="projectWizardForm" method="POST" action="{{ route('projects.store') }}">
            @csrf
            <input type="hidden" name="action" id="wizardActionInput" value="submit">
            <input type="hidden" name="current_step" :value="step">
            <input type="hidden" name="project_code" value="{{ $nextCode }}">
            <input type="hidden" name="site_id" :value="selectedSite?.id">

            {{-- ════════════════════════════════════════════════════════════════ --}}
            {{-- STEP 1: SITE SELECTION                                            --}}
            {{-- ════════════════════════════════════════════════════════════════ --}}
            <div x-show="step === 1" class="space-y-6">
                <div class="p-4 sm:p-6 rounded-2xl bg-white dark:bg-[#0f172a] border border-slate-200/80 dark:border-slate-800 shadow-xs space-y-5">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-200/80 dark:border-slate-800">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-950/60 border border-blue-200 dark:border-blue-800/80 flex items-center justify-center text-blue-600 dark:text-blue-400 shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            </div>
                            <div>
                                <h3 class="font-extrabold text-slate-900 dark:text-white text-base leading-tight">Step 1: Select Client Installation Site</h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Every project must be anchored to an installation site location</p>
                            </div>
                        </div>
                        <button type="button" @click="siteModalOpen = true" class="btn-secondary text-xs py-2 px-3.5 flex items-center justify-center gap-2 shadow-2xs self-start sm:self-auto shrink-0">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                            <span>Quick-Register New Site</span>
                        </button>
                    </div>

                    {{-- Selected Site Card (if chosen) --}}
                    <template x-if="selectedSite">
                        <div class="p-5 rounded-2xl bg-blue-50/60 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-800 flex items-start justify-between gap-4">
                            <div class="flex items-start gap-3">
                                <div class="w-8 h-8 rounded-[4px] bg-blue-600 text-white flex items-center justify-center font-bold text-xs shrink-0">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <h4 class="font-bold text-slate-900 dark:text-white text-base" x-text="selectedSite.name"></h4>
                                        <span class="text-xs font-mono font-bold text-blue-600 dark:text-blue-400" x-text="selectedSite.site_code"></span>
                                    </div>
                                    <p class="text-xs text-slate-600 dark:text-slate-300 mt-1" x-text="selectedSite.address"></p>
                                    <div class="flex items-center gap-4 text-xs text-slate-400 mt-1">
                                        <span x-text="selectedSite.city"></span>
                                        <span x-show="selectedSite.client_phone" x-text="'Phone: ' + selectedSite.client_phone"></span>
                                    </div>
                                </div>
                            </div>

                            <button type="button" @click="selectedSite = null" class="text-xs font-semibold text-rose-500 hover:underline shrink-0">
                                Change Site
                            </button>
                        </div>
                    </template>

                    {{-- Search Sites Bar --}}
                    <div x-show="!selectedSite" class="space-y-4">
                        <div class="relative flex items-center">
                            <svg class="w-4 h-4 text-slate-400 absolute left-3.5 pointer-events-none shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            <input type="text" x-model="siteSearch" @input.debounce.300ms="searchSites()" placeholder="Search existing sites by name, code, phone, address..." style="padding-left: 2.75rem !important;" class="w-full pl-11 pr-4 py-2.5 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl outline-none focus:ring-2 focus:ring-blue-500">
                        </div>

                        {{-- Search Results --}}
                        <div class="divide-y divide-slate-100 dark:divide-slate-800 max-h-64 overflow-y-auto rounded-xl border border-slate-100 dark:border-slate-800">
                            <template x-for="s in (siteResults.length ? siteResults : {{ $sites->toJson() }})" :key="s.id">
                                <div @click="pickSite(s)" class="p-3 hover:bg-slate-50 dark:hover:bg-slate-800/60 transition cursor-pointer flex items-center justify-between">
                                    <div>
                                        <div class="font-bold text-sm text-slate-900 dark:text-white" x-text="s.name"></div>
                                        <div class="text-xs text-slate-400" x-text="s.address + ' (' + s.city + ')'"></div>
                                    </div>
                                    <span class="text-xs font-mono font-bold text-slate-400" x-text="s.site_code"></span>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-between">
                    <a href="{{ route('projects.index') }}" class="btn-secondary text-sm">Back</a>
                    <div class="flex items-center gap-2">
                        <button type="button" @click="saveDraft()" class="btn-secondary text-sm">Save Draft</button>
                        <button type="button" @click="if(!selectedSite){ alert('Please pick a site first.'); } else { step = 2; }" class="btn-primary text-sm px-6 py-2.5">Next</button>
                    </div>
                </div>
            </div>

            {{-- ════════════════════════════════════════════════════════════════ --}}
            {{-- STEP 2: PROJECT INFO                                             --}}
            {{-- ════════════════════════════════════════════════════════════════ --}}
            <div x-show="step === 2" x-cloak class="space-y-6">
                <div class="p-6 rounded-2xl bg-white dark:bg-[#0f172a] border border-slate-200/80 dark:border-slate-800 shadow-xs space-y-4">
                    <div class="flex items-center gap-3 pb-4 border-b border-slate-200/80 dark:border-slate-800">
                        <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-950/60 border border-blue-200 dark:border-blue-800/80 flex items-center justify-center text-blue-600 dark:text-blue-400 shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </div>
                        <div>
                            <h3 class="font-extrabold text-slate-900 dark:text-white text-base leading-tight">Step 2: Core Project Specification</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Define project title, type, budget, and assigned lead technician</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">Project Title *</label>
                            <input type="text" name="title" x-model="title" required placeholder="e.g. 16-Camera IP Surveillance Installation" class="w-full px-3.5 py-2.5 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl outline-none focus:ring-2 focus:ring-blue-500 font-bold">
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">Project Type *</label>
                            <select name="project_type" x-model="project_type" @change="applyTemplate(project_type, true)" required class="w-full px-3.5 py-2.5 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl outline-none focus:ring-2 focus:ring-blue-500">
                                @foreach($projectTypes as $val => $lbl)
                                    <option value="{{ $val }}">{{ $lbl }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500">Lead Technician In-Charge</label>
                                <a href="{{ route('admin.users.create', ['role' => 'technician', 'return_to' => route('projects.create', ['step' => 2])]) }}" 
                                   class="btn-subtle text-white font-semibold"
                                   style="color: #ffffff !important;">
                                    <span class="text-white font-semibold" style="color: #ffffff !important;">+ Add New Technician</span>
                                </a>
                            </div>
                            <select name="lead_technician_id" x-model="lead_technician_id" class="w-full px-3.5 py-2.5 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl outline-none">
                                <option value="">Select Technician...</option>
                                @foreach($technicians as $tech)
                                    <option value="{{ $tech->id }}" @selected(session('new_technician_id') == $tech->id)>{{ $tech->name }} ({{ ucfirst($tech->role) }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">Client PO Number (Optional)</label>
                            <input type="text" name="po_number" x-model="po_number" placeholder="e.g. PO/2026/0889" class="w-full px-3.5 py-2.5 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl font-mono outline-none">
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">Project Estimated Budget / Value (₹)</label>
                            <input type="number" step="0.5" name="budget" x-model="budget" placeholder="125000" class="w-full px-3.5 py-2.5 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl font-mono font-bold outline-none">
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">Commencement Start Date</label>
                            <input type="date" name="start_date" x-model="start_date" class="w-full px-3.5 py-2.5 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl outline-none">
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">Target Completion Deadline</label>
                            <input type="date" name="deadline" x-model="deadline" class="w-full px-3.5 py-2.5 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl outline-none">
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">Priority</label>
                            <div class="flex gap-4">
                                @foreach(['low' => 'Low', 'medium' => 'Medium', 'high' => 'High', 'urgent' => 'Urgent'] as $pVal => $pLbl)
                                    <label class="flex items-center gap-1.5 cursor-pointer text-xs font-semibold">
                                        <input type="radio" name="priority" value="{{ $pVal }}" x-model="priority">
                                        <span>{{ $pLbl }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">Overview Description</label>
                            <textarea name="description" x-model="description" rows="2" placeholder="Brief scope of installation..." class="w-full px-3.5 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl outline-none"></textarea>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-between">
                    <button type="button" @click="step = 1" class="btn-secondary text-sm">Back</button>
                    <div class="flex items-center gap-2">
                        <button type="button" @click="saveDraft()" class="btn-secondary text-sm">Save Draft</button>
                        <button type="button" @click="if(!title){ alert('Project title is required'); } else { step = 3; }" class="btn-primary text-sm px-6 py-2.5">Next</button>
                    </div>
                </div>
            </div>

            {{-- ════════════════════════════════════════════════════════════════ --}}
            {{-- STEP 3: MATERIALS BILL OF MATERIALS (BOM) & SUBMISSION            --}}
            {{-- ════════════════════════════════════════════════════════════════ --}}
            <div x-show="step === 3" x-cloak class="space-y-6">
                <div class="p-4 sm:p-6 rounded-2xl bg-white dark:bg-[#0f172a] border border-slate-200/80 dark:border-slate-800 shadow-xs space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-200/80 dark:border-slate-800">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-950/60 border border-blue-200 dark:border-blue-800/80 flex items-center justify-center text-blue-600 dark:text-blue-400 shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                            </div>
                            <div>
                                <h3 class="font-extrabold text-slate-900 dark:text-white text-base leading-tight">Step 3: Bill of Materials (BOM)</h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Specify hardware line items and whether sourced from warehouse or local purchase</p>
                            </div>
                        </div>
                        <button type="button" @click="addMaterialRow()" class="btn-secondary text-xs py-2 px-3.5 flex items-center justify-center gap-2 shadow-2xs self-start sm:self-auto shrink-0">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                            <span>Add Item</span>
                        </button>
                    </div>

                    {{-- Datalists for auto-suggestions --}}
                    <datalist id="bom-products-list">
                        @foreach($products as $p)
                            <option value="{{ $p->name }}"></option>
                        @endforeach
                    </datalist>

                    <datalist id="bom-vendors-list">
                        @foreach($vendors as $v)
                            <option value="{{ $v->name }}"></option>
                        @endforeach
                    </datalist>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="text-xs uppercase font-bold text-slate-400 border-b border-slate-100 dark:border-slate-800">
                                <tr>
                                    <th class="pb-2">Item Name &amp; Model</th>
                                    <th class="pb-2 w-24">Qty</th>
                                    <th class="pb-2 w-24">Unit</th>
                                    <th class="pb-2 w-36">Source</th>
                                    <th class="pb-2">Local Shop Name (if local)</th>
                                    <th class="pb-2 w-10"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                <template x-for="(mat, idx) in materials" :key="idx">
                                    <tr>
                                        <td class="py-2 pr-2 align-top">
                                            <input type="text"
                                                   :name="`materials[${idx}][item_name]`"
                                                   x-model="mat.item_name"
                                                   list="bom-products-list"
                                                   @input="onProductSelect(mat)"
                                                   required
                                                   placeholder="Item description"
                                                   class="w-full px-3 py-1.5 text-xs bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg outline-none">
                                            <div x-show="mat.is_custom && isNewProduct(mat.item_name)" x-cloak class="text-[11px] text-slate-400 dark:text-slate-500 mt-1 font-medium">
                                                New item. Will be saved for next time.
                                            </div>
                                        </td>
                                        <td class="py-2 pr-2 align-top">
                                            <input type="number" step="any" min="0.01" :name="`materials[${idx}][quantity]`" x-model="mat.quantity" required class="w-full px-2 py-1.5 text-xs bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg font-mono font-bold outline-none">
                                        </td>
                                        <td class="py-2 pr-2 align-top">
                                            <select :name="`materials[${idx}][unit]`" x-model="mat.unit" class="w-full px-2 py-1.5 text-xs bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg outline-none">
                                                <option value="nos">nos</option>
                                                <option value="mtr">mtr</option>
                                                <option value="box">box</option>
                                                <option value="pkt">pkt</option>
                                                <option value="roll">roll</option>
                                                <option value="set">set</option>
                                            </select>
                                        </td>
                                        <td class="py-2 pr-2 align-top">
                                            <select :name="`materials[${idx}][source]`" x-model="mat.source" class="w-full px-2 py-1.5 text-xs bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg outline-none font-semibold">
                                                <option value="warehouse">Warehouse</option>
                                                <option value="local_purchase">Local Purchase</option>
                                            </select>
                                        </td>
                                        <td class="py-2 pr-2 align-top">
                                            <input type="text"
                                                   :name="`materials[${idx}][shop_name]`"
                                                   x-model="mat.shop_name"
                                                   list="bom-vendors-list"
                                                   placeholder="Shop name if local"
                                                   class="w-full px-3 py-1.5 text-xs bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg outline-none">
                                            <div x-show="mat.is_custom && mat.source === 'local_purchase' && isNewVendor(mat.shop_name)" x-cloak class="text-[11px] text-slate-400 dark:text-slate-500 mt-1 font-medium">
                                                New item. Will be saved for next time.
                                            </div>
                                        </td>
                                        <td class="py-2 text-center align-top">
                                            <button type="button" @click="removeMaterialRow(idx)" class="text-rose-500 hover:text-rose-700 text-lg leading-none pt-1.5">&times;</button>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Submission Actions --}}
                <div class="flex items-center justify-between pt-4">
                    <button type="button" @click="step = 2" class="btn-secondary text-sm">Back</button>

                    <div class="flex items-center gap-2">
                        <button type="button" @click="saveDraft()" class="btn-secondary text-sm">
                            Save Draft
                        </button>
                        <button type="submit" name="action" value="submit" class="btn-primary text-sm px-6 py-2.5">
                            Submit
                        </button>
                    </div>
                </div>
            </div>

        </form>

        {{-- Quick Register Site Modal --}}
        <div x-show="siteModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
            <div @click.away="siteModalOpen = false" class="bg-white dark:bg-[#0f172a] rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-200 dark:border-slate-800 space-y-4">
                <div class="flex items-center justify-between pb-3.5 border-b border-slate-200/80 dark:border-slate-800">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-950/60 border border-blue-200 dark:border-blue-800/80 flex items-center justify-center text-blue-600 dark:text-blue-400 shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        </div>
                        <div>
                            <h3 class="font-extrabold text-slate-900 dark:text-white text-base leading-tight">Quick-Register Installation Site</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Directly add and select site location for this project</p>
                        </div>
                    </div>
                    <button type="button" @click="siteModalOpen = false" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-slate-700 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 transition cursor-pointer" aria-label="Close modal">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="space-y-3.5">
                    <div>
                        <label class="crm-label">
                            <svg class="w-4 h-4 shrink-0 inline-block" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                            Site / Premises Name <span class="required-marker text-rose-500">*</span>
                        </label>
                        <input type="text" x-model="newSite.name" placeholder="e.g. Al-Falah Spinning Mills" class="crm-input">
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="crm-label">
                                <svg class="w-4 h-4 shrink-0 inline-block" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                Client Name
                            </label>
                            <input type="text" x-model="newSite.client_name" placeholder="Organization or Owner" class="crm-input">
                        </div>
                        <div>
                            <label class="crm-label">
                                <svg class="w-4 h-4 shrink-0 inline-block" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                Client Phone
                            </label>
                            <input type="text" x-model="newSite.client_phone" placeholder="+91 98765 43210" class="crm-input">
                        </div>
                    </div>
                    <div>
                        <label class="crm-label">
                            <svg class="w-4 h-4 shrink-0 inline-block" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            Full Physical Address <span class="required-marker text-rose-500">*</span>
                        </label>
                        <textarea x-model="newSite.address" rows="2" placeholder="Building, Street, Landmark..." class="crm-input h-auto py-2.5"></textarea>
                    </div>
                    <div>
                        <label class="crm-label">
                            <svg class="w-4 h-4 shrink-0 inline-block" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                            City
                        </label>
                        <input type="text" x-model="newSite.city" placeholder="e.g. Coimbatore" class="crm-input">
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2.5 pt-3.5 border-t border-slate-200/80 dark:border-slate-800">
                    <button type="button" @click="siteModalOpen = false" class="btn-secondary text-xs px-4 py-2">Cancel</button>
                    <button type="button" @click="createSite()" class="btn-primary text-xs px-4 py-2 shadow-md">Register &amp; Select</button>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>
