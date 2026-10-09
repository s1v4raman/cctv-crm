<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <a href="{{ route('sites.show', $site) }}" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                </a>
                <h2 class="text-xl font-extrabold text-slate-900 dark:text-white font-heading tracking-tight">Edit Site #{{ $site->site_code }}</h2>
            </div>
        </div>
    </x-slot>

    <div class="py-6 px-4 sm:px-6 lg:px-8 max-w-4xl mx-auto"
         x-data="{
             lat: '{{ old('latitude', $site->latitude) }}',
             lng: '{{ old('longitude', $site->longitude) }}',
             gmapsUrl: '{{ old('google_maps_url', $site->google_maps_url) }}',
             locating: false,
             locError: '',
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
                 let m = this.gmapsUrl.match(/@(-?\d+\.\d+),(-?\d+\.\d+)/);
                 if (m) {
                     this.lat = parseFloat(m[1]).toFixed(6);
                     this.lng = parseFloat(m[2]).toFixed(6);
                     return;
                 }
                 m = this.gmapsUrl.match(/[?&]q=(-?\d+\.\d+),(-?\d+\.\d+)/);
                 if (m) {
                     this.lat = parseFloat(m[1]).toFixed(6);
                     this.lng = parseFloat(m[2]).toFixed(6);
                     return;
                 }
                 m = this.gmapsUrl.match(/!3d(-?\d+\.\d+)!4d(-?\d+\.\d+)/);
                 if (m) {
                     this.lat = parseFloat(m[1]).toFixed(6);
                     this.lng = parseFloat(m[2]).toFixed(6);
                 }
             }
         }">

        <form method="POST" action="{{ route('sites.update', $site) }}" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="p-6 rounded-2xl bg-white dark:bg-[#0f172a] border border-slate-200/80 dark:border-slate-800 shadow-xs space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                    <h3 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-blue-600"></span>
                        Site Identification &amp; Client Details
                    </h3>
                    <span class="px-2.5 py-1 text-xs font-mono font-bold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 rounded-lg">
                        {{ $site->site_code }}
                    </span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">
                            Site / Premises Name <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="name" value="{{ old('name', $site->name) }}" required class="w-full px-3.5 py-2.5 text-sm bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">Client / Organization Name</label>
                        <input type="text" name="client_name" value="{{ old('client_name', $site->client_name) }}" class="w-full px-3.5 py-2.5 text-sm bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">Client Phone Number</label>
                        <input type="text" name="client_phone" value="{{ old('client_phone', $site->client_phone) }}" class="w-full px-3.5 py-2.5 text-sm bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">Client Email</label>
                        <input type="email" name="client_email" value="{{ old('client_email', $site->client_email) }}" class="w-full px-3.5 py-2.5 text-sm bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">Local Contact Person</label>
                        <input type="text" name="contact_person" value="{{ old('contact_person', $site->contact_person) }}" class="w-full px-3.5 py-2.5 text-sm bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>
                </div>
            </div>

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
                        <textarea name="address" rows="2" required class="w-full px-3.5 py-2.5 text-sm bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none">{{ old('address', $site->address) }}</textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">City / Town</label>
                        <input type="text" name="city" value="{{ old('city', $site->city) }}" class="w-full px-3.5 py-2.5 text-sm bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">State</label>
                        <input type="text" name="state" value="{{ old('state', $site->state) }}" class="w-full px-3.5 py-2.5 text-sm bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">PIN Code</label>
                        <input type="text" name="pincode" value="{{ old('pincode', $site->pincode) }}" class="w-full px-3.5 py-2.5 text-sm bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>
                </div>

                <div class="mt-4 pt-4 border-t border-slate-100 dark:border-slate-800 space-y-4">
                    <div class="flex items-center justify-between">
                        <label class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                            GPS Coordinates &amp; Directions
                        </label>
                        <button type="button" @click="getCurrentLocation()" :disabled="locating" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 border border-blue-200 dark:border-blue-800 hover:bg-blue-100 transition active:scale-95 cursor-pointer">
                            <svg class="w-4 h-4" :class="locating ? 'animate-spin' : ''" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 11c0 3.517-1.009 6.799-2.753 9.571m-3.44-2.04l.054-.09A13.916 13.916 0 008 11a4 4 0 118 0c0 1.017-.07 2.019-.203 3m-2.118 6.844A21.88 21.88 0 0015.171 17m3.839 1.132c.645-2.266.99-4.659.99-7.132A8 8 0 004 11a7.96 7.96 0 001.07 4"/></svg>
                            <span x-text="locating ? 'Detecting...' : 'Get Current Location (GPS)'"></span>
                        </button>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Latitude</label>
                            <input type="number" step="any" name="latitude" x-model="lat" class="w-full px-3.5 py-2 text-sm bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Longitude</label>
                            <input type="number" step="any" name="longitude" x-model="lng" class="w-full px-3.5 py-2 text-sm bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Google Maps Link</label>
                        <input type="text" name="google_maps_url" x-model="gmapsUrl" @input.debounce.500ms="parseGoogleMapsUrl()" class="w-full px-3.5 py-2 text-sm bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>
                </div>
            </div>

            <div class="p-6 rounded-2xl bg-white dark:bg-[#0f172a] border border-slate-200/80 dark:border-slate-800 shadow-xs">
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">Site Notes &amp; Access Instructions</label>
                <textarea name="notes" rows="3" class="w-full px-3.5 py-2.5 text-sm bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none">{{ old('notes', $site->notes) }}</textarea>
            </div>

            <div class="flex items-center justify-between pt-4">
                @if(!$site->projects()->exists())
                    <button type="button" onclick="if(confirm('Are you sure you want to delete this site?')) document.getElementById('delete-site-form').submit();" class="text-rose-600 hover:text-rose-800 text-xs font-bold">
                        Delete Site
                    </button>
                @else
                    <div></div>
                @endif

                <div class="flex items-center gap-3">
                    <a href="{{ route('sites.show', $site) }}" class="btn-secondary text-sm">Cancel</a>
                    <button type="submit" class="btn-primary text-sm shadow-md px-6 py-2.5">Update Installation Site</button>
                </div>
            </div>
        </form>

        <form id="delete-site-form" method="POST" action="{{ route('sites.destroy', $site) }}" class="hidden">
            @csrf
            @method('DELETE')
        </form>
    </div>
</x-app-layout>
