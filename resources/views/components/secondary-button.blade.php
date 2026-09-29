<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center justify-center px-4 py-2.5 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-200 hover:text-slate-900 dark:hover:text-white font-bold text-xs uppercase tracking-wider rounded-xl shadow-xs transition-all focus:outline-none focus:ring-2 focus:ring-indigo-500 disabled:opacity-40']) }}>
    {{ $slot }}
</button>

