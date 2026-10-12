<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-bold text-slate-900 dark:text-white font-heading tracking-tight">New Lead</h2>
            <a href="{{ route('leads.index') }}" class="crm-back-link text-sm font-medium text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 transition">
                ← Back to Leads
            </a>
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

        {{-- Form Card: 1px border, 4px radius, white background --}}
        <div class="crm-card bg-white dark:bg-[#0f172a] rounded-[4px] border border-slate-200 dark:border-slate-800 p-6 shadow-xs">
            <form method="POST" action="{{ route('leads.store') }}">
                @csrf

                <div class="space-y-4">
                    {{-- Customer Name and Phone in a row --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="customer_name" class="crm-label text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5 block">
                                Customer Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="customer_name" id="customer_name" value="{{ old('customer_name') }}"
                                   placeholder="e.g. Raman Kumar" required
                                   style="height: 38px; min-height: 38px; max-height: 38px; border-radius: 4px; box-sizing: border-box;"
                                   class="crm-input w-full px-3 text-sm border border-slate-300 dark:border-slate-700 bg-white dark:bg-[#0b1120] text-slate-900 dark:text-white focus:outline-none focus:border-blue-500">
                            @error('customer_name')
                                <p class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="phone" class="crm-label text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5 block">
                                Phone <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="phone" id="phone" value="{{ old('phone') }}"
                                   placeholder="10-digit mobile" required
                                   style="height: 38px; min-height: 38px; max-height: 38px; border-radius: 4px; box-sizing: border-box;"
                                   class="crm-input w-full px-3 text-sm border border-slate-300 dark:border-slate-700 bg-white dark:bg-[#0b1120] text-slate-900 dark:text-white focus:outline-none focus:border-blue-500">
                            @error('phone')
                                <p class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Email and Source in a row --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="email" class="crm-label text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5 block">
                                Email
                            </label>
                            <input type="email" name="email" id="email" value="{{ old('email') }}"
                                   placeholder="Optional"
                                   style="height: 38px; min-height: 38px; max-height: 38px; border-radius: 4px; box-sizing: border-box;"
                                   class="crm-input w-full px-3 text-sm border border-slate-300 dark:border-slate-700 bg-white dark:bg-[#0b1120] text-slate-900 dark:text-white focus:outline-none focus:border-blue-500">
                            @error('email')
                                <p class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="source" class="crm-label text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5 block">
                                Source
                            </label>
                            <select name="source" id="source"
                                    style="height: 38px; min-height: 38px; max-height: 38px; border-radius: 4px; box-sizing: border-box;"
                                    class="crm-select w-full px-3 text-sm border border-slate-300 dark:border-slate-700 bg-white dark:bg-[#0b1120] text-slate-900 dark:text-white focus:outline-none focus:border-blue-500">
                                <option value="">Select source…</option>
                                <option value="referral" @selected(old('source') === 'referral')>Referral</option>
                                <option value="walk-in" @selected(old('source') === 'walk-in')>Walk-in</option>
                                <option value="call" @selected(old('source') === 'call')>Phone Call</option>
                                <option value="website" @selected(old('source') === 'website')>Website</option>
                            </select>
                        </div>
                    </div>

                    {{-- Site Address --}}
                    <div>
                        <label for="site_address" class="crm-label text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5 block">
                            Site Address
                        </label>
                        <textarea name="site_address" id="site_address" rows="3"
                                  placeholder="Installation site address…"
                                  style="border-radius: 4px; box-sizing: border-box;"
                                  class="crm-textarea w-full p-3 text-sm border border-slate-300 dark:border-slate-700 bg-white dark:bg-[#0b1120] text-slate-900 dark:text-white focus:outline-none focus:border-blue-500 resize-vertical">{{ old('site_address') }}</textarea>
                    </div>

                    {{-- Notes --}}
                    <div>
                        <label for="notes" class="crm-label text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5 block">
                            Notes
                        </label>
                        <textarea name="notes" id="notes" rows="3"
                                  placeholder="Any additional notes or requirements…"
                                  style="border-radius: 4px; box-sizing: border-box;"
                                  class="crm-textarea w-full p-3 text-sm border border-slate-300 dark:border-slate-700 bg-white dark:bg-[#0b1120] text-slate-900 dark:text-white focus:outline-none focus:border-blue-500 resize-vertical">{{ old('notes') }}</textarea>
                    </div>
                </div>

                {{-- Action buttons: Cancel plain outlined, Create Lead flat blue --}}
                <div class="mt-6 flex items-center justify-end gap-3">
                    <a href="{{ route('leads.index') }}" 
                       style="height: 38px; min-height: 38px; max-height: 38px; border-radius: 4px; box-sizing: border-box;"
                       class="crm-btn-secondary px-4 border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 text-sm font-medium hover:bg-slate-50 dark:hover:bg-slate-700 transition inline-flex items-center">
                        Cancel
                    </a>
                    <button type="submit" 
                            style="height: 38px; min-height: 38px; max-height: 38px; border-radius: 4px; background-color: #2563eb; box-sizing: border-box;"
                            class="crm-btn-primary px-5 text-sm font-semibold text-white hover:bg-blue-700 transition shadow-xs">
                        Create Lead
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>