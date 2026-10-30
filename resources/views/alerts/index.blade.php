<x-app-layout>
    @php
        $currChannel = request('channel');
        $currStatus = request('status');
        $currSearch = request('search');
        $filterCount = ($currChannel ? 1 : 0) + ($currStatus ? 1 : 0) + ($currSearch ? 1 : 0) + (request()->filled('event') ? 1 : 0);
        $here = fn (array $change) => request()->fullUrlWithQuery(array_merge(['page' => null], $change));

        $viewTitle = 'All Messages';
        if ($currChannel === 'whatsapp') { $viewTitle = 'WhatsApp Logs'; }
        elseif ($currChannel === 'sms') { $viewTitle = 'SMS Logs'; }
        elseif ($currChannel === 'email') { $viewTitle = 'Email Logs'; }
        elseif ($currStatus === 'failed') { $viewTitle = 'Failed Alerts'; }
    @endphp

    <x-list-page-assets />

    <div class="ld-root" x-data="Object.assign(listPage(@js($logs->pluck('id')), 'alerts', { channel: true, recipient: true, message: true, status: true, sent_at: true }, @js($filterCount > 0)), { composeOpen: false })" @keydown.escape.window="menu = null">

        @if (session('status'))
            <div class="px-4 py-2.5 text-sm bg-emerald-50 text-emerald-800 border-b border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-900 flex items-center justify-between">
                <span>{{ session('status') }}</span>
                <button type="button" @click="$el.parentElement.remove()" class="text-emerald-700 dark:text-emerald-400 font-bold">&times;</button>
            </div>
        @endif

        {{-- Top Band: Saved Views + Zoho-Style Toolbar --}}
        <div class="ld-band">
            <div class="ld-views">
                <span class="ld-chip">{{ $viewTitle }}</span>
                <div class="relative" @click.outside="menu === 'views' && (menu = null)">
                    <button type="button" class="ld-icon-btn" title="Filter views" aria-label="Other views" :aria-expanded="menu === 'views'" @click="open('views')">
                        <svg class="icon-solid" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><circle cx="4" cy="10" r="1.6"/><circle cx="10" cy="10" r="1.6"/><circle cx="16" cy="10" r="1.6"/></svg>
                    </button>
                    <div class="ld-menu left-0" x-show="menu === 'views'" x-cloak>
                        <a href="{{ route('alerts.index') }}" class="plain-link {{ !$currChannel && !$currStatus ? 'is-current' : '' }}">
                            <span>All Messages</span>
                            @if(!$currChannel && !$currStatus)<svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>@endif
                        </a>
                        <a href="{{ route('alerts.index', ['channel' => 'whatsapp']) }}" class="plain-link {{ $currChannel === 'whatsapp' ? 'is-current' : '' }}">
                            <span>WhatsApp Logs ({{ $stats['whatsapp'] }})</span>
                            @if($currChannel === 'whatsapp')<svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>@endif
                        </a>
                        <a href="{{ route('alerts.index', ['channel' => 'email']) }}" class="plain-link {{ $currChannel === 'email' ? 'is-current' : '' }}">
                            <span>Email Logs ({{ $stats['email'] }})</span>
                            @if($currChannel === 'email')<svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>@endif
                        </a>
                        <a href="{{ route('alerts.index', ['channel' => 'sms']) }}" class="plain-link {{ $currChannel === 'sms' ? 'is-current' : '' }}">
                            <span>SMS Logs ({{ $stats['sms'] }})</span>
                            @if($currChannel === 'sms')<svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>@endif
                        </a>
                        <a href="{{ route('alerts.index', ['status' => 'failed']) }}" class="plain-link {{ $currStatus === 'failed' ? 'is-current' : '' }}">
                            <span class="text-rose-600 dark:text-rose-400 font-semibold">Failed Alerts ({{ $stats['failed'] }})</span>
                            @if($currStatus === 'failed')<svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>@endif
                        </a>
                    </div>
                </div>
            </div>

            <div class="ld-toolbar">
                <div class="flex items-center gap-1.5 flex-wrap">
                    {{-- Slide-out Filter toggle button --}}
                    <button type="button" class="ld-tool" :class="showFilter && 'is-on'" @click="showFilter = !showFilter; $nextTick(() => fit())" :aria-pressed="showFilter">
                        <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5h18l-7 8.5V19l-4 2v-7.5L3 5z"/></svg>
                        <span>Filter</span>
                        @if($filterCount > 0)<span class="text-xs font-semibold" style="color: var(--ld-accent)">{{ $filterCount }}</span>@endif
                    </button>

                    <form method="POST" action="{{ route('alerts.run-sweep') }}" data-confirm="Send payment reminders for all overdue invoices now?">
                        @csrf
                        <button type="submit" class="ld-tool text-slate-700 dark:text-slate-300 hover:text-slate-900" title="Auto-send payment reminders for overdue invoices">
                            <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            <span>Send Reminders</span>
                        </button>
                    </form>

                    <span class="ld-sep" aria-hidden="true"></span>

                    <a href="{{ route('alerts.gateways') }}" class="ld-tool plain-link" title="Gateway Settings">
                        <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <span>Gateways</span>
                    </a>

                    <a href="{{ route('alerts.templates') }}" class="ld-tool plain-link" title="Notification Templates">
                        <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <span>Templates</span>
                    </a>
                </div>

                <div class="flex items-center gap-2 ml-auto">
                    {{-- Primary Action: Write Message --}}
                    <button type="button" @click="composeOpen = !composeOpen" class="ld-accent" style="border-radius: 4px; height: 32px; padding: 0 14px">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                        <span x-text="composeOpen ? 'Close Composer' : 'Write Message'">Write Message</span>
                    </button>

                    {{-- More Actions menu --}}
                    <div class="relative" @click.outside="menu === 'more' && (menu = null)">
                        <button type="button" class="ld-outline" title="More options" aria-label="More actions" :aria-expanded="menu === 'more'" @click="open('more')">
                            <svg class="w-4 h-4 icon-solid" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><circle cx="4" cy="10" r="1.6"/><circle cx="10" cy="10" r="1.6"/><circle cx="16" cy="10" r="1.6"/></svg>
                        </button>
                        <div class="ld-menu right-0" x-show="menu === 'more'" x-cloak>
                            <a href="{{ route('alerts.gateways') }}" class="plain-link">Manage SMS / WhatsApp Gateways</a>
                            <a href="{{ route('alerts.templates') }}" class="plain-link">Manage Notification Templates</a>
                            <button type="button" @click="menu = null; window.print()">Print this log sheet</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Body: Filter Panel + Main Table --}}
        <div class="ld-body" x-ref="body">

            {{-- Slide-out Left Filter Drawer --}}
            <div class="ld-card ld-filter" role="search" x-show="showFilter" x-cloak aria-label="Filter alerts">
                <form method="GET" action="{{ route('alerts.index') }}" class="flex flex-col h-full min-h-0">
                    <div class="ld-filter-scroll">
                        <h2>Filter Logs by</h2>
                        <div class="relative">
                            <svg class="w-4 h-4 absolute left-2.5 top-2" style="color: var(--ld-muted)" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 10.5a6.5 6.5 0 11-13 0 6.5 6.5 0 0113 0z"/></svg>
                            <input type="search" name="search" value="{{ request('search') }}" placeholder="Search recipient, text, phone…" aria-label="Search the filters" class="ld-input" style="padding-left: 32px !important">
                        </div>

                        <details open>
                            <summary><span class="ld-caret ld-caret-sm" aria-hidden="true"></span><span>Dispatch Channel</span></summary>
                            <div class="space-y-1">
                                <label class="ld-check">
                                    <input type="radio" name="channel" value="" @checked(!request('channel')) style="accent-color: var(--ld-accent)">
                                    <span>All Channels</span>
                                    <span class="ld-count">{{ $stats['total'] }}</span>
                                </label>
                                <label class="ld-check">
                                    <input type="radio" name="channel" value="whatsapp" @checked(request('channel') === 'whatsapp') style="accent-color: var(--ld-accent)">
                                    <span>WhatsApp</span>
                                    <span class="ld-count">{{ $stats['whatsapp'] }}</span>
                                </label>
                                <label class="ld-check">
                                    <input type="radio" name="channel" value="email" @checked(request('channel') === 'email') style="accent-color: var(--ld-accent)">
                                    <span>Email</span>
                                    <span class="ld-count">{{ $stats['email'] }}</span>
                                </label>
                                <label class="ld-check">
                                    <input type="radio" name="channel" value="sms" @checked(request('channel') === 'sms') style="accent-color: var(--ld-accent)">
                                    <span>SMS</span>
                                    <span class="ld-count">{{ $stats['sms'] }}</span>
                                </label>
                            </div>
                        </details>

                        <details open>
                            <summary><span class="ld-caret ld-caret-sm" aria-hidden="true"></span><span>Delivery Status</span></summary>
                            <div class="space-y-1">
                                <label class="ld-check">
                                    <input type="radio" name="status" value="" @checked(!request('status')) style="accent-color: var(--ld-accent)">
                                    <span>All Statuses</span>
                                </label>
                                <label class="ld-check">
                                    <input type="radio" name="status" value="sent" @checked(request('status') === 'sent') style="accent-color: var(--ld-accent)">
                                    <span>Sent</span>
                                </label>
                                <label class="ld-check">
                                    <input type="radio" name="status" value="delivered" @checked(request('status') === 'delivered') style="accent-color: var(--ld-accent)">
                                    <span>Delivered</span>
                                </label>
                                <label class="ld-check">
                                    <input type="radio" name="status" value="failed" @checked(request('status') === 'failed') style="accent-color: var(--ld-accent)">
                                    <span class="text-rose-600 dark:text-rose-400 font-semibold">Failed</span>
                                    <span class="ld-count">{{ $stats['failed'] }}</span>
                                </label>
                            </div>
                        </details>
                    </div>

                    <div class="ld-filter-actions">
                        <button type="submit" id="ld-apply" class="ld-accent" style="border-radius: 4px; height: 30px; padding: 0 12px">Apply Filter</button>
                        @if($filterCount > 0)
                            <a href="{{ route('alerts.index') }}" class="ld-outline plain-link" style="height: 30px">Clear</a>
                        @endif
                    </div>
                </form>
            </div>

            {{-- Main Content Area --}}
            <section class="ld-main" aria-label="Messages">

                {{-- Metric KPI Tiles --}}
                <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 mb-4">
                    <div class="ld-card p-3">
                        <div class="text-xs text-slate-500 dark:text-slate-400 font-medium">Total Messages</div>
                        <div class="text-xl font-bold text-slate-900 dark:text-white mt-0.5">{{ number_format($stats['total']) }}</div>
                    </div>
                    <div class="ld-card p-3">
                        <div class="text-xs text-slate-500 dark:text-slate-400 font-medium flex items-center gap-1.5">
                            <x-icon.whatsapp class="w-3 h-3 text-emerald-600" />
                            <span>WhatsApp</span>
                        </div>
                        <div class="text-xl font-bold text-slate-900 dark:text-white mt-0.5">{{ number_format($stats['whatsapp']) }}</div>
                    </div>
                    <div class="ld-card p-3">
                        <div class="text-xs text-slate-500 dark:text-slate-400 font-medium flex items-center gap-1.5">
                            <x-icon.email class="w-3 h-3 text-sky-600" />
                            <span>Email</span>
                        </div>
                        <div class="text-xl font-bold text-slate-900 dark:text-white mt-0.5">{{ number_format($stats['email']) }}</div>
                    </div>
                    <div class="ld-card p-3">
                        <div class="text-xs text-slate-500 dark:text-slate-400 font-medium">SMS Text</div>
                        <div class="text-xl font-bold text-slate-900 dark:text-white mt-0.5">{{ number_format($stats['sms']) }}</div>
                    </div>
                    <div class="ld-card p-3">
                        <div class="text-xs text-slate-500 dark:text-slate-400 font-medium text-rose-600 dark:text-rose-400">Failed Delivery</div>
                        <div class="text-xl font-bold {{ $stats['failed'] > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-slate-900 dark:text-white' }} mt-0.5">{{ number_format($stats['failed']) }}</div>
                    </div>
                </div>

                {{-- Compose Message Form Drawer (expandable) --}}
                <div x-show="composeOpen" x-cloak class="ld-card p-5 mb-4 border border-slate-200 dark:border-slate-800">
                    <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100 dark:border-slate-800">
                        <div>
                            <h3 class="font-bold text-sm text-slate-900 dark:text-white">Compose Direct Dispatch</h3>
                            <p class="text-xs text-slate-500">Send an instant WhatsApp message, SMS text or email update to any client or field technician.</p>
                        </div>
                        <button type="button" @click="composeOpen = false" class="text-slate-400 hover:text-slate-700 text-lg font-bold">&times;</button>
                    </div>

                    <form method="POST" action="{{ route('alerts.broadcast') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-3.5 text-xs">
                        @csrf
                        <div>
                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Dispatch Channel <span class="text-rose-500">*</span></label>
                            <select name="channel" required class="ld-input">
                                <option value="whatsapp">WhatsApp Direct</option>
                                <option value="email">Email Notification</option>
                                <option value="sms">SMS Text</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Recipient Name <span class="text-rose-500">*</span></label>
                            <input type="text" name="recipient_name" required placeholder="e.g. John Doe" class="ld-input">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Recipient Phone / Mobile</label>
                            <input type="text" name="phone" placeholder="e.g. 9876543210" class="ld-input">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Email Subject (Optional)</label>
                            <input type="text" name="subject" placeholder="e.g. Service visit schedule confirmation" class="ld-input">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Recipient Email (Optional)</label>
                            <input type="email" name="email" placeholder="e.g. customer@example.com" class="ld-input">
                        </div>
                        <div class="sm:col-span-3">
                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Message Content <span class="text-rose-500">*</span></label>
                            <textarea name="message" rows="3" required placeholder="Type the message body here…" class="ld-input" style="height: auto !important"></textarea>
                        </div>
                        <div class="sm:col-span-3 flex justify-end gap-2">
                            <button type="button" @click="composeOpen = false" class="ld-outline" style="height: 32px">Cancel</button>
                            <button type="submit" class="ld-accent" style="border-radius: 4px; height: 32px; padding: 0 16px">Send Message</button>
                        </div>
                    </form>
                </div>

                {{-- Table Card --}}
                <div class="ld-card flex flex-col flex-1 min-h-0">
                    <div class="ld-scroll">
                        <table class="ld-table">
                            <thead>
                                <tr>
                                    <th style="min-width: 170px">Channel &amp; Event</th>
                                    <th style="min-width: 180px">Recipient</th>
                                    <th style="min-width: 280px">Message Content</th>
                                    <th style="min-width: 110px">Status</th>
                                    <th style="min-width: 150px">Sent At</th>
                                    <th style="width: 100px; text-align: right">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($logs as $log)
                                    <tr>
                                        <td>
                                            <div class="flex items-center gap-1.5 font-semibold text-slate-900 dark:text-white">
                                                @if($log->channel === 'whatsapp')
                                                    <x-icon.whatsapp class="w-3.5 h-3.5 text-emerald-600 shrink-0" />
                                                    <span>WhatsApp</span>
                                                @elseif($log->channel === 'sms')
                                                    <span class="text-xs px-1 py-0.5 rounded bg-slate-100 dark:bg-slate-800 font-mono">SMS</span>
                                                @else
                                                    <x-icon.email class="w-3.5 h-3.5 text-sky-600 shrink-0" />
                                                    <span>Email</span>
                                                @endif
                                            </div>
                                            <div class="text-xs text-slate-500 dark:text-slate-400 capitalize mt-0.5">
                                                {{ str_replace('_', ' ', $log->event_type) }}
                                            </div>
                                        </td>
                                        <td>
                                            <div class="ld-name font-bold">{{ $log->recipient_name }}</div>
                                            <div class="text-xs text-slate-500 dark:text-slate-400 font-mono mt-0.5">
                                                {{ $log->recipient_phone ?: $log->recipient_email }}
                                            </div>
                                        </td>
                                        <td class="max-w-md">
                                            <div class="text-slate-800 dark:text-slate-300 line-clamp-2 leading-relaxed text-xs">
                                                {{ $log->message_body }}
                                            </div>
                                            @if($log->error_message)
                                                <div class="text-xs text-rose-600 dark:text-rose-400 font-medium mt-1 flex items-center gap-1">
                                                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                                    <span>{{ $log->error_message }}</span>
                                                </div>
                                            @endif
                                        </td>
                                        <td class="whitespace-nowrap">
                                            @php $st = strtolower($log->status ?? 'sent'); @endphp
                                            @if($st === 'failed')
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-400 border border-rose-200 dark:border-rose-900/40">Failed</span>
                                            @elseif($st === 'delivered')
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-900/40">Delivered</span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-blue-50 text-blue-700 dark:bg-blue-950/40 dark:text-blue-400 border border-blue-200 dark:border-blue-900/40">Sent</span>
                                            @endif
                                        </td>
                                        <td class="whitespace-nowrap text-xs">
                                            <div class="font-medium text-slate-800 dark:text-slate-200">{{ $log->sent_at?->format('d M Y') ?? $log->created_at->format('d M Y') }}</div>
                                            <div class="text-slate-500 dark:text-slate-400">{{ $log->sent_at?->format('h:i A') ?? $log->created_at->format('h:i A') }}</div>
                                        </td>
                                        <td class="whitespace-nowrap text-right">
                                            <div class="flex items-center justify-end gap-2 text-xs">
                                                @if($log->action_url)
                                                    <a href="{{ $log->action_url }}" target="_blank" class="ld-outline plain-link" style="height: 26px; padding: 0 8px;" title="View Proposal PDF">
                                                        PDF
                                                    </a>
                                                @endif
                                                @if($log->channel === 'whatsapp' && $log->whats_app_web_url)
                                                    <a href="{{ $log->whats_app_web_url }}" target="_blank" class="ld-outline plain-link inline-flex items-center gap-1 text-emerald-600 dark:text-emerald-400" style="height: 26px; padding: 0 8px;" title="Open in WhatsApp Web / App">
                                                        <x-icon.whatsapp class="w-3 h-3 text-emerald-600 shrink-0" />
                                                        <span>Chat</span>
                                                    </a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" style="padding: 48px 16px; text-align: center; color: var(--ld-muted)">
                                            @if($filterCount > 0)
                                                No notification logs match these filters.
                                                <a href="{{ route('alerts.index') }}" class="ml-1 underline plain-link" style="color: var(--ld-accent, var(--crm-accent, #2563eb)); font-weight: 600;">Show all messages</a>
                                            @else
                                                No messages logged yet. Use "Write Message" or "Send Reminders" to dispatch alerts.
                                            @endif
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Table Footer (Leads Standard) --}}
                    <div class="ld-foot">
                        <div>Total Records <strong>{{ $logs->total() }}</strong></div>
                        <div class="flex items-center gap-2">
                            <span class="px-1 text-xs">
                                @if($logs->total() > 0)
                                    Showing <strong>{{ $logs->firstItem() }}</strong> to <strong>{{ $logs->lastItem() }}</strong>
                                @else
                                    0
                                @endif
                            </span>
                            @if($logs->hasPages())
                                <div class="flex items-center gap-1">
                                    @if($logs->onFirstPage())
                                        <span class="ld-page-arrow" aria-hidden="true"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg></span>
                                    @else
                                        <a href="{{ $logs->previousPageUrl() }}" class="ld-page-arrow plain-link" aria-label="Previous page"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg></a>
                                    @endif
                                    @if($logs->hasMorePages())
                                        <a href="{{ $logs->nextPageUrl() }}" class="ld-page-arrow plain-link" aria-label="Next page"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg></a>
                                    @else
                                        <span class="ld-page-arrow" aria-hidden="true"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg></span>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

            </section>
        </div>
    </div>
</x-app-layout>
