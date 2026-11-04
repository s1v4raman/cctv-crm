<x-app-layout>
    <x-slot name="header">
        <div>
            <a href="{{ route('sites.index') }}" class="back-link">&larr; Back</a>
            <h2 class="text-xl font-bold text-slate-900 dark:text-white mt-1">Register Installation Site</h2>
        </div>
    </x-slot>

    <div class="py-6 px-4 sm:px-6 max-w-[800px] mx-auto"
         x-data="{
             lat: '{{ old('latitude') }}',
             lng: '{{ old('longitude') }}',
             gmapsUrl: '{{ old('google_maps_url') }}',
             clientPhone: '{{ old('client_phone') }}',
             siteName: '{{ old('name') }}',
             clientName: '{{ old('client_name') }}',
             contactPerson: '{{ old('contact_person', old('client_contact_person')) }}',
             clientEmail: '{{ old('client_email') }}',
             selectedLeadId: '{{ old('lead_id', request('lead_id', '')) }}',
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
             address: '{{ old('address') }}',
             city: '{{ old('city') }}',
             state: '{{ old('state') }}',
             pincode: '{{ old('pincode') }}',
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
            <div x-show="duplicateWarning" x-cloak class="mb-4 p-4 rounded-[4px] bg-amber-50 border border-amber-300 text-amber-800 text-sm">
                <span class="font-bold">Potential Duplicate Site Warning:</span>
                <span x-text="duplicateWarning"></span>
            </div>

            @if ($errors->any())
                <div class="mb-4 p-4 rounded-[4px] bg-red-50 border border-red-200 text-[#dc2626] text-sm">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('sites.store') }}">
                @csrf

                {{-- Site Core Details --}}
                <div class="crm-section mb-4">
                    <h3>Site Details</h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                        <div class="sm:col-span-2">
                            <label class="crm-label">Site Name / Location Identifier <span class="required-marker">*</span></label>
                            <input aria-label="Site Name / Location Identifier" type="text" name="name" x-model="siteName" @blur="checkDuplicate()" value="{{ old('name') }}" required placeholder="e.g. Apex Hospital - Admin Block" class="crm-input">
                            @error('name') <p class="error-text">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="crm-label">Link to CRM Lead / Client</label>
                            <select aria-label="Link to CRM Lead / Client" name="lead_id" x-model="selectedLeadId" @change="onLeadChange()" class="crm-select">
                                <option value="">-- Standalone Site / Select Client --</option>
                                @foreach($leads as $l)
                                    <option value="{{ $l->id }}" @selected(old('lead_id', request('lead_id')) == $l->id)>
                                        {{ $l->customer_name }} ({{ $l->company_name ?? $l->lead_number ?? 'Lead' }})
                                    </option>
                                @endforeach
                            </select>
                            @error('lead_id') <p class="error-text">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="crm-label">Client / Organization Name</label>
                            <input aria-label="Client / Organization Name" type="text" name="client_name" x-model="clientName" value="{{ old('client_name') }}" placeholder="e.g. Anand Textiles Pvt Ltd" class="crm-input">
                            @error('client_name') <p class="error-text">{{ $message }}</p> @enderror
                        </div>

                        <div class="sm:col-span-2">
                            <label class="crm-label">Building / Structure Type <span class="required-marker">*</span></label>
                            <select aria-label="Building / Structure Type" name="type" required class="crm-select">
                                @foreach(['commercial' => 'Commercial / Office', 'industrial' => 'Industrial / Factory', 'residential' => 'Residential / Apartment', 'warehouse' => 'Warehouse / Logistics', 'retail' => 'Retail / Showroom', 'hospital' => 'Hospital / Healthcare', 'education' => 'School / College', 'other' => 'Other Facility'] as $k => $label)
                                    <option value="{{ $k }}" @selected(old('type') == $k)>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('type') <p class="error-text">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="crm-label">Physical Address <span class="required-marker">*</span></label>
                        <textarea aria-label="Physical Address" name="address" x-model="address" rows="2" required placeholder="e.g. Plot 42, Electronics City Phase 1" class="crm-input" style="height: auto;">{{ old('address') }}</textarea>
                        @error('address') <p class="error-text">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="crm-label">City <span class="required-marker">*</span></label>
                            <input aria-label="City" type="text" name="city" x-model="city" value="{{ old('city') }}" required placeholder="e.g. Bengaluru" class="crm-input">
                            @error('city') <p class="error-text">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="crm-label">State</label>
                            <input aria-label="State" type="text" name="state" x-model="state" value="{{ old('state', 'Karnataka') }}" placeholder="e.g. Karnataka" class="crm-input">
                            @error('state') <p class="error-text">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="crm-label">PIN Code</label>
                            <input aria-label="PIN Code" type="text" name="pincode" x-model="pincode" value="{{ old('pincode') }}" placeholder="e.g. 560100" class="crm-input">
                            @error('pincode') <p class="error-text">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                {{-- Site Contact & Location --}}
                <div class="crm-section mb-4">
                    <h3>Contact &amp; Coordinates</h3>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-4">
                        <div>
                            <label class="crm-label">Site Contact Person</label>
                            <input aria-label="Site Contact Person" type="text" name="contact_person" x-model="contactPerson" value="{{ old('contact_person', old('client_contact_person')) }}" placeholder="e.g. Suresh Kumar (Manager)" class="crm-input">
                        </div>

                        <div>
                            <label class="crm-label">Site Contact Phone</label>
                            <input aria-label="Site Contact Phone" type="text" name="client_phone" x-model="clientPhone" @blur="checkDuplicate()" value="{{ old('client_phone') }}" placeholder="e.g. 9840123456" class="crm-input">
                        </div>

                        <div>
                            <label class="crm-label">Client Email Address</label>
                            <input aria-label="Client Email Address" type="email" name="client_email" x-model="clientEmail" value="{{ old('client_email') }}" placeholder="e.g. contact@client.com" class="crm-input">
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
                            <input aria-label="Latitude" type="number" step="any" name="latitude" x-model="lat" placeholder="e.g. 11.016844" class="crm-input">
                        </div>

                        <div>
                            <label class="crm-label">Longitude</label>
                            <input aria-label="Longitude" type="number" step="any" name="longitude" x-model="lng" placeholder="e.g. 76.955832" class="crm-input">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="crm-label">Google Maps Link</label>
                        <input aria-label="Google Maps Link" type="text" name="google_maps_url" x-model="gmapsUrl" @input.debounce.500ms="parseGoogleMapsUrl()" placeholder="e.g. https://maps.google.com/?q=11.0168,76.9558" class="crm-input">
                    </div>

                    <div>
                        <label class="crm-label">Site Notes &amp; Access Instructions</label>
                        <textarea aria-label="Site Notes &amp; Access Instructions" name="notes" rows="3" placeholder="e.g. Security gate entry requirements, contact guard on arrival" class="crm-input" style="height: auto;">{{ old('notes') }}</textarea>
                    </div>
                </div>

                <div class="form-actions">
                    <a href="{{ route('sites.index') }}" class="btn-secondary">Cancel</a>
                    <button type="submit" class="btn-primary">Save</button>
                </div>
            </form>
        </div>
</x-app-layout>
