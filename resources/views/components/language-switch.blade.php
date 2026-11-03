{{-- English / Tamil switch for the worker sign-in, worker portal and technician pages.
     Saved in a cookie on this phone and on the signed-in account (LocaleController).
     portal="worker" / "technician" keeps the sign-in page on that tab after switching. --}}
@props(['portal' => null])
<form method="POST" action="{{ route('locale.update') }}"
      {{ $attributes->merge(['class' => 'inline-flex items-center shrink-0 rounded-[6px] border border-slate-300 dark:border-slate-700 overflow-hidden']) }}
      role="group" aria-label="{{ __('Language') }}">
    @csrf
    @if ($portal)
        <input type="hidden" name="portal" value="{{ $portal }}">
    @endif
    @foreach (config('app.supported_locales') as $code => $label)
        <button type="submit" name="locale" value="{{ $code }}" lang="{{ $code }}"
                aria-pressed="{{ app()->getLocale() === $code ? 'true' : 'false' }}"
                @class([
                    'px-2.5 py-1.5 text-xs font-semibold transition-colors cursor-pointer',
                    'bg-blue-600 text-white' => app()->getLocale() === $code,
                    'bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700' => app()->getLocale() !== $code,
                ])>
            {{ $label }}
        </button>
    @endforeach
</form>
