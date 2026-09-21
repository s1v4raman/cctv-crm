<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center px-4 py-2.5 bg-rose-600 hover:bg-rose-500 active:bg-rose-700 border border-rose-500/40 rounded-xl font-bold text-xs text-white uppercase tracking-wider shadow-lg shadow-rose-600/20 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2 focus:ring-offset-slate-900 transition-all duration-150']) }}>
    {{ $slot }}
</button>

