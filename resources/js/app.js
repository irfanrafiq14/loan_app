import './bootstrap';
import Alpine from 'alpinejs';

Alpine.data('otpForm', () => ({
    digits: ['', '', '', ''],
    otp: '',
    timer: 59,
    fetching: false,
    start() {
        setInterval(() => {
            if (this.timer > 0) {
                this.timer--;
            }
        }, 1000);
        setTimeout(() => this.autofill(), 3500);
    },
    sync() {
        this.otp = this.digits.join('');
    },
    onInput(index, event) {
        const value = event.target.value.replace(/\D/g, '').slice(-1);
        this.digits[index] = value;
        this.sync();
        if (value && event.target.nextElementSibling) {
            event.target.nextElementSibling.focus();
        }
        if (this.otp.length === 4) {
            this.$refs.form.submit();
        }
    },
    onBackspace(index, event) {
        if (! this.digits[index] && event.target.previousElementSibling) {
            event.target.previousElementSibling.focus();
        }
    },
    async autofill() {
        this.fetching = true;
        try {
            const token = document.querySelector('meta[name="csrf-token"]').content;
            const response = await fetch('/verify-otp/autofill', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token,
                    Accept: 'application/json',
                },
            });
            const data = await response.json();
            if (data.otp) {
                this.digits = data.otp.split('');
                this.sync();
                this.$nextTick(() => this.$refs.form.submit());
            }
        } catch (e) {
            // Demo autofill is best-effort.
        }
        this.fetching = false;
    },
}));

Alpine.data('paymentForm', (selectedMethod) => ({
    method: selectedMethod,
    image: null,
    fileName: null,
    errors: {},
    validate(event) {
        this.errors = {};

        if (! this.method) {
            this.errors.method = 'Select a payment method.';
        }

        const transaction = document.querySelector('input[name="transaction_id"]')?.value?.trim();
        if (! transaction) {
            this.errors.transaction = 'Enter the transaction ID.';
        }

        if (! this.$refs.screenshot?.files?.length) {
            this.errors.screenshot = 'Upload a payment screenshot.';
        }

        if (Object.keys(this.errors).length) {
            event.preventDefault();
            this.$el.querySelector('[data-first-error]')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    },
    preview(event) {
        const file = event.target.files[0];
        if (! file) {
            return;
        }
        this.image = URL.createObjectURL(file);
        this.fileName = file.name;
    },
    onDrop(event) {
        const file = event.dataTransfer?.files?.[0];
        if (! file || ! this.$refs.screenshot) {
            return;
        }
        const transfer = new DataTransfer();
        transfer.items.add(file);
        this.$refs.screenshot.files = transfer.files;
        this.preview({ target: this.$refs.screenshot });
    },
    remove() {
        this.image = null;
        this.fileName = null;
        if (this.$refs.screenshot) {
            this.$refs.screenshot.value = '';
        }
    },
    async copy(value) {
        if (! value) {
            return;
        }
        await navigator.clipboard.writeText(value.trim());
    },
}));

window.Alpine = Alpine;
Alpine.start();
