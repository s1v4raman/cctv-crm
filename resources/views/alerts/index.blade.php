<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-xl font-semibold text-slate-900 dark:text-white">Alerts</h1>
                <p class="mt-0.5 text-sm text-slate-500 dark:text-slate-400">WhatsApp, SMS and email messages sent to customers and staff</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('alerts.gateways') }}" class="btn-secondary">Gateways</a>
                <a href="{{ route('alerts.templates') }}" class="btn-secondary">Templates</a>
                <form method="POST" action="{{ route('alerts.run-sweep') }}" data-confirm="Send payment reminders for all overdue invoices now?">
                    @csrf
                    <button type="submit" class="btn-primary">Send reminders</button>
                </form>
            </div>
        </div>
    </x-slot>

    <div class="py-8 bg-slate-50 dark:bg-[#060913] min-h-screen">
        <div class="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8">

            @if (session('status'))
                <div class="mb-5 p-4 rounded-xl bg-emerald-100 dark:bg-emerald-950/40 border border-emerald-300 dark:border-emerald-500/30 text-emerald-900 dark:text-emerald-300 text-sm font-medium flex items-center gap-2">
                    <span>{{ session('status') }}</span>
                </div>
            @endif

            {{-- Messages sent, by channel --}}
            <dl class="grid grid-cols-2 sm:grid-cols-5 gap-px rounded border border-slate-200 dark:border-slate-800 bg-slate-200 dark:bg-slate-800 overflow-hidden mb-6">
                @foreach([
                    ['Sent', $stats['total']],
                    ['WhatsApp', $stats['whatsapp']],
                    ['SMS', $stats['sms']],
                    ['Email', $stats['email']],
                    ['Failed', $stats['failed']],
                ] as [$label, $value])
                    <div class="bg-white dark:bg-slate-900 px-4 py-3">
                        <dt class="text-xs text-slate-500 dark:text-slate-400">{{ $label }}</dt>
                        <dd class="text-xl font-semibold mt-0.5 {{ $label === 'Failed' && $value > 0 ? 'text-rose-700 dark:text-rose-400' : 'text-slate-900 dark:text-white' }}">{{ $value }}</dd>
                    </div>
                @endforeach
            </dl>

            {{-- Send one message by hand --}}
            <div class="bg-white dark:bg-[#0f172a] rounded p-4 mb-6 border border-slate-200 dark:border-white/5" x-data="{ open: false }">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <h3 class="text-sm font-semibold text-slate-900 dark:text-white">Send a message</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">One WhatsApp, SMS or email to a customer or technician.</p>
                    </div>
                    <button type="button" @click="open = !open" class="btn-secondary" :aria-expanded="open">
                        <span x-show="!open">Write message</span>
                        <span x-show="open" x-cloak>Close</span>
                    </button>
                </div>

                <div x-show="open" x-cloak class="mt-4 pt-4 border-t border-slate-200 dark:border-white/5">
                    <form method="POST" action="{{ route('alerts.broadcast') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
                        @csrf
                        <div>
                            <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Dispatch Channel <span class="text-rose-500">*</span></label>
                            <select aria-label="Dispatch Channel" name="channel" required class="w-full text-sm min-h-[44px] px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-[#060913] border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-slate-200 focus:border-blue-500 focus:ring-blue-500 font-semibold">
                                <option value="whatsapp">WhatsApp Direct</option>
                                <option value="email">Email Notification</option>
                                <option value="sms">SMS Text</option>
                            </select>
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Recipient Name <span class="text-rose-500">*</span></label>
                            <input aria-label="Recipient Name" type="text" name="recipient_name" required placeholder="e.g. John Doe" class="w-full text-sm min-h-[44px] px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-[#060913] border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-slate-200 focus:border-blue-500 focus:ring-blue-500">
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Recipient Phone / Mobile</label>
                            <input aria-label="Recipient Phone / Mobile" type="text" name="phone" placeholder="e.g. 9876543210" class="w-full text-sm min-h-[44px] px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-[#060913] border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-slate-200 focus:border-blue-500 focus:ring-blue-500">
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Email Subject (If sending email)</label>
                            <input aria-label="Email Subject (If sending email)" type="text" name="subject" placeholder="e.g. Important Service Notification regarding your CCTV setup" class="w-full text-sm min-h-[44px] px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-[#060913] border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-slate-200 focus:border-blue-500 focus:ring-blue-500">
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Recipient Email (If sending email)</label>
                            <input aria-label="Recipient Email (If sending email)" type="email" name="email" placeholder="e.g. customer@example.com" class="w-full text-sm min-h-[44px] px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-[#060913] border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-slate-200 focus:border-blue-500 focus:ring-blue-500">
                        </div>

                        <div class="sm:col-span-3">
                            <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Message Body <span class="text-rose-500">*</span></label>
                            <textarea aria-label="Message Body" name="message" rows="3" required placeholder="Type your message content here..." class="w-full text-sm min-h-[44px] px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-[#060913] border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-slate-200 focus:border-blue-500 focus:ring-blue-500"></textarea>
                        </div>

                        <div class="sm:col-span-3 flex justify-end">
                            <button type="submit" class="btn-primary">
                                Send message
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Filter Bar --}}
            <div class="bg-white dark:bg-[#0f172a] rounded-2xl p-4 mb-6 shadow-xs border border-slate-200 dark:border-white/5">
                <form method="GET" action="{{ route('alerts.index') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Search Logs</label>
                        <input aria-label="Search Logs" type="text" name="search" value="{{ request('search') }}" placeholder="Recipient, Phone, Message text..." class="w-full text-sm min-h-[44px] px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-[#060913] border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-slate-200 focus:border-amber-500 focus:ring-amber-500">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Channel</label>
                        <select aria-label="Channel" name="channel" class="w-full text-sm min-h-[44px] px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-[#060913] border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-slate-200 focus:border-amber-500 focus:ring-amber-500">
                            <option value="">All Channels</option>
                            <option value="whatsapp" @selected(request('channel') === 'whatsapp')>WhatsApp</option>
                            <option value="sms" @selected(request('channel') === 'sms')>SMS</option>
                            <option value="email" @selected(request('channel') === 'email')>Email</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Status</label>
                        <select aria-label="Status" name="status" class="w-full text-sm min-h-[44px] px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-[#060913] border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-slate-200 focus:border-amber-500 focus:ring-amber-500">
                            <option value="">All Statuses</option>
                            <option value="sent" @selected(request('status') === 'sent')>Sent</option>
                            <option value="delivered" @selected(request('status') === 'delivered')>Delivered</option>
                            <option value="failed" @selected(request('status') === 'failed')>Failed</option>
                        </select>
                    </div>
                    <div class="flex items-end gap-2">
                        <button type="submit" class="btn-amber min-h-[44px] flex-1 px-5 py-2.5 text-sm font-bold rounded-xl transition shadow-xs cursor-pointer">
                            Filter
                        </button>
                        @if(request()->anyFilled(['search', 'channel', 'status', 'event']))
                            <a href="{{ route('alerts.index') }}" class="btn-secondary min-h-[44px] px-4 py-2.5 text-sm font-semibold rounded-xl flex items-center justify-center">
                                Reset
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            {{-- Table --}}
            <div class="bg-white dark:bg-[#0f172a] rounded-2xl shadow-xs border border-slate-200 dark:border-white/5 overflow-hidden">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-white/5 text-xs crm-responsive">
                    <thead class="bg-slate-100 dark:bg-[#0b1120]">
                        <tr>
                            <th class="px-4 py-3.5 text-left font-bold text-slate-700 dark:text-slate-400 uppercase tracking-wider text-[11px]">Channel & Event</th>
                            <th class="px-4 py-3.5 text-left font-bold text-slate-700 dark:text-slate-400 uppercase tracking-wider text-[11px]">Recipient</th>
                            <th class="px-4 py-3.5 text-left font-bold text-slate-700 dark:text-slate-400 uppercase tracking-wider text-[11px]">Message Content</th>
                            <th class="px-4 py-3.5 text-left font-bold text-slate-700 dark:text-slate-400 uppercase tracking-wider text-[11px]">Status</th>
                            <th class="px-4 py-3.5 text-left font-bold text-slate-700 dark:text-slate-400 uppercase tracking-wider text-[11px]">Sent At</th>
                            <th class="px-4 py-3.5 text-right font-bold text-slate-700 dark:text-slate-400 uppercase tracking-wider text-[11px]">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-white/5 bg-white dark:bg-[#0f172a]">
                        @forelse($logs as $log)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition">
                                <td class="px-4 py-3.5 whitespace-nowrap">
                                    <span class="inline-flex items-center gap-1.5 text-sm text-slate-900 dark:text-white">
                                        @if($log->channel === 'whatsapp')
                                            <x-icon.whatsapp class="w-3.5 h-3.5 text-black dark:text-white shrink-0" />
                                            <span>WhatsApp</span>
                                        @elseif($log->channel === 'sms')
                                            <span>SMS</span>
                                        @else
                                            <x-icon.email class="w-3.5 h-3.5 text-black dark:text-white shrink-0" />
                                            <span>Email</span>
                                        @endif
                                    </span>
                                    <div class="text-xs text-slate-500 dark:text-slate-400 first-letter:uppercase">
                                        {{ str_replace('_', ' ', $log->event_type) }}
                                    </div>
                                </td>
                                <td class="px-4 py-3.5">
                                    <div class="font-bold text-slate-900 dark:text-slate-100">{{ $log->recipient_name }}</div>
                                    <div class="text-[11px] text-slate-600 dark:text-slate-400 font-mono">
                                        {{ $log->recipient_phone ?: $log->recipient_email }}
                                    </div>
                                </td>
                                <td class="px-4 py-3.5 max-w-md">
                                    <div class="text-slate-800 dark:text-slate-300 line-clamp-2 leading-relaxed whitespace-pre-line text-[11px]">{{ $log->message_body }}</div>
                                    @if($log->error_message)
                                        <div class="text-[11px] text-rose-600 dark:text-rose-400 font-medium mt-1"><svg class="w-3.5 h-3.5 inline-block align-[-2px] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg> {{ $log->error_message }}</div>
                                    @endif
                                </td>
                                <td class="px-4 py-3.5 whitespace-nowrap">
                                    @php $st = strtolower($log->status ?? 'sent'); @endphp
                                    <span class="text-sm {{ $st === 'failed' ? 'text-rose-700 dark:text-rose-400 font-medium' : 'text-slate-600 dark:text-slate-300' }}">{{ ucfirst($st) }}</span>
                                </td>
                                <td class="px-4 py-3.5 whitespace-nowrap">
                                    <div class="font-semibold text-slate-900 dark:text-slate-100">{{ $log->sent_at?->format('d M Y') ?? $log->created_at->format('d M Y') }}</div>
                                    <div class="text-[11px] text-slate-500 dark:text-slate-400">{{ $log->sent_at?->format('h:i A') ?? $log->created_at->format('h:i A') }}</div>
                                </td>
                                <td class="px-4 py-3.5 whitespace-nowrap text-right font-medium">
                                    <div class="flex items-center justify-end gap-3">
                                        @if($log->action_url)
                                            <a href="{{ $log->action_url }}" target="_blank" class="text-sm" title="View Proposal PDF">
                                                PDF
                                            </a>
                                        @endif
                                        @if($log->channel === 'whatsapp' && $log->whats_app_web_url)
                                            <a href="{{ $log->whats_app_web_url }}" target="_blank" class="inline-flex items-center gap-1 text-sm" title="Open in WhatsApp Web / App">
                                                <x-icon.whatsapp class="w-3.5 h-3.5 text-black dark:text-white shrink-0" />
                                                <span>Chat</span>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-slate-500">
                                    No messages sent yet. Use Send reminders to send payment reminders for overdue invoices.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $logs->links() }}
            </div>

        </div>
    </div>
</x-app-layout>
