<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <a href="{{ route('finance.petty_cash.index') }}" class="inline-flex items-center p-2 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                </a>
                <div>
                    <h2 class="font-bold text-2xl text-gray-900 leading-tight flex items-center gap-2">
                        <span>Physical Cash Reconciliation & Tally</span>
                        <span class="text-xs px-2.5 py-1 bg-amber-100 text-amber-800 font-semibold rounded-full uppercase tracking-wider">Daily Audit</span>
                    </h2>
                    <p class="text-sm text-gray-500 mt-0.5">Account: <span class="font-semibold text-gray-800">{{ $account->name }}</span> ({{ $account->account_type === 'main_vault' ? 'Main Safe Vault' : 'Technician Float Wallet' }})</p>
                </div>
            </div>
            <div class="text-right">
                <span class="text-xs text-gray-500 uppercase tracking-wider font-semibold block">System Expected Balance</span>
                <span class="text-2xl font-black text-indigo-700">₹{{ number_format($expectedBalance, 2) }}</span>
            </div>
        </div>
    </x-slot>

    <div class="py-8 bg-gray-50/50 min-h-screen" x-data="reconcileCounter({{ $expectedBalance }})">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            @if(session('success') || session('status'))
                <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 font-medium flex items-center justify-between shadow-sm">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>{{ session('success') ?? session('status') }}</span>
                    </div>
                </div>
            @endif

            @if($errors->any())
                <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 shadow-sm space-y-1">
                    <div class="font-semibold flex items-center gap-2">
                        <svg class="w-5 h-5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Please correct the errors below:</span>
                    </div>
                    <ul class="list-disc list-inside text-sm pl-2">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('finance.petty_cash.reconcile.store', $account) }}" method="POST">
                @csrf

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    
                    <!-- Left: Denomination Counter (2 cols) -->
                    <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-6">
                        <div class="flex items-center justify-between border-b border-gray-100 pb-4">
                            <div>
                                <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                                    <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                    <span>Indian Rupee (INR) Denomination Breakdown</span>
                                </h3>
                                <p class="text-xs text-gray-500 mt-0.5">Input physical note count for each note/coin tier.</p>
                            </div>
                            <button type="button" @click="resetDenominations()" class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">Clear All</button>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <!-- High Value Notes -->
                            <div class="space-y-3">
                                <div class="text-xs font-bold text-gray-400 uppercase tracking-wider border-b border-gray-100 pb-1">High Value Notes</div>
                                
                                <!-- ₹2000 -->
                                <div class="flex items-center justify-between p-3 rounded-xl bg-pink-50/50 border border-pink-100 hover:border-pink-300 transition">
                                    <div class="w-24">
                                        <span class="font-bold text-pink-900 text-sm">₹ 2,000</span>
                                        <span class="block text-[10px] text-pink-600">Note</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs text-gray-400">×</span>
                                        <input type="number" name="denominations[2000]" x-model.number="denominations['2000']" min="0" class="w-20 rounded-lg border-gray-200 text-right font-semibold text-sm focus:border-pink-500 focus:ring-pink-500">
                                    </div>
                                    <div class="w-24 text-right font-bold text-gray-900 text-sm" x-text="formatCurrency((denominations['2000'] || 0) * 2000)"></div>
                                </div>

                                <!-- ₹500 -->
                                <div class="flex items-center justify-between p-3 rounded-xl bg-amber-50/50 border border-amber-100 hover:border-amber-300 transition">
                                    <div class="w-24">
                                        <span class="font-bold text-amber-900 text-sm">₹ 500</span>
                                        <span class="block text-[10px] text-amber-600">Note</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs text-gray-400">×</span>
                                        <input type="number" name="denominations[500]" x-model.number="denominations['500']" min="0" class="w-20 rounded-lg border-gray-200 text-right font-semibold text-sm focus:border-amber-500 focus:ring-amber-500">
                                    </div>
                                    <div class="w-24 text-right font-bold text-gray-900 text-sm" x-text="formatCurrency((denominations['500'] || 0) * 500)"></div>
                                </div>

                                <!-- ₹200 -->
                                <div class="flex items-center justify-between p-3 rounded-xl bg-orange-50/50 border border-orange-100 hover:border-orange-300 transition">
                                    <div class="w-24">
                                        <span class="font-bold text-orange-900 text-sm">₹ 200</span>
                                        <span class="block text-[10px] text-orange-600">Note</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs text-gray-400">×</span>
                                        <input type="number" name="denominations[200]" x-model.number="denominations['200']" min="0" class="w-20 rounded-lg border-gray-200 text-right font-semibold text-sm focus:border-orange-500 focus:ring-orange-500">
                                    </div>
                                    <div class="w-24 text-right font-bold text-gray-900 text-sm" x-text="formatCurrency((denominations['200'] || 0) * 200)"></div>
                                </div>

                                <!-- ₹100 -->
                                <div class="flex items-center justify-between p-3 rounded-xl bg-violet-50/50 border border-violet-100 hover:border-violet-300 transition">
                                    <div class="w-24">
                                        <span class="font-bold text-violet-900 text-sm">₹ 100</span>
                                        <span class="block text-[10px] text-violet-600">Note</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs text-gray-400">×</span>
                                        <input type="number" name="denominations[100]" x-model.number="denominations['100']" min="0" class="w-20 rounded-lg border-gray-200 text-right font-semibold text-sm focus:border-violet-500 focus:ring-violet-500">
                                    </div>
                                    <div class="w-24 text-right font-bold text-gray-900 text-sm" x-text="formatCurrency((denominations['100'] || 0) * 100)"></div>
                                </div>

                                <!-- ₹50 -->
                                <div class="flex items-center justify-between p-3 rounded-xl bg-cyan-50/50 border border-cyan-100 hover:border-cyan-300 transition">
                                    <div class="w-24">
                                        <span class="font-bold text-cyan-900 text-sm">₹ 50</span>
                                        <span class="block text-[10px] text-cyan-600">Note</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs text-gray-400">×</span>
                                        <input type="number" name="denominations[50]" x-model.number="denominations['50']" min="0" class="w-20 rounded-lg border-gray-200 text-right font-semibold text-sm focus:border-cyan-500 focus:ring-cyan-500">
                                    </div>
                                    <div class="w-24 text-right font-bold text-gray-900 text-sm" x-text="formatCurrency((denominations['50'] || 0) * 50)"></div>
                                </div>
                            </div>

                            <!-- Small Notes & Coins -->
                            <div class="space-y-3">
                                <div class="text-xs font-bold text-gray-400 uppercase tracking-wider border-b border-gray-100 pb-1">Small Notes & Loose Cash</div>

                                <!-- ₹20 -->
                                <div class="flex items-center justify-between p-3 rounded-xl bg-emerald-50/50 border border-emerald-100 hover:border-emerald-300 transition">
                                    <div class="w-24">
                                        <span class="font-bold text-emerald-900 text-sm">₹ 20</span>
                                        <span class="block text-[10px] text-emerald-600">Note / Coin</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs text-gray-400">×</span>
                                        <input type="number" name="denominations[20]" x-model.number="denominations['20']" min="0" class="w-20 rounded-lg border-gray-200 text-right font-semibold text-sm focus:border-emerald-500 focus:ring-emerald-500">
                                    </div>
                                    <div class="w-24 text-right font-bold text-gray-900 text-sm" x-text="formatCurrency((denominations['20'] || 0) * 20)"></div>
                                </div>

                                <!-- ₹10 -->
                                <div class="flex items-center justify-between p-3 rounded-xl bg-yellow-50/50 border border-yellow-100 hover:border-yellow-300 transition">
                                    <div class="w-24">
                                        <span class="font-bold text-yellow-900 text-sm">₹ 10</span>
                                        <span class="block text-[10px] text-yellow-600">Note / Coin</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs text-gray-400">×</span>
                                        <input type="number" name="denominations[10]" x-model.number="denominations['10']" min="0" class="w-20 rounded-lg border-gray-200 text-right font-semibold text-sm focus:border-yellow-500 focus:ring-yellow-500">
                                    </div>
                                    <div class="w-24 text-right font-bold text-gray-900 text-sm" x-text="formatCurrency((denominations['10'] || 0) * 10)"></div>
                                </div>

                                <!-- ₹5 -->
                                <div class="flex items-center justify-between p-3 rounded-xl bg-gray-50 border border-gray-200 hover:border-gray-400 transition">
                                    <div class="w-24">
                                        <span class="font-bold text-gray-900 text-sm">₹ 5</span>
                                        <span class="block text-[10px] text-gray-500">Coin / Note</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs text-gray-400">×</span>
                                        <input type="number" name="denominations[5]" x-model.number="denominations['5']" min="0" class="w-20 rounded-lg border-gray-200 text-right font-semibold text-sm focus:border-gray-500 focus:ring-gray-500">
                                    </div>
                                    <div class="w-24 text-right font-bold text-gray-900 text-sm" x-text="formatCurrency((denominations['5'] || 0) * 5)"></div>
                                </div>

                                <!-- ₹2 -->
                                <div class="flex items-center justify-between p-3 rounded-xl bg-gray-50 border border-gray-200 hover:border-gray-400 transition">
                                    <div class="w-24">
                                        <span class="font-bold text-gray-900 text-sm">₹ 2</span>
                                        <span class="block text-[10px] text-gray-500">Coin</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs text-gray-400">×</span>
                                        <input type="number" name="denominations[2]" x-model.number="denominations['2']" min="0" class="w-20 rounded-lg border-gray-200 text-right font-semibold text-sm focus:border-gray-500 focus:ring-gray-500">
                                    </div>
                                    <div class="w-24 text-right font-bold text-gray-900 text-sm" x-text="formatCurrency((denominations['2'] || 0) * 2)"></div>
                                </div>

                                <!-- ₹1 -->
                                <div class="flex items-center justify-between p-3 rounded-xl bg-gray-50 border border-gray-200 hover:border-gray-400 transition">
                                    <div class="w-24">
                                        <span class="font-bold text-gray-900 text-sm">₹ 1</span>
                                        <span class="block text-[10px] text-gray-500">Coin</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs text-gray-400">×</span>
                                        <input type="number" name="denominations[1]" x-model.number="denominations['1']" min="0" class="w-20 rounded-lg border-gray-200 text-right font-semibold text-sm focus:border-gray-500 focus:ring-gray-500">
                                    </div>
                                    <div class="w-24 text-right font-bold text-gray-900 text-sm" x-text="formatCurrency((denominations['1'] || 0) * 1)"></div>
                                </div>

                                <!-- Loose Coins Total -->
                                <div class="flex items-center justify-between p-3 rounded-xl bg-gray-50 border border-gray-200 hover:border-gray-400 transition">
                                    <div class="w-24">
                                        <span class="font-bold text-gray-900 text-sm">Loose Coins</span>
                                        <span class="block text-[10px] text-gray-500">Direct Value (₹)</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs text-gray-400">₹</span>
                                        <input type="number" step="0.01" name="denominations[coins]" x-model.number="denominations['coins']" min="0" class="w-20 rounded-lg border-gray-200 text-right font-semibold text-sm focus:border-gray-500 focus:ring-gray-500">
                                    </div>
                                    <div class="w-24 text-right font-bold text-gray-900 text-sm" x-text="formatCurrency(denominations['coins'] || 0)"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right: Audit Summary & Actions (1 col) -->
                    <div class="space-y-6">
                        
                        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-5">
                            <h3 class="text-base font-bold text-gray-900 pb-2 border-b border-gray-100 flex items-center justify-between">
                                <span>Reconciliation Summary</span>
                                <span class="text-xs text-gray-400 font-normal">{{ date('d M Y') }}</span>
                            </h3>

                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Reconciliation Date *</label>
                                    <input type="date" name="reconciliation_date" value="{{ date('Y-m-d') }}" required class="w-full rounded-xl border-gray-200 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </div>

                                <div class="p-4 rounded-xl bg-gray-50 border border-gray-200 space-y-3">
                                    <div class="flex justify-between items-center text-sm">
                                        <span class="text-gray-600">Book Balance:</span>
                                        <span class="font-bold text-gray-900">₹{{ number_format($expectedBalance, 2) }}</span>
                                    </div>
                                    <div class="flex justify-between items-center text-sm">
                                        <span class="text-gray-600">Physical Cash Count:</span>
                                        <span class="font-bold text-indigo-700" x-text="formatCurrency(totalPhysicalCash)"></span>
                                    </div>
                                    <div class="pt-2 border-t border-gray-200 flex justify-between items-center">
                                        <span class="text-xs font-bold uppercase tracking-wider text-gray-500">Variance:</span>
                                        <span class="text-base font-black" :class="varianceClass" x-text="varianceText"></span>
                                    </div>
                                </div>

                                <!-- Dynamic Variance Status Alert -->
                                <div class="p-3.5 rounded-xl text-xs font-medium" :class="varianceBannerClass">
                                    <div class="flex items-start gap-2">
                                        <svg class="w-4 h-4 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        <span x-text="varianceMessage"></span>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Remarks / Variance Explanation</label>
                                    <textarea name="reconciliation_notes" rows="3" placeholder="Explain reasons if there is any shortage/excess, or notes on physical count verification..." class="w-full rounded-xl border-gray-200 text-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                                </div>
                            </div>

                            <div class="pt-3 border-t border-gray-100 flex flex-col gap-2">
                                <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm rounded-xl shadow-sm transition">
                                    Confirm & Save Reconciliation
                                </button>
                                <a href="{{ route('finance.petty_cash.index') }}" class="w-full inline-flex items-center justify-center px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold text-sm rounded-xl transition">
                                    Cancel
                                </a>
                            </div>
                        </div>

                        <!-- Previous Reconciliations -->
                        @if($account->reconciliations->count() > 0)
                            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 space-y-3">
                                <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider">Past Tallies for this Account</h4>
                                <div class="divide-y divide-gray-100 text-xs">
                                    @foreach($account->reconciliations->sortByDesc('reconciliation_date')->take(3) as $recon)
                                        <div class="py-2.5 flex items-center justify-between">
                                            <div>
                                                <div class="font-semibold text-gray-800">{{ $recon->reconciliation_date->format('d M Y') }}</div>
                                                <div class="text-gray-400">{{ $recon->reconciliation_no }} by {{ $recon->verifiedBy->name ?? 'Office Cashier' }}</div>
                                            </div>
                                            <div class="text-right">
                                                <div class="font-bold text-gray-900">₹{{ number_format($recon->physical_counted_balance, 2) }}</div>
                                                @if($recon->variance_status === 'matched')
                                                    <span class="text-[10px] font-bold text-emerald-600">Exact Match</span>
                                                @elseif($recon->variance_status === 'shortage')
                                                    <span class="text-[10px] font-bold text-rose-600">-₹{{ number_format(abs($recon->variance_amount), 2) }} Short</span>
                                                @else
                                                    <span class="text-[10px] font-bold text-amber-600">+₹{{ number_format($recon->variance_amount, 2) }} Excess</span>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                    </div>

                </div>
            </form>

        </div>
    </div>

    <script>
        function reconcileCounter(expected) {
            return {
                systemExpected: parseFloat(expected) || 0,
                denominations: {
                    '2000': '',
                    '500': '',
                    '200': '',
                    '100': '',
                    '50': '',
                    '20': '',
                    '10': '',
                    '5': '',
                    '2': '',
                    '1': '',
                    'coins': ''
                },
                get totalPhysicalCash() {
                    let total = 0;
                    total += (parseFloat(this.denominations['2000']) || 0) * 2000;
                    total += (parseFloat(this.denominations['500']) || 0) * 500;
                    total += (parseFloat(this.denominations['200']) || 0) * 200;
                    total += (parseFloat(this.denominations['100']) || 0) * 100;
                    total += (parseFloat(this.denominations['50']) || 0) * 50;
                    total += (parseFloat(this.denominations['20']) || 0) * 20;
                    total += (parseFloat(this.denominations['10']) || 0) * 10;
                    total += (parseFloat(this.denominations['5']) || 0) * 5;
                    total += (parseFloat(this.denominations['2']) || 0) * 2;
                    total += (parseFloat(this.denominations['1']) || 0) * 1;
                    total += (parseFloat(this.denominations['coins']) || 0);
                    return total;
                },
                get variance() {
                    return this.totalPhysicalCash - this.systemExpected;
                },
                get varianceClass() {
                    if (Math.abs(this.variance) < 0.01) return 'text-emerald-600';
                    return this.variance < 0 ? 'text-rose-600' : 'text-amber-600';
                },
                get varianceText() {
                    if (Math.abs(this.variance) < 0.01) return '₹0.00 (Exact Match)';
                    if (this.variance < 0) return '-₹' + Math.abs(this.variance).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' (Shortage)';
                    return '+₹' + this.variance.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' (Excess)';
                },
                get varianceBannerClass() {
                    if (Math.abs(this.variance) < 0.01) return 'bg-emerald-50 text-emerald-800 border border-emerald-200';
                    return this.variance < 0 ? 'bg-rose-50 text-rose-800 border border-rose-200' : 'bg-amber-50 text-amber-800 border border-amber-200';
                },
                get varianceMessage() {
                    if (Math.abs(this.variance) < 0.01) return 'Physical cash perfectly tallies with the digital ledger records.';
                    if (this.variance < 0) return 'Shortage detected: Physical cash is less than the expected balance. Please verify missing vouchers.';
                    return 'Excess detected: Physical cash exceeds book balance. Please verify unrecorded customer collections.';
                },
                formatCurrency(val) {
                    return '₹ ' + (parseFloat(val) || 0).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                },
                resetDenominations() {
                    for (let k in this.denominations) {
                        this.denominations[k] = '';
                    }
                }
            };
        }
    </script>
</x-app-layout>
