<button {{ $attributes->merge(['type' => 'submit', 'class' => 'crm-btn-primary inline-flex items-center justify-center px-5 py-2.5 text-white font-extrabold text-xs uppercase tracking-wider rounded-xl shadow-lg active:scale-[0.98] transition-all duration-150 focus:outline-none focus:ring-2 focus:ring-offset-2 cursor-pointer']) }}
        style="background: linear-gradient(135deg, var(--crm-accent, #be123c), var(--crm-accent-hover, #9f1239)); border: 1px solid rgba(255,255,255,0.15); box-shadow: 0 4px 14px -1px var(--crm-accent-shadow, rgba(190, 18, 60, 0.35));">
    {{ $slot }}
</button>

