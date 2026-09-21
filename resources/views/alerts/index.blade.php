<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold leading-tight text-white tracking-tight flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-amber-400 shadow-[0_0_10px_#f59e0b]"></span>
                    Automated Alerts & Notification Hub
                </h2>
                <p class="mt-1 text-sm text-slate-400 font-medium">Multi-channel WhatsApp, SMS & Email communication telemetry, dispatch logs & triggers</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('alerts.gateways') }}"
                   class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border border-white/10 bg-[#0f172a] hover:bg-slate-800 text-slate-300 text-xs font-bold shadow-sm transition">
                    🔌 SMS & Webhook Gateways
                </a>
                <a href="{{ route('alerts.templates') }}"
                   class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border border-white/10 bg-[#0f172a] hover:bg-slate-800 text-slate-300 text-xs font-bold shadow-sm transition">
                    ⚙️ Templates
                </a>
                <form method="POST" action="{{ route('alerts.run-sweep') }}">
                    @csrf
                    <button type="submit"
                            class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-400 hover:to-amber-500 text-slate-950 text-xs font-extrabold shadow-[0_4px_14px_rgba(245,158,11,0.25)] transition">
                        ⚡ Run Reminder Sweep
                    </button>
                </form>
            </div>
        </div>
    </x-slot>

    <div class="py-8 bg-[#060913] min-h-screen font-['Plus_Jakarta_Sans']">
        <div class="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8">

            @if (session('status'))
                <div class="mb-5 p-4 rounded-xl bg-emerald-950/40 border border-emerald-500/30 text-emerald-300 text-sm font-medium flex items-center gap-2">
                    <span>{{ session('status') }}</span>
                </div>
            @endif

            {{-- Summary Cards --}}
            <div class="grid grid-cols-1 sm:grid-cols-5 gap-4 mb-6">
                <div class="bg-[#0f172a] p-4 rounded-2xl border border-white/5 shadow-[0_10px_25px_-5px_rgba(0,0,0,0.5)]">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total Alerts Sent</span>
                    <div class="text-2xl font-extrabold text-white mt-1 font-['Outfit']">{{ $stats['total'] }}</div>
                    <span class="text-[11px] text-slate-500">All channels combined</span>
                </div>
                <div class="bg-[#0f172a] p-4 rounded-2xl border border-white/5 shadow-[0_10px_25px_-5px_rgba(0,0,0,0.5)]">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-400">WhatsApp Messages</span>
                    <div class="text-2xl font-extrabold text-emerald-400 mt-1 font-['Outfit']">{{ $stats['whatsapp'] }}</div>
                    <span class="text-[11px] text-slate-500">Chat dispatches</span>
                </div>
                <div class="bg-[#0f172a] p-4 rounded-2xl border border-white/5 shadow-[0_10px_25px_-5px_rgba(0,0,0,0.5)]">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-amber-400">SMS Alerts</span>
                    <div class="text-2xl font-extrabold text-amber-400 mt-1 font-['Outfit']">{{ $stats['sms'] }}</div>
                    <span class="text-[11px] text-slate-500">SMS gateway logs</span>
                </div>
                <div class="bg-[#0f172a] p-4 rounded-2xl border border-white/5 shadow-[0_10px_25px_-5px_rgba(0,0,0,0.5)]">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-sky-400">Email Notifications</span>
                    <div class="text-2xl font-extrabold text-sky-400 mt-1 font-['Outfit']">{{ $stats['email'] }}</div>
                    <span class="text-[11px] text-slate-500">HTML emails delivered</span>
                </div>
                <div class="bg-[#0f172a] p-4 rounded-2xl border border-white/5 shadow-[0_10px_25px_-5px_rgba(0,0,0,0.5)]">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-rose-400">Delivery Failures</span>
                    <div class="text-2xl font-extrabold {{ $stats['failed'] > 0 ? 'text-rose-400' : 'text-slate-500' }} mt-1 font-['Outfit']">{{ $stats['failed'] }}</div>
                    <span class="text-[11px] text-slate-500">Unreachable recipients</span>
                </div>
            </div>

            {{-- Quick Send Ad-hoc Alert Card --}}
            <div class="bg-[#0f172a] rounded-2xl p-5 mb-6 shadow-[0_10px_25px_-5px_rgba(0,0,0,0.5)] border border-white/5" x-data="{ open: false }">
                <div class="flex items-center justify-between cursor-pointer" @click="open = !open">
                    <div class="flex items-center gap-2">
                        <span class="w-7 h-7 rounded-lg bg-blue-500/10 text-blue-400 border border-blue-500/20 flex items-center justify-center text-sm font-bold">📢</span>
                        <div>
                            <h3 class="text-sm font-bold text-white">Send Instant Ad-hoc Notification / Broadcast</h3>
                            <p class="text-xs text-slate-400">Dispatch a custom WhatsApp message, SMS, or Email directly to a client or technician.</p>
                        </div>
                    </div>
                    <button type="button" class="text-xs font-bold text-amber-400 hover:underline">
                        <span x-show="!open">+ Open Composer</span>
                        <span x-show="open">&minus; Hide Composer</span>
                    </button>
                </div>

                <div x-show="open" x-cloak class="mt-4 pt-4 border-t border-white/5">
                    <form method="POST" action="{{ route('alerts.broadcast') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
                        @csrf
                        <div>
                            <label class="block font-bold text-slate-400 mb-1">Dispatch Channel <span class="text-rose-400">*</span></label>
                            <select name="channel" required class="w-full text-xs rounded-xl bg-[#060913] border-slate-700 text-slate-200 focus:border-amber-400 focus:ring-amber-400 font-semibold">
                                <option value="whatsapp">💬 WhatsApp Direct</option>
                                <option value="email">✉️ Email Notification</option>
                                <option value="sms">📱 SMS Text</option>
                            </select>
                        </div>

                        <div>
                            <label class="block font-bold text-slate-400 mb-1">Recipient Name <span class="text-rose-400">*</span></label>
                            <input type="text" name="recipient_name" required placeholder="e.g. John Doe" class="w-full text-xs rounded-xl bg-[#060913] border-slate-700 text-slate-200 focus:border-amber-400 focus:ring-amber-400">
                        </div>

                        <div>
                            <label class="block font-bold text-slate-400 mb-1">Recipient Phone / Mobile</label>
                            <input type="text" name="phone" placeholder="e.g. 9876543210" class="w-full text-xs rounded-xl bg-[#060913] border-slate-700 text-slate-200 focus:border-amber-400 focus:ring-amber-400">
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block font-bold text-slate-400 mb-1">Email Subject (If sending email)</label>
                            <input type="text" name="subject" placeholder="e.g. Important Service Notification regarding your CCTV setup" class="w-full text-xs rounded-xl bg-[#060913] border-slate-700 text-slate-200 focus:border-amber-400 focus:ring-amber-400">
                        </div>

                        <div>
                            <label class="block font-bold text-slate-400 mb-1">Recipient Email (If sending email)</label>
                            <input type="email" name="email" placeholder="e.g. customer@example.com" class="w-full text-xs rounded-xl bg-[#060913] border-slate-700 text-slate-200 focus:border-amber-400 focus:ring-amber-400">
                        </div>

                        <div class="sm:col-span-3">
                            <label class="block font-bold text-slate-400 mb-1">Message Body <span class="text-rose-400">*</span></label>
                            <textarea name="message" rows="3" required placeholder="Type your message content here..." class="w-full text-xs rounded-xl bg-[#060913] border-slate-700 text-slate-200 focus:border-amber-400 focus:ring-amber-400"></textarea>
                        </div>

                        <div class="sm:col-span-3 flex justify-end">
                            <button type="submit" class="px-5 py-2.5 bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-400 hover:to-amber-500 text-slate-950 rounded-xl text-xs font-extrabold shadow-sm transition">
                                Dispatch Message Now &rarr;
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Filter Bar --}}
            <div class="bg-[#0f172a] rounded-2xl p-4 mb-6 shadow-[0_10px_25px_-5px_rgba(0,0,0,0.5)] border border-white/5">
                <form method="GET" action="{{ route('alerts.index') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-400 mb-1">Search Logs</label>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Recipient, Phone, Message text..." class="w-full text-xs rounded-xl bg-[#060913] border-slate-700 text-slate-200 focus:border-amber-400 focus:ring-amber-400">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-400 mb-1">Channel</label>
                        <select name="channel" class="w-full text-xs rounded-xl bg-[#060913] border-slate-700 text-slate-200 focus:border-amber-400 focus:ring-amber-400">
                            <option value="">All Channels</option>
                            <option value="whatsapp" @selected(request('channel') === 'whatsapp')>💬 WhatsApp</option>
                            <option value="sms" @selected(request('channel') === 'sms')>📱 SMS</option>
                            <option value="email" @selected(request('channel') === 'email')>✉️ Email</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-400 mb-1">Status</label>
                        <select name="status" class="w-full text-xs rounded-xl bg-[#060913] border-slate-700 text-slate-200 focus:border-amber-400 focus:ring-amber-400">
                            <option value="">All Statuses</option>
                            <option value="sent" @selected(request('status') === 'sent')>Sent</option>
                            <option value="delivered" @selected(request('status') === 'delivered')>Delivered</option>
                            <option value="failed" @selected(request('status') === 'failed')>Failed</option>
                        </select>
                    </div>
                    <div class="flex items-end gap-2">
                        <button type="submit" class="flex-1 py-2 px-4 bg-blue-600 hover:bg-blue-500 text-white rounded-xl text-xs font-bold transition">
                            Filter
                        </button>
                        @if(request()->anyFilled(['search', 'channel', 'status', 'event']))
                            <a href="{{ route('alerts.index') }}" class="py-2 px-3 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-xl text-xs font-semibold">
                                Reset
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            {{-- Table --}}
            <div class="bg-[#0f172a] rounded-2xl shadow-[0_10px_25px_-5px_rgba(0,0,0,0.5)] border border-white/5 overflow-hidden">
                <table class="min-w-full divide-y divide-white/5 text-xs">
                    <thead class="bg-[#0b1120]">
                        <tr>
                            <th class="px-4 py-3.5 text-left font-bold text-slate-400 uppercase tracking-wider text-[11px]">Channel & Event</th>
                            <th class="px-4 py-3.5 text-left font-bold text-slate-400 uppercase tracking-wider text-[11px]">Recipient</th>
                            <th class="px-4 py-3.5 text-left font-bold text-slate-400 uppercase tracking-wider text-[11px]">Message Content</th>
                            <th class="px-4 py-3.5 text-left font-bold text-slate-400 uppercase tracking-wider text-[11px]">Status</th>
                            <th class="px-4 py-3.5 text-left font-bold text-slate-400 uppercase tracking-wider text-[11px]">Sent At</th>
                            <th class="px-4 py-3.5 text-right font-bold text-slate-400 uppercase tracking-wider text-[11px]">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5 bg-[#0f172a]">
                        @forelse($logs as $log)
                            <tr class="hover:bg-slate-800/50 transition">
                                <td class="px-4 py-3.5 whitespace-nowrap">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold border {{ $log->channel_badge_class }} mb-1">
                                        @if($log->channel === 'whatsapp') 💬 WhatsApp
                                        @elseif($log->channel === 'sms') 📱 SMS
                                        @else ✉️ Email
                                        @endif
                                    </span>
                                    <div class="text-[11px] font-semibold text-slate-300 capitalize">
                                        {{ str_replace('_', ' ', $log->event_type) }}
                                    </div>
                                </td>
                                <td class="px-4 py-3.5">
                                    <div class="font-bold text-slate-200">{{ $log->recipient_name }}</div>
                                    <div class="text-[11px] text-slate-400 font-mono">
                                        {{ $log->recipient_phone ?: $log->recipient_email }}
                                    </div>
                                </td>
                                <td class="px-4 py-3.5 max-w-md">
                                    <div class="text-slate-300 line-clamp-2 leading-relaxed whitespace-pre-line text-[11px]">{{ $log->message_body }}</div>
                                    @if($log->error_message)
                                        <div class="text-[10px] text-rose-400 font-medium mt-1">⚠️ {{ $log->error_message }}</div>
                                    @endif
                                </td>
                                <td class="px-4 py-3.5 whitespace-nowrap">
                                    <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold {{ $log->status_badge_class }}">
                                        {{ strtoupper($log->status) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3.5 whitespace-nowrap text-slate-400">
                                    <div class="font-semibold text-slate-200">{{ $log->sent_at?->format('d M Y') ?? $log->created_at->format('d M Y') }}</div>
                                    <div class="text-[10px] text-slate-500">{{ $log->sent_at?->format('h:i A') ?? $log->created_at->format('h:i A') }}</div>
                                </td>
                                <td class="px-4 py-3.5 whitespace-nowrap text-right font-medium">
                                    <div class="flex items-center justify-end gap-1.5">
                                        @if($log->action_url)
                                            <a href="{{ $log->action_url }}" target="_blank" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-sky-500/10 hover:bg-sky-500/20 text-sky-400 border border-sky-500/30 text-[11px] font-bold transition" title="View Proposal PDF">
                                                📄 PDF
                                            </a>
                                        @endif
                                        @if($log->channel === 'whatsapp' && $log->whats_app_web_url)
                                            <a href="{{ $log->whats_app_web_url }}" target="_blank" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 text-[11px] font-bold transition" title="Open in WhatsApp Web / App">
                                                💬 Chat
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-slate-500">
                                    No notification logs found. Click "Run Reminder Sweep" to execute automated checks.
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
