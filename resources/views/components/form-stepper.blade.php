{{--
    Step indicator for a long form shown one step at a time (resources/js/form-steps.js).
    Use inside x-data="formSteps", with one [data-step] block per label.
    Done steps can be clicked to go back; a later step opens once the steps before it are valid.
--}}
@props(['steps'])

@php($labels = array_values($steps))

<ol class="form-stepper" aria-label="Form steps">
    @foreach ($labels as $i => $label)
        <li :class="{ 'is-current': step === {{ $i + 1 }}, 'is-done': step > {{ $i + 1 }} }">
            <button type="button" @click="goTo({{ $i + 1 }})" :aria-current="step === {{ $i + 1 }} ? 'step' : null">
                <span class="form-stepper-dot">{{ $i + 1 }}</span>
                <span class="form-stepper-label">{{ $label }}</span>
            </button>
        </li>
    @endforeach
</ol>
<p class="sr-only" aria-live="polite" x-text="'Step ' + step + ' of {{ count($labels) }}: ' + {{ \Illuminate\Support\Js::from($labels) }}[step - 1]"></p>
