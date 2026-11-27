{{--
    Photo hero with the headline and description on the left and the quiz on the right. It is built like the HOME page hero: same height, same layout
    (container, columns, spacing), same overlay and headline styling (partials/hero-headline); a single photo with the slow zoom instead of the slideshow;
    and NO "Authentic Trips, Belgian Heart" badge (that one is for the home page only), a small breadcrumb sits there instead.
    Include with: heroTitle (the *accent* markers are ignored), heroText, heroImage (photo base path), heroAlt, crumb (breadcrumb label),
    plus the quiz variables quizSteps / quizPreset / quizTourType from the controller. Optional: crumbMid [url, label] (a link between Home and the crumb),
    heroOneLine (true keeps the headline on one line from sm up), heroTwoLines (true keeps it on exactly two lines), hideCrumbOnPhones (true hides the breadcrumb below 640px), quizPackage (a Package the enquiry is about), and content pushed to the 'hero-extra' stack (shown under the text). The page must set @section('hero').
--}}
<section class="relative isolate -mt-[4.5rem] flex min-h-[44rem] flex-col overflow-hidden bg-ink text-white lg:-mt-[8rem] lg:min-h-[50rem]">
    <div class="absolute inset-0 z-0 overflow-hidden bg-ink" aria-hidden="true">
        @if(!empty($heroImage))
            <x-photo :base="$heroImage" :alt="$heroAlt ?? ''" sizes="(max-width: 1023px) 110vw, 100vw" :eager="true" class="hero-single absolute inset-0 size-full" />
        @endif
    </div>
    <div class="absolute inset-0 z-[1] bg-gradient-to-b from-ink/33 to-ink/26"></div>

    <div class="container-wide relative z-10 grid w-full flex-1 items-end gap-10 pb-12 pt-[calc(4.5rem+2.5rem)] lg:grid-cols-[1.15fr_0.85fr] lg:items-center lg:gap-14 lg:pb-14 lg:pt-[calc(8rem+1.5rem)]">
        <div class="min-w-0 lg:pt-24 max-sm:text-center">
            <p class="hero-rise text-sm font-semibold text-white [text-shadow:0_1px_8px_rgb(0_0_0/0.5)] {{ ! empty($hideCrumbOnPhones) ? 'max-sm:hidden' : '' }}"><a href="{{ route('home') }}" class="underline decoration-amber decoration-2 underline-offset-4">Home</a> / @isset($crumbMid)<a href="{{ $crumbMid[0] }}" class="underline decoration-amber decoration-2 underline-offset-4">{{ $crumbMid[1] }}</a> / @endisset{{ $crumb }}</p>
            @include('partials.hero-headline', ['title' => $heroTitle, 'oneLine' => $heroOneLine ?? false, 'twoLines' => $heroTwoLines ?? false])
            @if(filled($heroText ?? null))<p class="hero-rise hero-rise-2 mt-9 max-w-xl text-lg text-white [text-shadow:0_2px_4px_rgb(0_0_0/0.45),0_4px_24px_rgb(0_0_0/0.5)] max-sm:hidden sm:text-xl">{{ $heroText }}</p>@endif
            @stack('hero-extra')
        </div>

        <div class="hero-rise hero-rise-2 w-full lg:max-w-[32rem] lg:justify-self-end">
            @include('partials.quiz')
        </div>
    </div>
</section>
