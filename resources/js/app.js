import Alpine from 'alpinejs';
import focus from '@alpinejs/focus';

Alpine.plugin(focus);
window.Alpine = Alpine;

/* ------------------------------------------------------------------
   reCAPTCHA v2 (checkbox). Loaded on demand the first time a form is
   used, so pages stay fast. Supports many widgets per page (explicit
   rendering), including inside the modal and the quiz.
   ------------------------------------------------------------------ */
const GoCaptcha = {
    siteKey: document.querySelector('meta[name="recaptcha-site-key"]')?.content || '',
    loading: false,
    ready: false,
    queue: [],
    load() {
        if (!this.siteKey || this.loading) return;
        this.loading = true;
        window.goCaptchaReady = () => {
            this.ready = true;
            this.queue.splice(0).forEach((fn) => fn());
        };
        const s = document.createElement('script');
        s.src = 'https://www.google.com/recaptcha/api.js?onload=goCaptchaReady&render=explicit';
        s.async = true;
        s.defer = true;
        document.head.appendChild(s);
    },
    render(el, onChange) {
        if (!this.siteKey || !el || el.dataset.widgetId) return;
        const go = () => {
            if (el.dataset.widgetId) return;
            const id = window.grecaptcha.render(el, {
                sitekey: this.siteKey,
                callback: () => onChange && onChange(true),
                'expired-callback': () => onChange && onChange(false),
                'error-callback': () => onChange && onChange(false),
            });
            el.dataset.widgetId = String(id);
        };
        this.ready ? go() : this.queue.push(go);
        this.load();
    },
    response(el) {
        const id = el?.dataset.widgetId;
        return id !== undefined && this.ready ? window.grecaptcha.getResponse(Number(id)) : '';
    },
    reset(el) {
        const id = el?.dataset.widgetId;
        if (id !== undefined && this.ready) window.grecaptcha.reset(Number(id));
    },
};
window.GoCaptcha = GoCaptcha;

/* ------------------------------------------------------------------
   Enquiry form. One component for every form on the site.
   Posts JSON to the same endpoint as the no-JS fallback.
   ------------------------------------------------------------------ */
Alpine.data('enquiryForm', (config = {}) => ({
    formName: config.formName || 'contact',
    errors: {},
    submitting: false,
    done: false,
    generalError: '',
    captchaOk: false,
    consent: false,

    init() {
        if (config.captcha === 'now') {
            // The quiz contact step only appears after the visitor has already engaged.
            this.$nextTick(() => this.mountCaptcha());
            return;
        }
        if (config.captcha === 'modal') {
            // The modal form exists on every page but stays hidden: load reCAPTCHA when it opens.
            this.$watch('$store.modal.show', (open) => open && this.$nextTick(() => this.mountCaptcha()));
            return;
        }
        // Standalone forms load reCAPTCHA on first touch, keeping first load fast.
        const mount = () => this.mountCaptcha();
        this.$el.addEventListener('focusin', mount, { once: true });
        this.$el.addEventListener('pointerdown', mount, { once: true });
    },

    mountCaptcha() {
        GoCaptcha.render(this.$refs.captcha, (ok) => (this.captchaOk = ok));
    },

    fieldError(name) {
        return (this.errors[name] || [])[0] || '';
    },

    async submit() {
        this.errors = {};
        this.generalError = '';

        this.mountCaptcha();
        if (!this.consent) {
            this.errors = { consent: ['Please tick the box to agree before sending.'] };
            this.$refs.consent?.focus();
            return;
        }
        if (GoCaptcha.siteKey && !GoCaptcha.response(this.$refs.captcha)) {
            this.errors = { 'g-recaptcha-response': ['Please tick "I\'m not a robot".'] };
            return;
        }

        this.submitting = true;
        const fd = new FormData(this.$refs.form);
        fd.set('page_url', window.location.href);

        try {
            const res = await fetch(this.$refs.form.action, {
                method: 'POST',
                body: fd,
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
            });
            const data = await res.json().catch(() => ({}));

            if (res.status === 422) {
                this.errors = data.errors || {};
                GoCaptcha.reset(this.$refs.captcha);
                this.captchaOk = false;
                this.$nextTick(() => this.$el.querySelector('[aria-invalid="true"]')?.focus());
            } else if (res.status === 429) {
                this.generalError = 'Too many attempts. Please wait a minute and try again.';
            } else if (res.ok && data.redirect) {
                this.done = true;
                window.dataLayer = window.dataLayer || [];
                window.dataLayer.push({ event: 'enquiry_submit', form_name: this.formName });
                setTimeout(() => (window.location.href = data.redirect), 700);
            } else {
                this.generalError = data.message || 'Something went wrong. Please try again.';
                GoCaptcha.reset(this.$refs.captcha);
            }
        } catch (e) {
            this.generalError = 'We could not send your enquiry. Check your connection and try again.';
        } finally {
            this.submitting = false;
        }
    },
}));

/* ------------------------------------------------------------------
   Hero quiz. Steps and options come from the database (admin editable).
   Tile steps advance on click, dropdown steps use a Next button.
   The last step is the shared enquiry form with the answers attached.
   ------------------------------------------------------------------ */
Alpine.data('quiz', (steps) => ({
    steps,
    i: 0,
    answers: {},
    labels: {},
    selects: {},

    get total() {
        return this.steps.length + 1;
    },
    get onContact() {
        return this.i >= this.steps.length;
    },
    get step() {
        return this.steps[this.i];
    },
    get progress() {
        return Math.round(((this.i + 1) / this.total) * 100);
    },
    get answersJson() {
        return JSON.stringify(this.answers);
    },
    get summary() {
        return this.steps
            .filter((s) => this.labels[s.key])
            .map((s) => ({ q: s.question, a: this.labels[s.key] }));
    },

    pick(opt) {
        this.answers[this.step.key] = opt.value;
        this.labels[this.step.key] = opt.label;
        setTimeout(() => this.next(), 180);
    },
    isPicked(opt) {
        return this.answers[this.step.key] === opt.value;
    },
    selectChange(e) {
        const opt = this.step.options.find((o) => o.value === e.target.value);
        if (opt) {
            this.answers[this.step.key] = opt.value;
            this.labels[this.step.key] = opt.label;
        } else {
            delete this.answers[this.step.key];
            delete this.labels[this.step.key];
        }
    },
    next() {
        if (this.i < this.steps.length) {
            this.i++;
            if (this.onContact) {
                GoCaptcha.load();
                window.dataLayer = window.dataLayer || [];
                window.dataLayer.push({ event: 'quiz_complete_steps' });
            }
            this.$nextTick(() => this.$refs.card?.focus());
        }
    },
    back() {
        if (this.i > 0) this.i--;
    },
}));

/* Modal + shared opener. Any element can call $store.modal.open({...}). */
Alpine.store('modal', {
    show: false,
    mode: 'quote', // quote | question
    tourType: '',
    packageId: '',
    packageName: '',
    heading: '',
    open(opts = {}) {
        this.mode = opts.mode || 'quote';
        this.tourType = opts.tourType || '';
        this.packageId = opts.packageId || '';
        this.packageName = opts.packageName || '';
        this.heading = opts.heading || '';
        this.show = true;
        GoCaptcha.load();
        document.documentElement.classList.add('overflow-hidden');
    },
    close() {
        this.show = false;
        document.documentElement.classList.remove('overflow-hidden');
    },
});

Alpine.start();
