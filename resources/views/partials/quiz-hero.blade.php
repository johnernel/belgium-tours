{{--
    Photo hero with the headline and description on the left and the quiz on the right.
    Include with: heroTitle (supports *accent* markers), heroText, heroImage (photo base path), heroAlt, crumb (breadcrumb label),
    plus the quiz variables quizSteps / quizPreset / quizTourType from the controller. Optional: crumbMid [url, label] (a link between Home and the crumb), heroTitleClass (headline size classes), quizPackage (a Package the enquiry is about), and content pushed to the 'hero-extra' stack (shown under the text). The page must set @section('hero').
--}}
<section class="relative isolate -mt-[4.5rem] flex min-h-[40rem] items-center overflow-hidden bg-ink pt-[4.5rem] text-white lg:min-h-[44rem]">
    @if(!empty($heroImage))
        <x-photo :base="$heroImage" :alt="$heroAlt ?? ''" sizes="100vw" :eager="true" class="absolute inset-0 z-0 size-full" />
    @endif
    <div class="absolute inset-0 z-[1] bg-gradient-to-b from-ink/55 via-ink/35 to-ink/85"></div>
    <div class="absolute inset-0 z-[1] bg-gradient-to-r from-ink/75 via-ink/35 to-transparent"></div>

    <div class="container-page relative z-10 grid w-full items-center gap-10 py-14 sm:py-16 lg:grid-cols-[1.1fr_0.9fr] lg:gap-14 lg:py-20">
        <div>
            <p class="hero-rise mb-4 text-sm font-semibold text-white/85"><a href="{{ route('home') }}" class="underline decoration-amber decoration-2 underline-offset-4">Home</a> / @isset($crumbMid)<a href="{{ $crumbMid[0] }}" class="underline decoration-amber decoration-2 underline-offset-4">{{ $crumbMid[1] }}</a> / @endisset{{ $crumb }}</p>
            <h1 class="on-dark hero-rise hero-rise-2 font-serif font-medium !text-white {{ $heroTitleClass ?? 'max-w-[16ch] text-[2.65rem] leading-[1.02] sm:text-6xl lg:max-w-[14ch] lg:text-[4.5rem]' }}">@heading($heroTitle)</h1>
            @if(filled($heroText ?? null))<p class="hero-rise hero-rise-2 mt-8 max-w-lg text-lg text-white/90 sm:text-xl">{{ $heroText }}</p>@endif
            @stack('hero-extra')
        </div>

        <div class="hero-rise hero-rise-2 lg:w-full lg:max-w-[28rem] lg:justify-self-end">
            @include('partials.quiz')
        </div>
    </div>
</section>
