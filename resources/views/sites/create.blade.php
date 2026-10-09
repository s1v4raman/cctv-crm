<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('sites.index') }}" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    </a>
                    <h2 class="text-xl font-extrabold text-slate-900 dark:text-white font-heading tracking-tight">Register Installation Site</h2>
                </div>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Add client physical location, GPS coordinates, and contact details for field deployment</p>
            </div>
        </div>
    </x-slot>

    <div class="py-6 px-4 sm:px-6 lg:px-8 max-w-4xl mx-auto"
         x-data="{
             lat: '{{ old('latitude') }}',
             lng: '{{ old('longitude') }}',
             gmapsUrl: '{{ old('google_maps_url') }}',
             clientPhone: '{{ old('client_phone') }}',
             siteName: '{{ old('name') }}',
             locating: false,
             locError: '',
             duplicateWarning: '',
             async getCurrentLocation() {
                 if (!navigator.geolocation) {
                     this.locError = 'Geolocation is not supported by your browser.';
                     return;
                 }
                 this.locating = true;
                 this.locError = '';
                 navigator.geolocation.getCurrentPosition(
                     (pos) => {
                         this.lat = pos.coords.latitude.toFixed(6);
                         this.lng = pos.coords.longitude.toFixed(6);
                         this.locating = false;
                     },
                     (err) => {
                         this.locError = 'Unable to retrieve location: ' + err.message;
                         this.locating = false;
                     },
                     { enableHighAccuracy: true, timeout: 10000 }
                 );
             },
             parseGoogleMapsUrl() {
                 if (!this.gmapsUrl) return;
                 // Pattern 1: @lat,lng
                 let m = this.gmapsUrl.match(/@(-?\d+\.\d+),(-?\d+\.\d+)/);
                 if (m) {
                     this.lat = parseFloat(m[1]).toFixed(6);
                     this.lng = parseFloat(m[2]).toFixed(6);
                     return;
                 }
                 // Pattern 2: ?q=lat,lng
                 m = this.gmapsUrl.match(/[?&]q=(-?\d+\.\d+),(-?\d+\.\d+)/);
                 if (m) {
                     this.lat = parseFloat(m[1]).toFixed(6);
                     this.lng = parseFloat(m[2]).toFixed(6);
                     return;
                 }
                 // Pattern 3: !3dlat!4dlng
                 m = this.gmapsUrl.match(/!3d(-?\d+\.\d+)!4d(-?\d+\.\d+)/);
                 if (m) {
                     this.lat = parseFloat(m[1]).toFixed(6);
                     this.lng = parseFloat(m[2]).toFixed(6);
                 }
             },
             async checkDuplicate() {
                 if (!this.clientPhone && !this.siteName) return;
                 try {
                     const res = await fetch('{{ route('sites.check-duplicate') }}', {
                         method: 'POST',
                         headers: {
                             'Content-Type': 'application/json',
                             'X-CSRF-TOKEN': '{{ csrf_token() }}'
                         },
                         body: JSON.stringify({ phone: this.clientPhone, name: this.siteName })
                     });
                     const data = await res.json();
                     if (data.duplicate) {
                         this.duplicateWarning = data.reason;
                     } else {
                         this.duplicateWarning = '';
                     }
                 } catch (e) {}
             }
         }">

        {{-- Duplicate Alert Banner --}}
        <div x-show="duplicateWarning" x-cloak class="mb-6 p-4 rounded-2xl bg-amber-50 dark:bg-amber-950/40 border border-amber-300 dark:border-amber-800 text-amber-800 dark:text-amber-300 flex items-start gap-3">
            <svg class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            <div>
                <h4 class="font-bold text-sm">Potential Duplicate Site Warning</h4>
                <p class="text-xs mt-0.5" x-text="duplicateWarning"></p>
            </div>
        </div>

        <form method="POST" action="{{ route('sites.store') }}" class="space-y-6">
            @csrf

            {{-- Card 1: Core Details --}}
            <div class="p-6 rounded-2xl bg-white dark:bg-[#0f172a] border border-slate-200/80 dark:border-slate-800 shadow-xs space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                    <h3 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-blue-600"></span>
                        Site Identification &amp; Client Details
                    </h3>
                    <span class="px-2.5 py-1 text-xs font-mono font-bold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 rounded-lg">
                        {{ $nextCode }}
                    </span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">
                            Site / Premises Name <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="name" x-model="siteName" @blur="checkDuplicate()" value="{{ old('name') }}" required placeholder="e.g. Al-Falah Textiles Spinning Mill or Green Valley Villa" class="w-full px-3.5 py-2.5 text-sm bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none">
                        @error('name') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">Client / Organization Name</label>
                        <input type="text" name="client_name" value="{{ old('client_name') }}" placeholder="e.g. Precision Fab Pvt Ltd" class="w-full px-3.5 py-2.5 text-sm bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">Client Phone Number</label>
                        <input type="text" name="client_phone" x-model="clientPhone" @blur="checkDuplicate()" value="{{ old('client_phone') }}" placeholder="e.g. +91 98765 43210" class="w-full px-3.5 py-2.5 text-sm bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">Client Email</label>
                        <input type="email" name="client_email" value="{{ old('client_email') }}" placeholder="admin@clientdomain.com" class="w-full px-3.5 py-2.5 text-sm bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">Local Contact Person</label>
                        <input type="text" name="contact_person" value="{{ old('contact_person') }}" placeholder="e.g. Ramesh (Site Engineer)" class="w-full px-3.5 py-2.5 text-sm bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>
                </div>
            </div>

            {{-- Card 2: Physical Address & Location --}}
            <div class="p-6 rounded-2xl bg-white dark:bg-[#0f172a] border border-slate-200/80 dark:border-slate-800 shadow-xs space-y-4">
                <h3 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2 pb-3 border-b border-slate-100 dark:border-slate-800">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-600"></span>
                    Physical Address &amp; Navigation
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="sm:col-span-3">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">
                            Door / Building / Street Address <span class="text-rose-500">*</span>
                        </label>
                        <textarea name="address" rows="2" required placeholder="Plot No. 42, SIDCO Industrial Estate, Phase 2..." class="w-full px-3.5 py-2.5 text-sm bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none">{{ old('address') }}</textarea>
                        @error('address') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">City / Town</label>
                        <input type="text" name="city" value="{{ old('city', 'Coimbatore') }}" placeholder="Coimbatore" class="w-full px-3.5 py-2.5 text-sm bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">State</label>
                        <input type="text" name="state" value="{{ old('state', 'Tamil Nadu') }}" placeholder="Tamil Nadu" class="w-full px-3.5 py-2.5 text-sm bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">PIN Code</label>
                        <input type="text" name="pincode" value="{{ old('pincode') }}" placeholder="641001" class="w-full px-3.5 py-2.5 text-sm bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>
                </div>

                {{-- Google Maps URL Parser & Geolocation Section --}}
                <div class="mt-4 pt-4 border-t border-slate-100 dark:border-slate-800 space-y-4">
                    <div class="flex items-center justify-between">
                        <label class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                            GPS Coordinates &amp; Directions
                        </label>
                        <button type="button" @click="getCurrentLocation()" :disabled="locating" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 border border-blue-200 dark:border-blue-800 hover:bg-blue-100 transition active:scale-95 cursor-pointer">
                            <svg class="w-4 h-4" :class="locating ? 'animate-spin' : ''" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 11c0 3.517-1.009 6.799-2.753 9.571m-3.44-2.04l.054-.09A13.916 13.916 0 008 11a4 4 0 118 0c0 1.017-.07 2.019-.203 3m-2.118 6.844A21.88 21.88 0 0015.171 17m3.839 1.132c.645-2.266.99-4.659.99-7.132A8 8 0 004 11a7.96 7.96 0 001.07 4"/></svg>
                            <span x-text="locating ? 'Detecting Location...' : 'Get Current Location (GPS)'"></span>
                        </button>
                    </div>

                    <div x-show="locError" x-cloak class="text-xs text-rose-500" x-text="locError"></div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Latitude</label>
                            <input type="number" step="any" name="latitude" x-model="lat" placeholder="11.016844" class="w-full px-3.5 py-2 text-sm bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Longitude</label>
                            <input type="number" step="any" name="longitude" x-model="lng" placeholder="76.955832" class="w-full px-3.5 py-2 text-sm bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Or Paste Google Maps Link (Auto-extracts Lat/Long)</label>
                        <input type="text" name="google_maps_url" x-model="gmapsUrl" @input.debounce.500ms="parseGoogleMapsUrl()" placeholder="https://maps.google.com/?q=11.0168,76.9558" class="w-full px-3.5 py-2 text-sm bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>
                </div>
            </div>

            {{-- Card 3: Notes --}}
            <div class="p-6 rounded-2xl bg-white dark:bg-[#0f172a] border border-slate-200/80 dark:border-slate-800 shadow-xs">
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">Site Notes &amp; Access Instructions</label>
                <textarea name="notes" rows="3" placeholder="Security gate entry requirements, contact guard on arrival, ladder available on site..." class="w-full px-3.5 py-2.5 text-sm bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none">{{ old('notes') }}</textarea>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4">
                <a href="{{ route('sites.index') }}" class="btn-secondary text-sm">Cancel</a>
                <button type="submit" class="btn-primary text-sm shadow-md px-6 py-2.5">Save Installation Site</button>
            </div>
        </form>
    </div>
</x-app-layout>
