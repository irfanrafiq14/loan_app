import './bootstrap';
import Alpine from 'alpinejs';

const APP_NAME_KEY = 'customer_app_name';

function sanitizeAppName(name) {
    const value = String(name || '').replace(/\s+/g, ' ').trim();

    if (value === '' || value.length > 80) {
        return '';
    }

    if (['maxwallet', 'max wallet'].includes(value.toLowerCase())) {
        return '';
    }

    return value;
}

function readStoredAppName() {
    try {
        return sanitizeAppName(window.localStorage.getItem(APP_NAME_KEY));
    } catch (e) {
        return '';
    }
}

window.persistCustomerAppName = function persistCustomerAppName(name) {
    const value = sanitizeAppName(name);

    if (! value) {
        return;
    }

    try {
        window.localStorage.setItem(APP_NAME_KEY, value);
    } catch (e) {
        // Ignore private-mode storage failures.
    }
};

Alpine.data('storedAppBrand', (serverName = '') => ({
    name: '',
    init() {
        const fromServer = sanitizeAppName(serverName);

        if (fromServer) {
            window.persistCustomerAppName(fromServer);
            this.name = fromServer;
        } else {
            this.name = readStoredAppName();
        }

        this.applyChrome();
    },
    applyChrome() {
        if (! this.name) {
            return;
        }

        document.title = this.name;

        const letter = this.name.slice(0, 1).toUpperCase();
        const svg = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64"><rect width="64" height="64" rx="16" fill="#0EA5E9"/><text x="32" y="43" text-anchor="middle" font-family="Arial, Helvetica, sans-serif" font-size="34" font-weight="800" fill="#ffffff">${letter}</text></svg>`;
        const href = 'data:image/svg+xml,' + encodeURIComponent(svg);

        document.querySelectorAll('link[rel="icon"], link[rel="apple-touch-icon"]').forEach((link) => {
            link.setAttribute('href', href);
        });
    },
}));


Alpine.data('otpForm', () => ({
    digits: ['', '', '', ''],
    otp: '',
    timer: 59,
    fetching: false,
    clock() {
        const minutes = String(Math.floor(this.timer / 60)).padStart(2, '0');
        const seconds = String(this.timer % 60).padStart(2, '0');

        return `${minutes}:${seconds}`;
    },
    start() {
        if (!this._tick) {
            this._tick = setInterval(() => {
                if (this.timer > 0) {
                    this.timer--;
                }
            }, 1000);
        }
        this.scheduleAutofill();
    },
    scheduleAutofill() {
        clearTimeout(this._auto);
        this._auto = setTimeout(() => this.autofill(), 3500);
    },
    async resend() {
        try {
            const token = document.querySelector('meta[name="csrf-token"]').content;
            await fetch('/verify-otp/resend', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': token,
                    Accept: 'application/json',
                },
            });
        } catch (e) {
            // Resend is best-effort in the demo.
        }
        this.digits = ['', '', '', ''];
        this.otp = '';
        this.timer = 59;
        this.scheduleAutofill();
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

Alpine.data('paymentForm', (selectedMethod, paymentLink, methods) => ({
    method: selectedMethod,
    paymentLink: paymentLink || '',
    methods: methods || [],
    copied: false,
    image: null,
    fileName: null,
    errors: {},
    get currentLink() {
        return (this.paymentLink || '').trim();
    },
    validate(event) {
        this.errors = {};

        if (! this.method) {
            this.errors.method = 'Select a payment method.';
        }

        const transaction = (document.querySelector('input[name="transaction_id"]')?.value || '').replace(/\s+/g, '');
        if (! transaction) {
            this.errors.transaction = 'Enter the transaction ID.';
        } else if (transaction.length < 12) {
            this.errors.transaction = 'The transaction ID must be at least 12 characters.';
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
    async copyLink() {
        if (! this.currentLink) {
            return;
        }
        await navigator.clipboard.writeText(this.currentLink);
        this.copied = true;
        clearTimeout(this._copiedTimer);
        this._copiedTimer = setTimeout(() => this.copied = false, 2000);
    },
}));

window.Alpine = Alpine;
Alpine.start();
