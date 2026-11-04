{{--
    Back / Next for a form shown one step at a time (resources/js/form-steps.js).
    Follow it with the form's own submit button inside <template x-if="isLast()">.
    x-if, not x-show: the theme forces .btn-primary / .btn-secondary to display.
--}}
<template x-if="step > 1">
    <button type="button" @click="back()" class="btn-secondary text-xs">Back</button>
</template>
<template x-if="!isLast()">
    <button type="button" @click="next()" class="btn-primary text-xs">Next</button>
</template>
