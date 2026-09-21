<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-black tracking-wider text-white uppercase font-['Outfit'] flex items-center gap-2">
                    <span class="text-amber-400">⚙️</span> Message Templates & Automation Tags
                </h2>
                <p class="mt-1 text-xs text-slate-400 font-mono">Customize automated WhatsApp, SMS, and Email text with dynamic variables</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('alerts.gateways') }}"
                   class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border border-slate-700 bg-slate-800 text-slate-200 hover:bg-slate-700 text-xs font-bold transition">
                    🔌 Gateways
                </a>
                <a href="{{ route('alerts.index') }}"
                   class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border border-slate-700/60 bg-slate-800/40 text-slate-400 hover:text-white text-xs font-semibold transition">
                    &larr; Notification Logs
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8 bg-[#060913] min-h-screen text-slate-200">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

            @if (session('status'))
                <div class="mb-6 p-4 rounded-xl border border-emerald-500/30 bg-emerald-500/10 text-emerald-400 text-sm font-semibold flex items-center gap-3 shadow-xl">
                    <span>✓ {{ session('status') }}</span>
                </div>
            @endif

            <div class="space-y-6">
                @foreach($templates as $template)
                    <div class="bg-[#0F172A] rounded-2xl p-6 shadow-xl border border-white/10" x-data="{ expanded: false }">
                        
                        {{-- Header Row --}}
                        <div class="flex flex-wrap items-center justify-between gap-3 pb-4 border-b border-white/5 cursor-pointer" @click="expanded = !expanded">
                            <div class="flex items-center gap-3">
                                <span class="w-9 h-9 rounded-xl bg-amber-500/10 text-amber-400 flex items-center justify-center font-bold text-xs border border-amber-500/20 font-mono">
                                    {{ strtoupper(substr($template->category, 0, 3)) }}
                                </span>
                                <div>
                                    <h3 class="text-base font-bold text-white font-['Outfit']">{{ $template->title }}</h3>
                                    <span class="text-[11px] font-mono text-slate-400">Event key: <code class="text-amber-400">{{ $template->event_key }}</code></span>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="flex items-center gap-1.5 text-[10px] font-mono font-bold uppercase">
                                    <span class="px-2 py-0.5 rounded {{ $template->is_whatsapp_enabled ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/30' : 'bg-slate-800 text-slate-500 border border-slate-700' }}">WA</span>
                                    <span class="px-2 py-0.5 rounded {{ $template->is_sms_enabled ? 'bg-amber-500/10 text-amber-400 border border-amber-500/30' : 'bg-slate-800 text-slate-500 border border-slate-700' }}">SMS</span>
                                    <span class="px-2 py-0.5 rounded {{ $template->is_email_enabled ? 'bg-sky-500/10 text-sky-400 border border-sky-500/30' : 'bg-slate-800 text-slate-500 border border-slate-700' }}">EMAIL</span>
                                </div>
                                <button type="button" class="text-xs font-bold text-amber-400 hover:text-amber-300 font-mono">
                                    <span x-show="!expanded">✏️ Edit Template</span>
                                    <span x-show="expanded">&times; Close</span>
                                </button>
                            </div>
                        </div>

                        {{-- Available Tags Pills --}}
                        <div class="mt-3 flex flex-wrap items-center gap-1.5 text-xs">
                            <span class="text-slate-400 font-mono text-[10px] uppercase tracking-widest mr-1">Available Tags:</span>
                            @foreach($template->available_variables ?? [] as $var)
                                <span class="px-2 py-0.5 rounded-md bg-[#060913] text-sky-400 font-mono text-[10px] border border-slate-700">
                                    {<!-- -->{<!-- -->{{ $var }}<!-- -->}<!-- -->}
                                </span>
                            @endforeach
                        </div>

                        {{-- Collapsible Form --}}
                        <div x-show="expanded" x-cloak class="mt-5 pt-5 border-t border-white/5">
                            <form method="POST" action="{{ route('alerts.templates.update', $template) }}" class="space-y-4 text-xs">
                                @csrf
                                @method('PUT')

                                {{-- Channel Toggles --}}
                                <div class="flex flex-wrap items-center gap-6 p-3.5 rounded-xl bg-[#060913] border border-white/10 font-mono">
                                    <span class="font-bold text-slate-300 uppercase tracking-wider text-[10px]">Active Channels:</span>
                                    <label class="inline-flex items-center gap-2 cursor-pointer">
                                        <input type="checkbox" name="is_whatsapp_enabled" value="1" @checked($template->is_whatsapp_enabled) class="rounded border-slate-700 text-amber-500 focus:ring-0">
                                        <span class="font-bold text-white">💬 WhatsApp</span>
                                    </label>
                                    <label class="inline-flex items-center gap-2 cursor-pointer">
                                        <input type="checkbox" name="is_sms_enabled" value="1" @checked($template->is_sms_enabled) class="rounded border-slate-700 text-amber-500 focus:ring-0">
                                        <span class="font-bold text-white">📱 SMS</span>
                                    </label>
                                    <label class="inline-flex items-center gap-2 cursor-pointer">
                                        <input type="checkbox" name="is_email_enabled" value="1" @checked($template->is_email_enabled) class="rounded border-slate-700 text-amber-500 focus:ring-0">
                                        <span class="font-bold text-white">✉️ Email</span>
                                    </label>
                                </div>

                                {{-- WhatsApp Template --}}
                                <div>
                                    <label class="block text-[10px] font-mono uppercase tracking-widest text-slate-400 mb-1.5">
                                        💬 WhatsApp Message Text
                                    </label>
                                    <textarea name="whatsapp_template" rows="4" required class="w-full text-xs font-mono rounded-xl border border-slate-700 bg-[#060913] text-white focus:border-amber-400 focus:outline-none p-3 leading-relaxed">{{ $template->whatsapp_template }}</textarea>
                                </div>

                                {{-- SMS Template --}}
                                <div>
                                    <label class="block text-[10px] font-mono uppercase tracking-widest text-slate-400 mb-1.5">
                                        📱 SMS Text (Single / 160-char format)
                                    </label>
                                    <textarea name="sms_template" rows="2" class="w-full text-xs font-mono rounded-xl border border-slate-700 bg-[#060913] text-white focus:border-amber-400 focus:outline-none p-3 leading-relaxed">{{ $template->sms_template }}</textarea>
                                </div>

                                {{-- Email Subject & Body --}}
                                <div class="grid grid-cols-1 gap-3.5">
                                    <div>
                                        <label class="block text-[10px] font-mono uppercase tracking-widest text-slate-400 mb-1.5">
                                            ✉️ Email Subject Line
                                        </label>
                                        <input type="text" name="email_subject" value="{{ $template->email_subject }}" required class="w-full text-xs font-semibold rounded-xl border border-slate-700 bg-[#060913] text-white focus:border-amber-400 focus:outline-none p-2.5">
                                    </div>

                                    <div>
                                        <label class="block text-[10px] font-mono uppercase tracking-widest text-slate-400 mb-1.5">Email HTML Body</label>
                                        <textarea name="email_body" rows="4" required class="w-full text-xs font-mono rounded-xl border border-slate-700 bg-[#060913] text-white focus:border-amber-400 focus:outline-none p-3 leading-relaxed">{{ $template->email_body }}</textarea>
                                    </div>
                                </div>

                                <div class="flex justify-end pt-2">
                                    <button type="submit" class="px-5 py-2.5 bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-400 hover:to-amber-500 text-slate-950 rounded-xl text-xs font-black uppercase tracking-wider shadow-lg shadow-amber-500/20 transition">
                                        Save Template Changes &rarr;
                                    </button>
                                </div>
                            </form>
                        </div>

                    </div>
                @endforeach
            </div>

        </div>
    </div>
</x-app-layout>
