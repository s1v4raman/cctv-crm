<x-app-layout>
    <x-slot name="header">
        <div>
            <a href="{{ request('return_to', old('return_to')) === 'quotation' || request('for_quote', old('for_quote')) ? route('quotations.create-general') : route('leads.index') }}" class="back-link">
                ← {{ request('return_to', old('return_to')) === 'quotation' || request('for_quote', old('for_quote')) ? 'Back to Select Customer' : 'Back' }}
            </a>
            <h2 class="text-xl font-bold text-slate-900 dark:text-white font-heading tracking-tight">
                {{ request('return_to', old('return_to')) === 'quotation' || request('for_quote', old('for_quote')) ? 'New Customer for Quotation' : 'New Lead' }}
            </h2>
        </div>
    </x-slot>

    <div class="py-6 px-4 sm:px-6 lg:px-8 max-w-2xl mx-auto">
        @if ($errors->any())
            <div class="mb-4 p-3 rounded border border-rose-300 bg-rose-50 text-rose-800 text-xs">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        {{-- Section: 1 white box, 1px border, 4px radius, 16px padding --}}
        <div class="crm-section">
            <form method="POST" action="{{ route('leads.store') }}">
                @csrf
                @if(request('return_to', old('return_to')) === 'quotation' || request('for_quote', old('for_quote')))
                    <input type="hidden" name="for_quote" value="1">
                    <input type="hidden" name="return_to" value="quotation">
                @endif

                <div class="space-y-4">
                    {{-- Customer Name and Phone in a row --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="customer_name">
                                Customer Name <span class="required-marker">*</span>
                            </label>
                            <input type="text" name="customer_name" id="customer_name" value="{{ old('customer_name') }}"
                                   placeholder="e.g. Raman Kumar" required class="w-full">
                            @error('customer_name')
                                <p class="error-text">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="phone">
                                Phone <span class="required-marker">*</span>
                            </label>
                            <input type="text" name="phone" id="phone" value="{{ old('phone') }}"
                                   placeholder="e.g. 9876543210" required class="w-full">
                            @error('phone')
                                <p class="error-text">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Email and Source in a row --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="email">
                                Email
                            </label>
                            <input type="email" name="email" id="email" value="{{ old('email') }}"
                                   placeholder="e.g. raman@example.com" class="w-full">
                            @error('email')
                                <p class="error-text">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="source">
                                Source
                            </label>
                            <select name="source" id="source" class="w-full">
                                <option value="">Select source</option>
                                <option value="referral" @selected(old('source') === 'referral')>Referral</option>
                                <option value="walk-in" @selected(old('source') === 'walk-in')>Walk-in</option>
                                <option value="call" @selected(old('source') === 'call')>Phone Call</option>
                                <option value="website" @selected(old('source') === 'website')>Website</option>
                            </select>
                        </div>
                    </div>

                    {{-- Site Address --}}
                    <div>
                        <label for="site_address">
                            Site Address
                        </label>
                        <textarea name="site_address" id="site_address" rows="3"
                                  placeholder="e.g. 123 MG Road, Bengaluru"
                                  style="border-radius: 4px; box-sizing: border-box; width: 100%; padding: 10px; border: 1px solid #ccc; font-family: Arial, sans-serif; font-size: 14px;"
                                  class="resize-vertical">{{ old('site_address') }}</textarea>
                    </div>

                    {{-- Notes --}}
                    <div>
                        <label for="notes">
                            Notes
                        </label>
                        <textarea name="notes" id="notes" rows="3"
                                  placeholder="e.g. Customer prefers morning site visit"
                                  style="border-radius: 4px; box-sizing: border-box; width: 100%; padding: 10px; border: 1px solid #ccc; font-family: Arial, sans-serif; font-size: 14px;"
                                  class="resize-vertical">{{ old('notes') }}</textarea>
                    </div>
                </div>

                {{-- In forms, buttons are right-aligned: Cancel on the left, primary on the right --}}
                <div class="form-actions">
                    <a href="{{ request('return_to', old('return_to')) === 'quotation' || request('for_quote', old('for_quote')) ? route('quotations.create-general') : route('leads.index') }}" class="btn-secondary">
                        Cancel
                    </a>
                    <button type="submit" class="btn-primary">
                        {{ request('return_to', old('return_to')) === 'quotation' || request('for_quote', old('for_quote')) ? 'Save & Continue to Quotation →' : 'Save' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>