{{-- Gallery: centred intro, then a curved marquee of photo cards (see the `gallery` Alpine component). Uses $c. --}}
@php
    $galleryPhotos = [
        ['bruges-canal', 'Brick houses beside a canal in Bruges'],
        ['gravensteen', 'The medieval Gravensteen castle in Ghent at sunset'],
        ['la-roche', 'The Ardennes town of La Roche-en-Ardenne in its river valley'],
        ['brussels-grand-place', 'The Grand Place in Brussels at twilight'],
        ['city-leuven', 'The Grote Markt in Leuven'],
        ['flower-carpet', 'A flower carpet laid out in front of the Brussels Grand Place'],
        ['ghent-graslei', 'The Graslei and Korenlei quays in Ghent'],
        ['countryside', 'Countryside near Falmignoul in the Belgian Ardennes'],
        ['bruges-belfry', 'The Belfry tower rising above Bruges'],
        ['city-antwerp', 'The clock of Antwerp cathedral'],
    ];
@endphp
<section class="overflow-hidden py-16 sm:py-24" aria-labelledby="gal-title" x-data="gallery()">
    <div class="container-page text-center">
        <p class="text-sm font-semibold uppercase tracking-[0.22em] text-slate">{{ $c('gal_eyebrow') ?: 'From the road' }}</p>
        <h2 id="gal-title" class="mx-auto mt-4 max-w-3xl text-heading">@heading($c('gal_heading') ?: 'Belgium in *pictures*')</h2>
        <p class="mx-auto mt-5 max-w-xl text-lg leading-relaxed text-slate">{{ $c('gal_text') }}</p>
        <div class="mt-8">
            <x-button variant="cta" size="lg" icon="arrow-right" @click="$store.modal.open()">{{ $c('gal_cta') ?: 'Start planning' }}</x-button>
        </div>
    </div>

    {{-- Photo credits for these images are in docs/reference/IMAGE_CREDITS.md and config/credits.php. The second copy is hidden from assistive tech. --}}
    <div x-ref="stage" class="mt-12 py-10 sm:mt-14" role="group" aria-label="Photo gallery" style="perspective: 1400px">
        <div x-ref="track" class="flex w-max items-center gap-3 will-change-transform sm:gap-4">
            @foreach([0, 1] as $copy)
                @foreach($galleryPhotos as [$base, $alt])
                    <div class="relative h-48 w-32 shrink-0 overflow-hidden rounded-2xl bg-sky-soft shadow-card will-change-transform sm:h-72 sm:w-44 lg:h-[340px] lg:w-52" @if($copy) aria-hidden="true" @endif>
                        <x-photo :base="'images/photos/'.$base" :alt="$copy ? '' : $alt" :widths="[480, 800]" sizes="(min-width:1024px) 300px, 200px" :ratio="[480, 640]" class="absolute inset-0 size-full" draggable="false" />
                    </div>
                @endforeach
            @endforeach
        </div>
    </div>
</section>
