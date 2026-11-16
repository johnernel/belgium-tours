{{-- Home "Journal" section: heading + "All articles" link, hairline, three post cards. Uses $c and $posts. --}}
@if($posts->isNotEmpty())
    <section class="bg-cream py-16 sm:py-24" aria-labelledby="blog-title">
        <div class="container-page">
            <div class="flex flex-wrap items-end justify-between gap-x-8 gap-y-4 border-b border-ink/15 pb-8">
                <div>
                    <p class="flex items-center gap-4 text-xs font-semibold uppercase tracking-[0.22em] text-slate">
                        <span class="h-px w-10 bg-amber" aria-hidden="true"></span>{{ $c('blog_eyebrow') ?: 'The Journal' }}
                    </p>
                    <h2 id="blog-title" class="mt-5 max-w-xl text-heading heading-amber">@heading($c('blog_heading') ?: 'Stories from the *Lowlands.*')</h2>
                </div>
                <a href="{{ route('journal.index') }}" class="inline-flex min-h-11 items-center gap-2 border-b-2 border-ink/80 text-xs font-semibold uppercase tracking-[0.18em] text-ink hover:border-amber hover:text-amber-ink">
                    {{ $c('blog_all') ?: 'All articles' }} @icon('arrow-up-right', 'size-3.5')
                </a>
            </div>

            <div class="mt-10 grid gap-x-6 gap-y-12 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($posts as $post)
                    @include('partials.post-card', ['post' => $post])
                @endforeach
            </div>
        </div>
    </section>
@endif
