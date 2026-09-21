<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center justify-center px-4 py-2.5 bg-slate-800/90 hover:bg-slate-700/90 border border-slate-700 hover:border-slate-600 text-slate-200 hover:text-white font-bold text-xs uppercase tracking-wider rounded-xl shadow-sm transition-all focus:outline-none focus:ring-2 focus:ring-amber-400 focus:ring-offset-2 focus:ring-offset-slate-900 disabled:opacity-40']) }}>
    {{ $slot }}
</button>

