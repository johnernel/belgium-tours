{{-- FAQ band (FAQs page and Packages page): ivory background, heading and a "Still have questions?" card on the left, a native <details> accordion on the right
     (one open at a time). Include with: faqs (collection of Faq). Optional: heading (supports *accent*), titleId, name (the accordion group name, unique per page),
     body (trusted HTML shown under the heading), eyebrow. Edit the look here. --}}
@php
    $heading = $heading ?? 'Questions, Answered';
    $titleId = $titleId ?? 'fp-title';
    $name = $name ?? 'faq-page';
    $eyebrow = array_key_exists('eyebrow', get_defined_vars()) ? $eyebrow : 'FAQ'; // pass eyebrow => null (or '') to leave the small label out
@endphp
<section class="bg-ivory py-16 sm:py-24" aria-labelledby="{{ $titleId }}">
    <div class="container-page grid gap-10 lg:grid-cols-[0.8fr_1.2fr] lg:gap-16">
        <div class="max-sm:text-center">
            @if(filled($eyebrow))
                {{-- Same label + diamond divider as the other section headings; left on tablet and desktop, centered on phones --}}
                @include('partials.ht-eyebrow', ['label' => $eyebrow, 'mobileCenter' => true])
            @endif
            <h2 id="{{ $titleId }}" class="@if(filled($eyebrow))mt-5 @endif text-heading">@heading($heading)</h2>
            @if(!empty($body))<div class="prose-page mt-6">{!! $body !!}</div>@endif

            {{-- Help card (hidden on phones; the heading is centered there): pale sage panel with an orange top bar, a round icon, dark text and two square buttons (no dark background) --}}
            <div class="mt-8 overflow-hidden bg-sky text-ink shadow-card max-sm:hidden">
                <div class="h-1 bg-amber" aria-hidden="true"></div>
                <div class="p-6 sm:p-8">
                    <span class="grid size-14 place-items-center rounded-full bg-white text-teal-deep shadow-card">@icon('headset', 'size-7')</span>
                    <h3 class="mt-5 font-serif text-2xl text-ink sm:text-[1.75rem]">Still have questions?</h3>
                    <p class="mt-3 max-w-sm text-[0.97rem] leading-relaxed text-ink/80">We are here to help with any queries. Reach out and get clear answers directly from our team.</p>
                    <div class="mt-6 flex flex-wrap gap-3">
                        <x-button square variant="cta" size="md" icon="arrow-right" @click="$store.modal.open()">Get Quote Now</x-button>
                        @if($site->phone)
                            <x-button square variant="dark" size="md" :href="'tel:'.$site->phone_href">@icon('phone', 'size-4') Call us</x-button>
                        @endif
                    </div>
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
