@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border border-slate-700/80 bg-[#060913] text-slate-100 placeholder-slate-500 focus:border-amber-500 focus:ring-1 focus:ring-amber-500 rounded-xl shadow-sm text-sm py-2 px-3 transition-colors']) }}>

