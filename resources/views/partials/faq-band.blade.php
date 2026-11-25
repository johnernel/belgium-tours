{{-- FAQ band (FAQs page and Packages page): ivory background, heading and a "Still have questions?" card on the left, a native <details> accordion on the right
     (one open at a time). Include with: faqs (collection of Faq). Optional: heading (supports *accent*), titleId, name (the accordion group name, unique per page),
     body (trusted HTML shown under the heading), eyebrow. Edit the look here. --}}
@php
    $heading = $heading ?? 'Questions, Answered';
    $titleId = $titleId ?? 'fp-title';
    $name = $name ?? 'faq-page';
    $eyebrow = $eyebrow ?? 'FAQ';
@endphp
<section class="bg-ivory py-16 sm:py-24" aria-labelledby="{{ $titleId }}">
    <div class="container-page grid gap-10 lg:grid-cols-[0.8fr_1.2fr] lg:gap-16">
        <div>
            <p class="flex items-center gap-4 text-xs font-semibold uppercase tracking-[0.22em] text-slate">
                <span class="h-px w-10 bg-amber" aria-hidden="true"></span>{{ $eyebrow }}
            </p>
            <h2 id="{{ $titleId }}" class="mt-5 text-heading">@heading($heading)</h2>
            @if(!empty($body))<div class="prose-page mt-6">{!! $body !!}</div>@endif

            <div class="on-dark mt-8 rounded-3xl bg-dusk p-6 text-cream sm:p-7">
                <h3 class="font-serif text-2xl font-normal text-cream">Still have questions?</h3>
                <p class="mt-2 max-w-sm text-[0.95rem] leading-relaxed text-cream/90">We are here to help with any queries. Reach out and get clear answers directly from our team.</p>
                <div class="mt-5 flex flex-wrap gap-3">
                    @if($site->phone)
                        <x-button variant="light" size="sm" :href="'tel:'.$site->phone_href">@icon('phone', 'size-4') Call us</x-button>
                    @endif
                    <x-button variant="cta" size="sm" icon="arrow-right" @click="$store.modal.open()">Get Quote Now</x-button>
                </div>
            </div>
        </div>

        <div>
            @if($faqs->isEmpty())
                <p class="text-lg text-slate">Answers to common questions are coming soon.</p>
            @else
                <div class="border-t border-ink/10">
                    @foreach($faqs as $f)
                        <details name="{{ $name }}" @if($loop->first) open @endif class="group border-b border-ink/10 transition-colors open:rounded-2xl open:border-transparent open:bg-amber/20">
                            <summary class="flex cursor-pointer list-none items-center justify-between gap-4 rounded-2xl px-4 py-5 font-serif text-lg font-normal leading-snug text-ink sm:px-6 sm:text-xl [&::-webkit-details-marker]:hidden">
                                <span>{{ $f->question }}</span>
                                @icon('chevron-down', 'size-5 shrink-0 text-amber-ink transition-transform group-open:rotate-180')
                            </summary>
                            <div class="px-4 pb-6 text-[0.95rem] leading-relaxed text-ink/85 sm:px-6">{{ $f->answer }}</div>
                        </details>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</section>
