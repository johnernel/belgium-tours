{{-- Closing band (tour type, destination and travel style pages): include with label, heading (supports *accent*) and optional modalType (the tour type name sent with the quote request). Solid cream band (#F7F3EA), centered label with hairlines, two-colour heading, short divider, text and two buttons; the client's outlined drawing of a Belgian town hall and belfry (public/images/decor/belgium-outline.webp: transparent background, so it blends with the cream) faint in the bottom right corner --}}
<section class="relative isolate overflow-hidden bg-cream py-24 text-center sm:py-32" aria-labelledby="cta-title">
    <img src="{{ asset('images/decor/belgium-outline.webp') }}" alt="" width="1500" height="1000" loading="lazy" decoding="async" class="pointer-events-none absolute bottom-0 right-0 -z-10 h-auto w-[min(32rem,100%)] opacity-[0.38] [mask-image:linear-gradient(to_right,transparent,#000_30%),linear-gradient(to_top,transparent,#000_8%)] [mask-composite:intersect] [-webkit-mask-composite:source-in] max-sm:opacity-[0.22]" aria-hidden="true">
    <div class="container-page">
        <p class="flex items-center justify-center gap-4 text-xs font-semibold uppercase tracking-[0.22em] text-amber-ink">
            <span class="h-px w-10 bg-amber" aria-hidden="true"></span>{{ $label }}<span class="h-px w-10 bg-amber" aria-hidden="true"></span>
        </p>
        <h2 id="cta-title" class="mx-auto mt-6 max-w-3xl text-[2.25rem] leading-[1.1] sm:text-heading">@heading($heading)</h2>
        <span class="mx-auto mt-6 block h-0.5 w-12 bg-amber" aria-hidden="true"></span>
        <p class="mx-auto mt-6 max-w-xl text-lg text-ink/85">Tell us when you want to go and who is coming. We will send a free quote.</p>
        <div class="mt-9 flex flex-wrap justify-center gap-4">
            <x-button square size="lg" variant="cta" icon="arrow-right" @click="$store.modal.open({ tourType: {{ \Illuminate\Support\Js::from($modalType ?? '') }} })">Get a free quote</x-button>
            <x-button square size="lg" variant="outline" @click="$store.modal.open({ mode: 'question', tourType: {{ \Illuminate\Support\Js::from($modalType ?? '') }} })">Ask us a question</x-button>
        </div>
    </div>
</section>