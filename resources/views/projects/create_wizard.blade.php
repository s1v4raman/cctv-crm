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
            <span class="text-xs text-slate-400">4-Step Guided Setup</span>
        </div>
    </x-slot>

    <div class="py-6 px-4 sm:px-6 lg:px-8 max-w-5xl mx-auto"
         x-data="{
             step: 1,
             selectedSite: null,
             siteSearch: '',
             siteResults: [],
             siteModalOpen: false,
             newSite: { name: '', client_name: '', client_phone: '', address: '', city: 'Coimbatore', latitude: '', longitude: '' },
             
             // Step 2 Info
             title: '{{ old('title') }}',
             project_type: '{{ old('project_type', 'hardware_cctv') }}',
             lead_technician_id: '{{ old('lead_technician_id') }}',
             budget: '{{ old('budget') }}',
             po_number: '{{ old('po_number') }}',
             start_date: '{{ old('start_date', now()->toDateString()) }}',
             deadline: '{{ old('deadline') }}',
             priority: '{{ old('priority', 'medium') }}',
             description: '{{ old('description') }}',

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

             materials: [],

             init() {
                 this.applyTemplate(this.project_type, false);
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
                 this.materials.push({ item_name: '', quantity: 1, unit: 'nos', source: 'warehouse', shop_name: '', notes: '' });
             },
             removeMaterialRow(index) {
                 this.materials.splice(index, 1);
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
             }
         }">

        {{-- Wizard Progress Bar --}}
        <div class="mb-8">
            <div class="flex items-center justify-between max-w-3xl mx-auto relative">
                <div class="absolute left-0 top-1/2 -translate-y-1/2 h-1 bg-slate-200 dark:bg-slate-700 w-full z-0"></div>
                <div class="absolute left-0 top-1/2 -translate-y-1/2 h-1 bg-blue-600 transition-all duration-300 z-0"
                     :style="`width: ${(step - 1) * 33.33}%`"></div>

                {{-- Step 1 --}}
                <div class="relative z-10 flex flex-col items-center">
                    <button type="button" @click="step = 1" class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm transition-all"
                            :class="step >= 1 ? 'bg-blue-600 text-white ring-4 ring-blue-100 dark:ring-blue-950' : 'bg-slate-200 dark:bg-slate-700 text-slate-500'">
                        1
                    </button>
                    <span class="text-xs font-bold mt-2" :class="step >= 1 ? 'text-blue-600 dark:text-blue-400' : 'text-slate-400'">Site Selection</span>
                </div>

                {{-- Step 2 --}}
                <div class="relative z-10 flex flex-col items-center">
                    <button type="button" @click="if(selectedSite) step = 2" class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm transition-all"
                            :class="step >= 2 ? 'bg-blue-600 text-white ring-4 ring-blue-100 dark:ring-blue-950' : 'bg-slate-200 dark:bg-slate-700 text-slate-500'">
                        2
                    </button>
                    <span class="text-xs font-bold mt-2" :class="step >= 2 ? 'text-blue-600 dark:text-blue-400' : 'text-slate-400'">Project Info</span>
                </div>

                {{-- Step 3 --}}
                <div class="relative z-10 flex flex-col items-center">
                    <button type="button" @click="if(selectedSite && title) step = 3" class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm transition-all"
                            :class="step >= 3 ? 'bg-blue-600 text-white ring-4 ring-blue-100 dark:ring-blue-950' : 'bg-slate-200 dark:bg-slate-700 text-slate-500'">
                        3
                    </button>
                    <span class="text-xs font-bold mt-2" :class="step >= 3 ? 'text-blue-600 dark:text-blue-400' : 'text-slate-400'">Scope &amp; Req</span>
                </div>

                {{-- Step 4 --}}
                <div class="relative z-10 flex flex-col items-center">
                    <button type="button" @click="if(selectedSite && title) step = 4" class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm transition-all"
                            :class="step >= 4 ? 'bg-blue-600 text-white ring-4 ring-blue-100 dark:ring-blue-950' : 'bg-slate-200 dark:bg-slate-700 text-slate-500'">
                        4
                    </button>
                    <span class="text-xs font-bold mt-2" :class="step >= 4 ? 'text-blue-600 dark:text-blue-400' : 'text-slate-400'">Materials BOM</span>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('projects.store') }}">
            @csrf
            <input type="hidden" name="project_code" value="{{ $nextCode }}">
            <input type="hidden" name="site_id" :value="selectedSite?.id">

            {{-- ════════════════════════════════════════════════════════════════ --}}
            {{-- STEP 1: SITE SELECTION                                            --}}
            {{-- ════════════════════════════════════════════════════════════════ --}}
            <div x-show="step === 1" class="space-y-6">
                <div class="p-6 rounded-2xl bg-white dark:bg-[#0f172a] border border-slate-200/80 dark:border-slate-800 shadow-xs space-y-5">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                        <div>
                            <h3 class="font-extrabold text-slate-900 dark:text-white text-base">Step 1: Select Client Installation Site</h3>
                            <p class="text-xs text-slate-500 mt-0.5">Every project must be anchored to an installation site location</p>
                        </div>
                        <button type="button" @click="siteModalOpen = true" class="btn-secondary text-xs flex items-center gap-1.5 shadow-2xs">
                            <span>+ Quick-Register New Site</span>
                        </button>
                    </div>

                    {{-- Selected Site Card (if chosen) --}}
                    <template x-if="selectedSite">
                        <div class="p-5 rounded-2xl bg-blue-50/60 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-800 flex items-start justify-between gap-4">
                            <div class="flex items-start gap-3">
                                <div class="w-10 h-10 rounded-xl bg-blue-600 text-white flex items-center justify-center font-bold text-sm shrink-0">
                                    🏢
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
                        <div class="relative">
                            <svg class="w-4 h-4 text-slate-400 absolute left-3.5 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            <input type="text" x-model="siteSearch" @input.debounce.300ms="searchSites()" placeholder="Search existing sites by name, code, phone, address..." class="w-full pl-10 pr-4 py-2.5 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl outline-none focus:ring-2 focus:ring-blue-500">
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

                <div class="flex justify-end">
                    <button type="button" @click="if(!selectedSite){ alert('Please pick a site first.'); } else { step = 2; }" class="btn-primary text-sm px-6 py-2.5 shadow-md flex items-center gap-2">
                        <span>Continue to Project Info</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </button>
                </div>
            </div>

            {{-- ════════════════════════════════════════════════════════════════ --}}
            {{-- STEP 2: PROJECT INFO                                             --}}
            {{-- ════════════════════════════════════════════════════════════════ --}}
            <div x-show="step === 2" x-cloak class="space-y-6">
                <div class="p-6 rounded-2xl bg-white dark:bg-[#0f172a] border border-slate-200/80 dark:border-slate-800 shadow-xs space-y-4">
                    <h3 class="font-extrabold text-slate-900 dark:text-white text-base pb-3 border-b border-slate-100 dark:border-slate-800">
                        Step 2: Core Project Specification
                    </h3>

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
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">Lead Technician In-Charge</label>
                            <select name="lead_technician_id" x-model="lead_technician_id" class="w-full px-3.5 py-2.5 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl outline-none">
                                <option value="">Select Technician...</option>
                                @foreach($technicians as $tech)
                                    <option value="{{ $tech->id }}">{{ $tech->name }} ({{ ucfirst($tech->role) }})</option>
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
                                @foreach(['low' => 'Low', 'medium' => 'Medium', 'high' => 'High', 'urgent' => 'Urgent 🔥'] as $pVal => $pLbl)
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
                    <button type="button" @click="if(!title){ alert('Project title is required'); } else { step = 3; }" class="btn-primary text-sm px-6 py-2.5 shadow-md flex items-center gap-2">
                        <span>Continue to Requirements</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </button>
                </div>
            </div>

            {{-- ════════════════════════════════════════════════════════════════ --}}
            {{-- STEP 3: REQUIREMENTS CHECKLIST                                   --}}
            {{-- ════════════════════════════════════════════════════════════════ --}}
            <div x-show="step === 3" x-cloak class="space-y-6">
                <div class="p-6 rounded-2xl bg-white dark:bg-[#0f172a] border border-slate-200/80 dark:border-slate-800 shadow-xs space-y-5">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 pb-3 border-b border-slate-100 dark:border-slate-800">
                        <div>
                            <h3 class="font-extrabold text-slate-900 dark:text-white text-base">Step 3: Technical Requirements &amp; Scope</h3>
                            <p class="text-xs text-slate-400">Customized fields tailored for this specific project type</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-blue-50 dark:bg-blue-950 text-blue-600 dark:text-blue-400 border border-blue-200 dark:border-blue-800">
                                Mode: <strong x-text="templates[project_type]?.nameSuffix || project_type"></strong>
                            </span>
                            <button type="button" @click="applyTemplate(project_type)" class="text-xs font-semibold text-blue-600 dark:text-blue-400 hover:underline">
                                ↺ Reset Template
                            </button>
                        </div>
                    </div>

                    {{-- 1. HARDWARE CCTV --}}
                    <div x-show="project_type === 'hardware_cctv'" class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-500 mb-1">CCTV Cameras Count</label>
                            <input type="number" name="requirements[cctv_count]" x-model="req.cctv_count" min="0" class="w-full px-3.5 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl font-bold font-mono outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-500 mb-1">NVR / DVR Channels</label>
                            <select name="requirements[nvr_channels]" x-model="req.nvr_channels" class="w-full px-3.5 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl outline-none">
                                <option value="4">4-Channel</option>
                                <option value="8">8-Channel</option>
                                <option value="16">16-Channel</option>
                                <option value="32">32-Channel</option>
                                <option value="64">64-Channel</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Est. Cabling Metres</label>
                            <input type="number" name="requirements[cabling_metres]" x-model="req.cabling_metres" min="0" class="w-full px-3.5 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl font-bold font-mono outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Power Supply Type</label>
                            <input type="text" name="requirements[power_supply]" x-model="req.power_supply" placeholder="e.g. 8-Port PoE Switch" class="w-full px-3.5 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Display Monitor / TV</label>
                            <input type="text" name="requirements[display_screen]" x-model="req.display_screen" placeholder="e.g. 32-inch Full HD LED" class="w-full px-3.5 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Storage HDD Capacity</label>
                            <input type="text" name="requirements[storage_hdd]" x-model="req.storage_hdd" placeholder="e.g. 4TB Surveillance SATA HDD" class="w-full px-3.5 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl outline-none">
                        </div>
                    </div>

                    {{-- 2. NETWORKING & WI-FI --}}
                    <div x-show="project_type === 'networking'" class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Network Data Drop Points</label>
                            <input type="number" name="requirements[network_drops]" x-model="req.network_drops" min="0" placeholder="e.g. 24" class="w-full px-3.5 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl font-bold font-mono outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Wi-Fi Access Points (APs)</label>
                            <input type="number" name="requirements[wifi_aps]" x-model="req.wifi_aps" min="0" placeholder="e.g. 3" class="w-full px-3.5 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl font-bold font-mono outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Server / Network Rack Size</label>
                            <input type="text" name="requirements[rack_size]" x-model="req.rack_size" placeholder="e.g. 9U Wall Mount Server Rack" class="w-full px-3.5 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Switch Specification</label>
                            <input type="text" name="requirements[switch_spec]" x-model="req.switch_spec" placeholder="e.g. 24-Port Gigabit Managed PoE+ Switch" class="w-full px-3.5 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Est. Structured Cabling (m)</label>
                            <input type="number" name="requirements[cabling_metres]" x-model="req.cabling_metres" min="0" placeholder="e.g. 450" class="w-full px-3.5 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl font-bold font-mono outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Router / Firewall Gateway</label>
                            <input type="text" name="requirements[router_gateway]" x-model="req.router_gateway" placeholder="e.g. Dual-WAN Gigabit Security Router" class="w-full px-3.5 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl outline-none">
                        </div>
                    </div>

                    {{-- 3. SOFTWARE & WEB APPLICATION --}}
                    <div x-show="project_type === 'software_web'" class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Application Architecture</label>
                            <input type="text" name="requirements[app_architecture]" x-model="req.app_architecture" placeholder="e.g. Full-Stack Responsive Web App" class="w-full px-3.5 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Primary Tech Stack</label>
                            <input type="text" name="requirements[tech_stack]" x-model="req.tech_stack" placeholder="e.g. PHP Laravel + Tailwind + MySQL" class="w-full px-3.5 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Cloud Hosting Infrastructure</label>
                            <input type="text" name="requirements[hosting_infra]" x-model="req.hosting_infra" placeholder="e.g. Cloud VPS (Ubuntu / Nginx / SSL)" class="w-full px-3.5 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl outline-none">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Core Modules &amp; Functional Scope</label>
                            <input type="text" name="requirements[core_modules]" x-model="req.core_modules" placeholder="e.g. Auth, Role Permissions, CRM, Billing & REST API" class="w-full px-3.5 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Domain &amp; SSL Configuration</label>
                            <input type="text" name="requirements[domain_ssl]" x-model="req.domain_ssl" placeholder="e.g. Custom Domain + Cloudflare Wildcard SSL" class="w-full px-3.5 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl outline-none">
                        </div>
                    </div>

                    {{-- 4. BIOMETRIC ACCESS CONTROL --}}
                    <div x-show="project_type === 'access_control'" class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Controlled Doors Count</label>
                            <input type="number" name="requirements[doors_count]" x-model="req.doors_count" min="1" placeholder="e.g. 2" class="w-full px-3.5 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl font-bold font-mono outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Authentication Method</label>
                            <input type="text" name="requirements[auth_method]" x-model="req.auth_method" placeholder="e.g. Face Recognition + Fingerprint + Card" class="w-full px-3.5 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Lock Hardware Type</label>
                            <input type="text" name="requirements[lock_type]" x-model="req.lock_type" placeholder="e.g. 600lbs Electromagnetic (EM) Lock" class="w-full px-3.5 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Exit Device Sensor</label>
                            <input type="text" name="requirements[exit_device]" x-model="req.exit_device" placeholder="e.g. No-Touch Infrared Exit Sensor Switch" class="w-full px-3.5 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Controller Hardware</label>
                            <input type="text" name="requirements[controller_type]" x-model="req.controller_type" placeholder="e.g. Standalone IP Biometric Terminal" class="w-full px-3.5 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Battery Power Backup</label>
                            <input type="text" name="requirements[power_backup]" x-model="req.power_backup" placeholder="e.g. 12V 5A Supply with 7Ah Battery" class="w-full px-3.5 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl outline-none">
                        </div>
                    </div>

                    {{-- 5. TERMINAL ATTENDANCE --}}
                    <div x-show="project_type === 'hardware_attendance'" class="space-y-4">
                        <div class="p-4 rounded-xl bg-purple-50/50 dark:bg-purple-950/20 border border-purple-200/70 dark:border-purple-800/40">
                            <h4 class="text-xs font-bold text-purple-700 dark:text-purple-300 uppercase tracking-wider mb-3 flex items-center gap-1.5">
                                <span>⏱ Terminal Camera &amp; Hardware Parameters</span>
                            </h4>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <div>
                                    <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Attendance Terminals Count</label>
                                    <input type="number" min="0" name="hardware_specs[terminal_count]" x-model="hardware_specs.terminal_count" placeholder="e.g. 2" class="w-full px-3.5 py-2 text-sm bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl font-bold font-mono outline-none">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Integrated Cameras Count</label>
                                    <input type="number" min="0" name="hardware_specs[camera_count]" x-model="hardware_specs.camera_count" placeholder="e.g. 4" class="w-full px-3.5 py-2 text-sm bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl font-bold font-mono outline-none">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Device Brand / Model</label>
                                    <input type="text" name="hardware_specs[device_brand]" x-model="hardware_specs.device_brand" placeholder="e.g. Hikvision MinMoe / ZKTeco" class="w-full px-3.5 py-2 text-sm bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl outline-none">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Terminal IP Subnet / Network</label>
                                    <input type="text" name="hardware_specs[terminal_ip]" x-model="hardware_specs.terminal_ip" placeholder="e.g. 192.168.1.50 - 192.168.1.55" class="w-full px-3.5 py-2 text-sm bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl font-mono outline-none">
                                </div>
                                <div class="sm:col-span-2">
                                    <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Attendance Verification Method</label>
                                    <select name="hardware_specs[attendance_sync_mode]" x-model="hardware_specs.attendance_sync_mode" class="w-full px-3.5 py-2 text-sm bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl outline-none">
                                        <option value="face_recognition">AI Facial Recognition Camera Terminal</option>
                                        <option value="biometric_fingerprint">Biometric Optical Fingerprint Reader</option>
                                        <option value="rfid_card">RFID Proximity Card / Keyfob Terminal</option>
                                        <option value="hybrid_multi">Multi-Modal (Face + Fingerprint + Card)</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-xs font-bold uppercase text-slate-500 mb-1">User / Employee Capacity</label>
                                <input type="number" name="requirements[terminal_users]" x-model="req.terminal_users" min="0" placeholder="e.g. 500" class="w-full px-3.5 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl font-bold font-mono outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Communication Protocol</label>
                                <input type="text" name="requirements[communication]" x-model="req.communication" placeholder="e.g. TCP/IP Ethernet + Wi-Fi + USB" class="w-full px-3.5 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-bold uppercase text-slate-500 mb-1">HRMS / Payroll Sync Software</label>
                                <input type="text" name="requirements[hrms_software]" x-model="req.hrms_software" placeholder="e.g. Desktop HRMS + Automated Excel Sync" class="w-full px-3.5 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Mounting Location</label>
                                <input type="text" name="requirements[mounting_location]" x-model="req.mounting_location" placeholder="e.g. Main Reception / Turnstile Gate" class="w-full px-3.5 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl outline-none">
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Power / Backup Type</label>
                                <input type="text" name="requirements[power_battery]" x-model="req.power_battery" placeholder="e.g. Inbuilt Rechargeable Lithium Backup Battery" class="w-full px-3.5 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl outline-none">
                            </div>
                        </div>
                    </div>

                    {{-- 6. HYBRID (HARDWARE + SOFTWARE) --}}
                    <div x-show="project_type === 'hybrid'" class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Hardware Endpoints Count</label>
                            <input type="number" name="requirements[hardware_endpoints]" x-model="req.hardware_endpoints" min="0" placeholder="e.g. 16" class="w-full px-3.5 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl font-bold font-mono outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Software Platform</label>
                            <input type="text" name="requirements[software_platform]" x-model="req.software_platform" placeholder="e.g. Central Web Dashboard & Mobile Alert App" class="w-full px-3.5 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-500 mb-1">IoT Edge Gateway</label>
                            <input type="text" name="requirements[edge_gateway]" x-model="req.edge_gateway" placeholder="e.g. Edge IoT Controller Unit" class="w-full px-3.5 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Central Storage Server</label>
                            <input type="text" name="requirements[server_storage]" x-model="req.server_storage" placeholder="e.g. On-Premises High Availability Micro-Server" class="w-full px-3.5 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl outline-none">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Alert Channels &amp; Automation</label>
                            <input type="text" name="requirements[alert_channels]" x-model="req.alert_channels" placeholder="e.g. Real-Time WhatsApp, Email & SMS Alerts" class="w-full px-3.5 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl outline-none">
                        </div>
                    </div>

                    {{-- Common Special Instructions Textarea --}}
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Client Special Instructions &amp; Project Scope Details</label>
                        <textarea name="requirements[special_requirements]" x-model="req.special_requirements" rows="2" placeholder="e.g. Special milestones, deployment instructions, site-specific access notes..." class="w-full px-3.5 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl outline-none"></textarea>
                    </div>
                </div>

                <div class="flex items-center justify-between">
                    <button type="button" @click="step = 2" class="btn-secondary text-sm">Back</button>
                    <button type="button" @click="step = 4" class="btn-primary text-sm px-6 py-2.5 shadow-md flex items-center gap-2">
                        <span>Continue to Materials BOM</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </button>
                </div>
            </div>

            {{-- ════════════════════════════════════════════════════════════════ --}}
            {{-- STEP 4: MATERIALS BILL OF MATERIALS (BOM) & SUBMISSION            --}}
            {{-- ════════════════════════════════════════════════════════════════ --}}
            <div x-show="step === 4" x-cloak class="space-y-6">
                <div class="p-6 rounded-2xl bg-white dark:bg-[#0f172a] border border-slate-200/80 dark:border-slate-800 shadow-xs space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                        <div>
                            <h3 class="font-extrabold text-slate-900 dark:text-white text-base">Step 4: Bill of Materials (BOM)</h3>
                            <p class="text-xs text-slate-500 mt-0.5">Specify hardware line items and whether sourced from warehouse or local purchase</p>
                        </div>
                        <button type="button" @click="addMaterialRow()" class="btn-secondary text-xs flex items-center gap-1">
                            + Add Line Item
                        </button>
                    </div>

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
                                        <td class="py-2 pr-2">
                                            <input type="text" :name="`materials[${idx}][item_name]`" x-model="mat.item_name" required placeholder="Item description" class="w-full px-3 py-1.5 text-xs bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg outline-none">
                                        </td>
                                        <td class="py-2 pr-2">
                                            <input type="number" step="any" min="0.01" :name="`materials[${idx}][quantity]`" x-model="mat.quantity" required class="w-full px-2 py-1.5 text-xs bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg font-mono font-bold outline-none">
                                        </td>
                                        <td class="py-2 pr-2">
                                            <select :name="`materials[${idx}][unit]`" x-model="mat.unit" class="w-full px-2 py-1.5 text-xs bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg outline-none">
                                                <option value="nos">nos</option>
                                                <option value="mtr">mtr</option>
                                                <option value="box">box</option>
                                                <option value="pkt">pkt</option>
                                                <option value="roll">roll</option>
                                                <option value="set">set</option>
                                            </select>
                                        </td>
                                        <td class="py-2 pr-2">
                                            <select :name="`materials[${idx}][source]`" x-model="mat.source" class="w-full px-2 py-1.5 text-xs bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg outline-none font-semibold">
                                                <option value="warehouse">Warehouse</option>
                                                <option value="local_purchase">Local Purchase</option>
                                            </select>
                                        </td>
                                        <td class="py-2 pr-2">
                                            <input type="text" :name="`materials[${idx}][shop_name]`" x-model="mat.shop_name" placeholder="Shop name if local" class="w-full px-3 py-1.5 text-xs bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg outline-none">
                                        </td>
                                        <td class="py-2 text-center">
                                            <button type="button" @click="removeMaterialRow(idx)" class="text-rose-500 hover:text-rose-700 text-lg leading-none">&times;</button>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Submission Actions --}}
                <div class="flex items-center justify-between pt-4 border-t border-slate-200 dark:border-slate-800">
                    <button type="button" @click="step = 3" class="btn-secondary text-sm">Back</button>

                    <div class="flex items-center gap-3">
                        <button type="submit" name="action" value="save_draft" class="btn-secondary text-sm">
                            💾 Save as Draft
                        </button>
                        <button type="submit" name="action" value="submit" class="btn-primary text-sm shadow-md px-6 py-2.5">
                            🚀 Submit for Admin Approval
                        </button>
                    </div>
                </div>
            </div>

        </form>

        {{-- Quick Register Site Modal --}}
        <div x-show="siteModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
            <div @click.away="siteModalOpen = false" class="bg-white dark:bg-[#0f172a] rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-200 dark:border-slate-800 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                    <h3 class="font-bold text-slate-900 dark:text-white text-base">Quick-Register Installation Site</h3>
                    <button type="button" @click="siteModalOpen = false" class="text-slate-400 hover:text-slate-600 text-lg">&times;</button>
                </div>

                <div class="space-y-3">
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Site / Premises Name *</label>
                        <input type="text" x-model="newSite.name" placeholder="e.g. Al-Falah Spinning Mills" class="w-full px-3.5 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Client Name</label>
                        <input type="text" x-model="newSite.client_name" placeholder="Organization or Owner name" class="w-full px-3.5 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Client Phone</label>
                        <input type="text" x-model="newSite.client_phone" placeholder="+91 98765 43210" class="w-full px-3.5 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Full Physical Address *</label>
                        <textarea x-model="newSite.address" rows="2" placeholder="Building, Street, Landmark..." class="w-full px-3.5 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl outline-none"></textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-500 mb-1">City</label>
                        <input type="text" x-model="newSite.city" class="w-full px-3.5 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl outline-none">
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" @click="siteModalOpen = false" class="btn-secondary text-xs">Cancel</button>
                    <button type="button" @click="createSite()" class="btn-primary text-xs shadow-md">Register &amp; Select</button>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>
