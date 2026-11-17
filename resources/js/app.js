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
Alpine.data('quiz', (allSteps, preset = {}, tourType = '') => {
    // Steps already answered elsewhere (e.g. the tour type page) are pre-filled and skipped.
    const answers = {};
    const labels = {};
    Object.entries(preset || {}).forEach(([key, value]) => {
        const opt = allSteps.find((s) => s.key === key)?.options.find((o) => o.value === value);
        if (opt) {
            answers[key] = value;
            labels[key] = opt.label;
        }
    });
    const steps = allSteps.filter((s) => !(s.key in answers));

    return {
    steps,
    tourType,
    i: 0,
    answers,
    labels,

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
    arrow: false,
    // Dropdown steps move on by themselves once an option is chosen. Keyboard users stepping through the list with the
    // arrow keys only choose (a browser fires change on every arrow press); Enter then confirms and moves on.
    selectKey(e) {
        if (['ArrowUp', 'ArrowDown', 'Home', 'End', 'PageUp', 'PageDown'].includes(e.key)) {
            this.arrow = true;
        } else if (e.key === 'Enter' && this.answers[this.step.key]) {
            e.preventDefault();
            this.next();
        }
    },
    selectChange(e) {
        const opt = this.step.options.find((o) => o.value === e.target.value);
        if (opt) {
            this.answers[this.step.key] = opt.value;
            this.labels[this.step.key] = opt.label;
            if (this.arrow) {
                this.arrow = false;
            } else {
                const key = this.step.key;
                setTimeout(() => { if (!this.onContact && this.step.key === key) this.next(); }, 180);
            }
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
    };
});

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

/* ------------------------------------------------------------------
   Journey carousel: native scroll-snap track with arrows and dots.
   ------------------------------------------------------------------ */
Alpine.data('carousel', () => ({
    index: 0,
    canPrev: false,
    canNext: true,
    count: 0,

    init() {
        this.count = this.$refs.track ? this.$refs.track.children.length : 0;
        this.$nextTick(() => this.update());
        window.addEventListener('resize', () => this.update(), { passive: true });
    },
    get active() {
        return this.canNext ? this.index : this.count - 1;
    },
    step() {
        const t = this.$refs.track;
        const first = t.children[0];
        if (!first) return t.clientWidth;
        return first.getBoundingClientRect().width + (parseFloat(getComputedStyle(t).columnGap) || 0);
    },
    update() {
        const t = this.$refs.track;
        if (!t) return;
        this.index = Math.min(this.count - 1, Math.round(t.scrollLeft / this.step()));
        this.canPrev = t.scrollLeft > 4;
        this.canNext = t.scrollLeft + t.clientWidth < t.scrollWidth - 4;
    },
    scrollTo(left) {
        const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        this.$refs.track.scrollTo({ left, behavior: reduce ? 'auto' : 'smooth' });
    },
    prev() {
        this.scrollTo(this.$refs.track.scrollLeft - this.step());
    },
    next() {
        this.scrollTo(this.$refs.track.scrollLeft + this.step());
    },
    go(i) {
        this.scrollTo(i * this.step());
    },
}));

/* ------------------------------------------------------------------
   Background video: loaded only when it is about to scroll into view,
   paused while off screen, and skipped entirely for reduced motion or
   data-saver visitors (the poster image stays).
   ------------------------------------------------------------------ */
function initLazyVideos() {
    const skip = window.matchMedia('(prefers-reduced-motion: reduce)').matches || navigator.connection?.saveData;

    document.querySelectorAll('video[data-lazy-video]').forEach((video) => {
        let loaded = false;
        const io = new IntersectionObserver(
            (entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting && !loaded) {
                        loaded = true;
                        if (video.dataset.poster) video.poster = video.dataset.poster; // poster always; footage only if allowed
                        if (skip) {
                            io.disconnect();
                            return;
                        }
                        [['webm', 'video/webm'], ['mp4', 'video/mp4']].forEach(([key, type]) => {
                            if (!video.dataset[key]) return;
                            const s = document.createElement('source');
                            s.src = video.dataset[key];
                            s.type = type;
                            video.appendChild(s);
                        });
                        video.load();
                    }
                    if (loaded) {
                        entry.isIntersecting ? video.play().catch(() => {}) : video.pause();
                    }
                });
            },
            { rootMargin: '300px 0px' },
        );
        io.observe(video);
    });
}
document.addEventListener('DOMContentLoaded', initLazyVideos);

/* ------------------------------------------------------------------
   Testimonials carousel: slides move sideways. Arrows, round selectors,
   arrow keys and swipe. Auto-advances every 7 s unless the visitor is
   hovering or focused inside it, the tab is hidden or motion is reduced.
   ------------------------------------------------------------------ */
Alpine.data('quoteCarousel', (count) => ({
    i: 0,
    hold: false,
    startX: 0,
    go(n) { this.i = (n + count) % count; this.restart(); },
    next() { this.go(this.i + 1); },
    prev() { this.go(this.i - 1); },
    swipeStart(e) { this.startX = e.changedTouches[0].clientX; },
    swipeEnd(e) {
        const d = e.changedTouches[0].clientX - this.startX;
        if (Math.abs(d) > 50) { d < 0 ? this.next() : this.prev(); }
    },
    restart() {
        clearInterval(this.timer);
        if (count < 2 || window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
        this.timer = setInterval(() => { if (!this.hold && !document.hidden) this.i = (this.i + 1) % count; }, 7000);
    },
    init() { this.restart(); },
}));

/* ------------------------------------------------------------------
   Gallery marquee: cards drift left forever; each card is bent toward the
   viewer by its distance from the centre (big and angled at the edges,
   small and flat in the middle). Pauses on hover and when off-screen; with
   reduced motion the cards stay flat and the row scrolls by hand.
   ------------------------------------------------------------------ */
Alpine.data('gallery', () => ({
    init() {
        const stage = this.$refs.stage;
        const track = this.$refs.track;
        const cards = [...track.children];
        if (!cards.length) return;
        if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            stage.classList.add('overflow-x-auto');
            return;
        }
        let x = 0, last = 0, paused = false, visible = false, raf = 0, setW = 0, stageW = 0, centres = [], widths = [];
        const tilt = [], scale = [], shift = [];
        const measure = () => {
            stageW = stage.clientWidth;
            centres = cards.map((c) => c.offsetLeft + c.offsetWidth / 2);
            widths = cards.map((c) => c.offsetWidth);
            // The list is rendered twice: one loop is the distance from a card to its twin (includes the gap).
            const half = cards.length / 2;
            setW = cards[half].offsetLeft - cards[0].offsetLeft;
        };
        const frame = (t) => {
            raf = visible ? requestAnimationFrame(frame) : 0;
            const dt = last ? Math.min(t - last, 50) : 16;
            last = t;
            if (!paused) {
                x -= (stageW < 640 ? 0.035 : 0.05) * dt;
                if (x <= -setW) x += setW;
            }
            track.style.transform = 'translate3d(' + x + 'px,0,0)';
            // 1) each card's scale and tilt come from where it sits on screen
            const mid = stageW / 2;
            for (let i = 0; i < cards.length; i++) {
                const n = Math.max(-1.4, Math.min(1.4, (centres[i] + x - mid) / mid));
                const a = Math.abs(n);
                tilt[i] = n * -26;
                scale[i] = 0.78 + 0.62 * a * a;
            }
            // 2) scaling shrinks or grows a card around its centre without moving its neighbours, so the gaps
            //    would stretch in the middle and overlap at the edges. Shift every card by the width its
            //    neighbours gained or lost so the gap stays the same, anchored at the screen centre.
            let acc = 0, anchor = 0, found = false;
            for (let i = 0; i < cards.length; i++) {
                shift[i] = acc; // total growth of all cards to the left of this one
                if (!found && centres[i] + x >= mid) {
                    const prev = i ? shift[i - 1] : 0;
                    const span = i ? centres[i] - centres[i - 1] : 1;
                    const f = i ? (mid - (centres[i - 1] + x)) / span : 1;
                    anchor = prev + (shift[i] - prev) * Math.max(0, Math.min(1, f));
                    found = true;
                }
                acc += widths[i] * (scale[i] - 1);
            }
            for (let i = 0; i < cards.length; i++) {
                const tx = shift[i] - anchor + (widths[i] * (scale[i] - 1)) / 2;
                cards[i].style.transform = 'translateX(' + tx.toFixed(1) + 'px) perspective(900px) rotateY(' + tilt[i].toFixed(2) + 'deg) scale(' + scale[i].toFixed(3) + ')';
            }
        };
        const start = () => { if (!raf) { last = 0; raf = requestAnimationFrame(frame); } };
        measure();
        window.addEventListener('resize', measure);
        stage.addEventListener('pointerenter', (e) => { if (e.pointerType === 'mouse') paused = true; });
        stage.addEventListener('pointerleave', () => { paused = false; });
        new IntersectionObserver(([e]) => { visible = e.isIntersecting; if (visible) start(); }, { rootMargin: '100px' }).observe(stage);
        track.querySelectorAll('img').forEach((i) => i.addEventListener('load', measure, { once: true }));
    },
}));

Alpine.start();
