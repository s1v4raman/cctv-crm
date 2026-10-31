<x-app-layout>
    <x-slot name="header">
        <div>
            <a href="{{ route('sites.show', $site) }}" class="back-link">&larr; Back</a>
            <h2 class="text-xl font-bold text-slate-900 dark:text-white mt-1">Edit Site &mdash; #{{ $site->site_code }}</h2>
        </div>
    </x-slot>

    <div class="py-6 px-4 sm:px-6 max-w-[800px] mx-auto"
         x-data="{
             lat: '{{ old('latitude', $site->latitude) }}',
             lng: '{{ old('longitude', $site->longitude) }}',
             gmapsUrl: '{{ old('google_maps_url', $site->google_maps_url ?: $site->google_maps_link) }}',
             selectedLeadId: '{{ old('lead_id', $site->lead_id) }}',
             clientName: '{{ old('client_name', $site->client_name) }}',
             contactPerson: '{{ old('contact_person', $site->contact_person) }}',
             clientPhone: '{{ old('client_phone', $site->client_phone ?: ($site->contact_phone ?: $site->phone)) }}',
             clientEmail: '{{ old('client_email', $site->client_email ?: $site->email) }}',
             address: '{{ old('address', $site->address) }}',
             city: '{{ old('city', $site->city) }}',
             state: '{{ old('state', $site->state) }}',
             pincode: '{{ old('pincode', $site->pincode) }}',
             leadsList: {{ Js::from($leads->map(fn($l) => [
                 'id' => (string)$l->id,
                 'name' => $l->company_name ?: $l->customer_name,
                 'phone' => $l->phone ?? '',
                 'email' => $l->email ?? '',
                 'contact' => $l->customer_name ?? '',
                 'address' => $l->site_address ?? ($l->address ?? ''),
                 'city' => $l->city ?? '',
                 'state' => $l->state ?? '',
                 'pincode' => $l->pincode ?? '',
             ])) }},
             onLeadChange() {
                 if (!this.selectedLeadId) return;
                 const lead = this.leadsList.find(l => l.id == this.selectedLeadId);
                 if (lead) {
                     this.clientName = lead.name;
                     if (lead.phone && !this.clientPhone) this.clientPhone = lead.phone;
                     if (lead.email && !this.clientEmail) this.clientEmail = lead.email;
                     if (lead.contact && (!this.contactPerson || this.contactPerson === 'Site Manager')) this.contactPerson = lead.contact;
                     if (lead.address && !this.address) this.address = lead.address;
                     if (lead.city && !this.city) this.city = lead.city;
                     if (lead.state && !this.state) this.state = lead.state;
                     if (lead.pincode && !this.pincode) this.pincode = lead.pincode;
                 }
             },
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

            @if ($errors->any())
                <div class="mb-4 p-4 rounded-[4px] bg-red-50 border border-red-200 text-[#dc2626] text-sm">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="crm-section">
                <form id="site-form" method="POST" action="{{ route('sites.update', $site) }}">
                    @csrf
                    @method('PUT')

                    <h3 class="mb-4 pb-2 border-b border-[#ddd] dark:border-slate-800 font-bold text-base text-gray-900 dark:text-white">Site Details</h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                        <div class="sm:col-span-2">
                            <label class="crm-label">Site Name <span class="required-marker">*</span></label>
                            <input aria-label="Site Name" type="text" name="name" value="{{ old('name', $site->name) }}" required class="crm-input">
                            @error('name') <p class="error-text">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="crm-label">Link to CRM Lead / Client</label>
                            <select aria-label="Link to CRM Lead / Client" name="lead_id" x-model="selectedLeadId" @change="onLeadChange()" class="crm-select">
                                <option value="">-- Standalone Site / Select Client --</option>
                                @foreach($leads as $l)
                                    <option value="{{ $l->id }}" @selected(old('lead_id', $site->lead_id) == $l->id)>
                                        {{ $l->customer_name }} ({{ $l->company_name ?? $l->lead_number ?? 'Lead' }})
                                    </option>
                                @endforeach
                            </select>
                            @error('lead_id') <p class="error-text">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="crm-label">Client / Organization Name</label>
                            <input aria-label="Client / Organization Name" type="text" name="client_name" x-model="clientName" placeholder="e.g. Anand Textiles Pvt Ltd" class="crm-input">
                            @error('client_name') <p class="error-text">{{ $message }}</p> @enderror
                        </div>

                        <div class="sm:col-span-2">
                            <label class="crm-label">Facility Type <span class="required-marker">*</span></label>
                            <select aria-label="Facility Type" name="type" required class="crm-select">
                                @foreach(['commercial' => 'Commercial / Office', 'industrial' => 'Industrial / Factory', 'residential' => 'Residential / Apartment', 'warehouse' => 'Warehouse / Logistics', 'retail' => 'Retail / Showroom', 'hospital' => 'Hospital / Healthcare', 'education' => 'School / College', 'other' => 'Other Facility'] as $k => $label)
                                    <option value="{{ $k }}" @selected(old('type', $site->type) == $k)>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('type') <p class="error-text">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="crm-label">Physical Address <span class="required-marker">*</span></label>
                        <textarea aria-label="Physical Address" name="address" x-model="address" rows="2" required class="crm-input" style="height: auto;"></textarea>
                        @error('address') <p class="error-text">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-4">
                        <div>
                            <label class="crm-label">City <span class="required-marker">*</span></label>
                            <input aria-label="City" type="text" name="city" x-model="city" required class="crm-input">
                            @error('city') <p class="error-text">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="crm-label">State</label>
                            <input aria-label="State" type="text" name="state" x-model="state" class="crm-input">
                        </div>

                        <div>
                            <label class="crm-label">PIN Code</label>
                            <input aria-label="PIN Code" type="text" name="pincode" x-model="pincode" class="crm-input">
                        </div>
                    </div>

                    <h3 class="mb-4 pb-2 border-b border-[#ddd] dark:border-slate-800 font-bold text-base text-gray-900 dark:text-white mt-6">Contact &amp; Location</h3>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-4">
                        <div>
                            <label class="crm-label">Site Contact Person</label>
                            <input aria-label="Site Contact Person" type="text" name="contact_person" x-model="contactPerson" placeholder="e.g. R. Murugan (Store Manager)" class="crm-input">
                        </div>

                        <div>
                            <label class="crm-label">Site Contact Phone</label>
                            <input aria-label="Site Contact Phone" type="text" name="client_phone" x-model="clientPhone" placeholder="e.g. 98765 43210" class="crm-input">
                        </div>

                        <div>
                            <label class="crm-label">Client Email Address</label>
                            <input aria-label="Client Email Address" type="email" name="client_email" x-model="clientEmail" placeholder="e.g. contact@client.com" class="crm-input">
                        </div>
                    </div>

                    <div class="flex items-center justify-between mb-2">
                        <label class="crm-label mb-0">GPS Coordinates</label>
                        <button type="button" @click="getCurrentLocation()" :disabled="locating" class="action-link text-xs" style="background: none; border: none; cursor: pointer;">
                            <span x-text="locating ? 'Detecting Location...' : 'Get Current Location (GPS)'"></span>
                        </button>
                    </div>

                    <div x-show="locError" x-cloak class="error-text mb-2" x-text="locError"></div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="crm-label">Latitude</label>
                            <input aria-label="Latitude" type="number" step="any" name="latitude" x-model="lat" class="crm-input">
                        </div>

                        <div>
                            <label class="crm-label">Longitude</label>
                            <input aria-label="Longitude" type="number" step="any" name="longitude" x-model="lng" class="crm-input">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="crm-label">Google Maps Link</label>
                        <input aria-label="Google Maps Link" type="text" name="google_maps_url" x-model="gmapsUrl" @input.debounce.500ms="parseGoogleMapsUrl()" class="crm-input">
                    </div>

                    <div class="mb-4">
                        <label class="crm-label">Site Notes &amp; Access Instructions</label>
                        <textarea aria-label="Site Notes &amp; Access Instructions" name="notes" rows="3" class="crm-input" style="height: auto;">{{ old('notes', $site->notes) }}</textarea>
                    </div>
                </form>

                @if(auth()->user()->isAdmin())
                <form id="delete-site-form" method="POST" action="{{ route('sites.destroy', $site) }}" class="hidden">
                    @csrf
                    @method('DELETE')
                </form>
                @endif

                <div class="flex items-center justify-between pt-4 border-t border-[#ddd] mt-4">
                    @if(auth()->user()->isAdmin() && !$site->projects()->exists())
                        <button type="submit" form="delete-site-form" data-confirm="Are you sure you want to delete this site?" class="danger-link" style="background: none; border: none; cursor: pointer;">
                            Delete
                        </button>
                    @else
                        <div></div>
                    @endif

                    <div class="flex items-center gap-3">
                        <a href="{{ route('sites.show', $site) }}" class="btn-secondary">Cancel</a>
                        <button type="submit" form="site-form" class="btn-primary">Save</button>
                    </div>
                </div>
            </div>
        </div>
</x-app-layout>
