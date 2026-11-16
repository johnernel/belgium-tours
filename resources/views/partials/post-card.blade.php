{{-- One Journal card: tall photo with a category tag, serif title, short excerpt, "N min read" and a Read more link.
     The title link is stretched over the whole card, so there is a single link per card. Include with: post. --}}
<article class="group relative">
    <div class="relative aspect-[4/3] overflow-hidden rounded-3xl bg-sky-soft shadow-card sm:aspect-[3/4]">
        @if($post->image)
            <x-photo :base="$post->image" :alt="$post->image_alt ?: ''" sizes="(min-width:1024px) 30vw, (min-width:640px) 45vw, 92vw" fetchpriority="low" :ratio="[1200, 1600]" class="absolute inset-0 size-full transition-transform duration-700 group-hover:scale-105 motion-reduce:transition-none" />
        @endif
        @if($post->category)
            <span class="absolute left-4 top-4 rounded-full bg-aqua px-3.5 py-1.5 text-[0.7rem] font-semibold uppercase tracking-[0.16em] text-ink">{{ $post->category }}</span>
        @endif
    </div>
    <h3 class="mt-5 text-2xl leading-snug">
        <a href="{{ route('journal.show', $post->slug) }}" class="after:absolute after:inset-0 focus-visible:outline-none">{{ $post->title }}</a>
    </h3>
    @if($post->excerpt)<p class="mt-2 line-clamp-2 text-[0.95rem] leading-relaxed text-ink/85">{{ $post->excerpt }}</p>@endif
    <p class="mt-3 flex items-center gap-4 text-xs font-semibold uppercase tracking-[0.16em] text-ink/70">
        <span>{{ $post->read_minutes }} min read</span>
        <span class="h-px w-8 bg-ink/25" aria-hidden="true"></span>
        <span class="inline-flex items-center gap-1.5 text-amber-ink normal-case tracking-normal underline decoration-amber decoration-2 underline-offset-4 group-hover:text-ink" aria-hidden="true">Read more @icon('arrow-right', 'size-3.5')</span>
    </p>
</article>
