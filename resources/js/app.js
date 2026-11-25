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
   Paged carousel (Homepage Test packages): a native scroll-snap track, so touch swiping works with no code. The number of cards per
   view comes from the CSS widths (3 desktop, 2 tablet, 1 mobile); pages = cards / per view. No autoplay.
   ------------------------------------------------------------------ */
Alpine.data('pageCarousel', () => ({
    page: 0,
    pages: 1,
    perView: 1,

    ready: false,

    /* Measuring reads layout, so it waits until the carousel is about to scroll into view instead of running (nine times) while the page loads. */
    init() {
        const start = () => { if (this.ready) return; this.ready = true; this.measure(); };
        if ('IntersectionObserver' in window) {
            const io = new IntersectionObserver((entries) => { if (entries.some((e) => e.isIntersecting)) { io.disconnect(); start(); } }, { rootMargin: '600px 0px' });
            this.$nextTick(() => io.observe(this.$el));
        } else {
            this.$nextTick(start);
        }
        let raf = 0;
        window.addEventListener('resize', () => { if (! this.ready) return; cancelAnimationFrame(raf); raf = requestAnimationFrame(() => this.measure()); }, { passive: true });
    },
    cards() {
        return this.$refs.track ? this.$refs.track.children.length : 0;
    },
    step() {
        const t = this.$refs.track;
        const first = t && t.children[0];
        if (!first) return 1;
        return first.getBoundingClientRect().width + (parseFloat(getComputedStyle(t).columnGap) || 0);
    },
    measure() {
        const t = this.$refs.track;
        if (!t) return;
        const gap = parseFloat(getComputedStyle(t).columnGap) || 0;
        this.perView = Math.max(1, Math.round((t.clientWidth + gap) / this.step()));
        this.pages = Math.max(1, Math.ceil(this.cards() / this.perView));
        this.update();
    },
    update() {
        const t = this.$refs.track;
        if (!t) return;
        const atEnd = t.scrollLeft + t.clientWidth >= t.scrollWidth - 4;
        this.page = atEnd ? this.pages - 1 : Math.min(this.pages - 1, Math.round(t.scrollLeft / (this.step() * this.perView)));
    },
    go(p) {
        if (! this.ready) { this.ready = true; this.measure(); }
        const t = this.$refs.track;
        const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        const target = Math.max(0, Math.min(this.pages - 1, p));
        t.scrollTo({ left: target * this.perView * this.step(), behavior: reduce ? 'auto' : 'smooth' });
    },
    prev() { this.go(this.page - 1); },
    next() { this.go(this.page + 1); },
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

/* ------------------------------------------------------------------
   Journey map (home "The Journey Never Ends"). Dots and polaroids open a
   detail card with the city's photos; polaroid photos crossfade on a staggered
   timer (paused on hover, off-screen and with reduced motion); the routes draw
   in when the section scrolls into view; polaroids drift with the mouse.
   ------------------------------------------------------------------ */
Alpine.data('journeyMap', (cities) => ({
    cities,
    open: null,
    hover: null,
    card: 0,
    inView: false,
    reduce: false,
    idx: Object.fromEntries(cities.map((c) => [c.slug, 0])),
    trigger: null,
    raf: 0,
    pos: '',

    get cur() {
        return this.cities.find((c) => c.slug === this.open) || null;
    },
    lit(a, b) {
        const s = this.open || this.hover;
        return s === a || s === b;
    },
    init() {
        this.reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        const root = this.$el;
        if (this.reduce) root.classList.add('jm-reduce');
        if ('IntersectionObserver' in window) {
            new IntersectionObserver(([e]) => {
                this.inView = e.isIntersecting;
                if (e.isIntersecting) root.classList.add('is-in');
            }, { threshold: 0.2 }).observe(root);
        } else {
            this.inView = true;
            root.classList.add('is-in');
        }
        if (this.reduce) return;
        // Staggered photo cycling: every polaroid starts at a different moment and runs at a slightly different pace.
        this.cities.filter((c) => c.polaroid && c.images.length > 1).forEach((c, i) => {
            setTimeout(() => setInterval(() => this.tick(c), 3100 + i * 290), 500 + i * 780);
        });
    },
    tick(c) {
        if (!this.inView || document.hidden || this.hover === c.slug) return;
        this.idx[c.slug] = (this.idx[c.slug] + 1) % c.images.length;
    },
    toggle(slug, ev) {
        if (this.open === slug) return this.close();
        this.open = slug;
        this.card = 0;
        this.trigger = ev?.currentTarget || null;
        this.place();
        if (ev && ev.detail === 0) this.$nextTick(() => document.getElementById('jm-card')?.focus()); // keyboard users land in the card
    },
    close(restoreFocus = false) {
        if (!this.open) return;
        this.open = null;
        if (restoreFocus && this.trigger) this.trigger.focus();
    },
    // Put the card beside the city on the side that covers the fewest other dots and polaroids (never its own dot).
    place() {
        const c = this.cur;
        const wrap = this.$refs.wrap;
        const stage = this.$refs.stage;
        if (!c || !wrap || !stage) return;
        const W = stage.clientWidth;
        const H = stage.clientHeight;
        if (W < 560) {
            this.pos = ''; // phones: the card flows under the map
            return;
        }
        const cx = (c.x / 100) * W;
        const cy = (c.y / 100) * H;
        const cw = Math.min(264, W * 0.64);
        const ch = 292;
        const g = 28;
        const clamp = (v, lo, hi) => Math.max(lo, Math.min(hi, v));
        const options = [
            [cx + g, cy - ch / 2], [cx - g - cw, cy - ch / 2], [cx - cw / 2, cy + g], [cx - cw / 2, cy - ch - g],
            [4, H - ch - 4], [W - cw - 4, H - ch - 4], [W - cw - 4, 4], [4, 4], // the empty corners of the map
        ].map(([l, t]) => ({ l: clamp(l, 4, W - cw - 4), t: clamp(t, 4, H - ch - 4) }));
        const hit = (r, px, py, pad) => px > r.l - pad && px < r.l + cw + pad && py > r.t - pad && py < r.t + ch + pad;
        const score = (r) => {
            let s = hit(r, cx, cy, 14) ? 100 : 0;
            this.cities.forEach((o) => {
                if (o.slug !== c.slug && hit(r, (o.x / 100) * W, (o.y / 100) * H, 16)) s += 3;
                if (o.polaroid) {
                    const pw = W * 0.19;
                    const ph = pw * 0.86;
                    const px = (o.polaroid.x / 100) * W;
                    const py = (o.polaroid.y / 100) * H;
                    const ox = Math.max(0, Math.min(r.l + cw, px + pw / 2) - Math.max(r.l, px - pw / 2));
                    const oy = Math.max(0, Math.min(r.t + ch, py + ph / 2) - Math.max(r.t, py - ph / 2));
                    s += ((ox * oy) / (pw * ph)) * (o.slug === c.slug ? 6 : 3);
                }
            });
            return s;
        };
        const far = (r) => (Math.hypot(r.l + cw / 2 - cx, r.t + ch / 2 - cy) / W) * 3; // prefer a spot close to the city
        const best = options.map((r, i) => ({ r, s: score(r) + far(r) + i * 0.01 })).sort((a, b) => a.s - b.s)[0].r;
        this.pos = 'left:' + Math.round(best.l) + 'px;top:' + Math.round(best.t) + 'px';
    },
    step(d) {
        const n = this.cur ? this.cur.images.length : 1;
        this.card = (this.card + d + n) % n;
    },
    parallax(e) {
        if (this.reduce || e.pointerType === 'touch') return;
        const stage = e.currentTarget;
        cancelAnimationFrame(this.raf);
        this.raf = requestAnimationFrame(() => {
            const r = stage.getBoundingClientRect();
            stage.style.setProperty('--px', (((e.clientX - r.left) / r.width - 0.5) * 2).toFixed(3));
            stage.style.setProperty('--py', (((e.clientY - r.top) / r.height - 0.5) * 2).toFixed(3));
        });
    },
    resetParallax() {
        const stage = this.$refs.stage;
        stage.style.setProperty('--px', 0);
        stage.style.setProperty('--py', 0);
    },
}));

/* Sections marked data-lazy-alpine (below the fold on the home page) carry x-ignore, so Alpine does not set up their carousels while the page loads.
   They are initialised just before they scroll into view, which splits the start-up work into small pieces (less main-thread blocking). */
const lazyRoots = document.querySelectorAll('[data-lazy-alpine]');
const wake = (el) => { el.removeAttribute('x-ignore'); delete el._x_ignore; Alpine.initTree(el); };
if ('IntersectionObserver' in window && lazyRoots.length) {
    const io = new IntersectionObserver((entries) => {
        for (const e of entries) {
            if (e.isIntersecting) { io.unobserve(e.target); wake(e.target); }
        }
    }, { rootMargin: '900px 0px' });
    lazyRoots.forEach((el) => io.observe(el));
} else {
    lazyRoots.forEach((el) => el.removeAttribute('x-ignore'));
}

/* Photos marked data-defer-src (the hero slideshow's 2nd and 3rd photo) are only requested once the page has loaded and gone idle,
   so they do not compete with the first photo, the stylesheet and the fonts for bandwidth while the page is painting. */
const deferredPhotos = document.querySelectorAll('img[data-defer-srcset]');
if (deferredPhotos.length) {
    const loadDeferred = () => deferredPhotos.forEach((img) => { img.srcset = img.dataset.deferSrcset; img.src = img.dataset.deferSrc; });
    const kick = () => setTimeout(() => ('requestIdleCallback' in window ? requestIdleCallback(loadDeferred, { timeout: 3000 }) : loadDeferred()), 1200);
    document.readyState === 'complete' ? kick() : window.addEventListener('load', kick, { once: true });
}

Alpine.start();
