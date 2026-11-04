// Long modal forms show one group of fields at a time, so the dialog fits on a laptop screen
// with its title and buttons in view. Put x-data="formSteps" on the form (or a wrapper inside
// it), then:
//
//   <x-form-stepper :steps="['Claim', 'Amount']" />
//   <div data-step x-show="step === 1"> ...fields... </div>
//   <div data-step x-show="step === 2" x-cloak> ...fields... </div>
//   <x-form-step-buttons />  then the form's own submit button in <template x-if="isLast()">
//
// Next (and the stepper) only move on when the fields of the steps passed are valid. The form
// starts again at step 1 each time its dialog is opened.
export default function formSteps() {
    return {
        step: 1,
        total: 1,

        init() {
            this.total = this.steps().length || 1;

            // Back to step 1 when the dialog closes (x-show or the "hidden" class), so it opens
            // on the first step next time
            const dialog = this.$root.closest('.fixed.inset-0') ?? this.$root;
            new MutationObserver(() => {
                if (!dialog.getClientRects().length) this.step = 1;
            }).observe(dialog, { attributes: true, attributeFilter: ['class', 'style'] });

            // Enter in a one-line field goes to the next step instead of sending a half-filled form
            this.$root.addEventListener('keydown', (event) => {
                if (event.key !== 'Enter' || this.isLast()) return;
                if (!event.target.matches('input:not([type=button], [type=submit], [type=checkbox], [type=radio], [type=file])')) return;
                event.preventDefault();
                this.next();
            });

            // The browser rejects a field on submit: if it sits in another step, show that step
            let seen = false;
            this.$root.addEventListener('invalid', (event) => {
                if (seen) return;
                seen = true;
                setTimeout(() => (seen = false));
                const owner = this.stepOf(event.target);
                if (owner && owner !== this.step) {
                    this.step = owner;
                    this.$nextTick(() => event.target.reportValidity());
                }
            }, true);
        },

        steps() {
            return [...this.$root.querySelectorAll('[data-step]')];
        },

        stepOf(element) {
            return this.steps().findIndex((step) => step.contains(element)) + 1;
        },

        isLast() {
            return this.step >= this.total;
        },

        // Shows the step and the browser's message for its first field that is not valid
        stepIsValid(number) {
            const fields = this.steps()[number - 1]?.querySelectorAll('input, select, textarea') ?? [];
            const invalid = [...fields].find((field) => !field.checkValidity());
            if (!invalid) return true;

            this.step = number;
            this.$nextTick(() => invalid.reportValidity());
            return false;
        },

        next() {
            this.goTo(this.step + 1);
        },

        back() {
            this.goTo(this.step - 1);
        },

        goTo(number) {
            number = Math.min(Math.max(number, 1), this.total);
            for (let passed = this.step; passed < number; passed++) {
                if (!this.stepIsValid(passed)) return;
            }
            this.step = number;
            this.$nextTick(() => this.$root.querySelector('.form-stepper')?.scrollIntoView({ block: 'nearest' }));
        },
    };
}
