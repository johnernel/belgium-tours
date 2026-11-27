{{-- The hero headline, styled exactly like the home page hero: white serif, two lines (split at the first comma, otherwise halfway through the words),
     a soft text shadow and the orange brush underline under the second line. Include with: title (the *accent* markers are ignored here).
     Optional oneLine: from sm up the whole headline stays on ONE line and the font shrinks to fit the column when the words are long
     (--fit is roughly the headline's width in em); on phones a long headline may still wrap. --}}
@php
    $headline = \App\Support\Heading::titleCase(trim(str_replace('*', '', (string) $title)));
    $single = ! empty($oneLine);
    [$hl1, $hl2] = str_contains($headline, ',') ? array_map('trim', explode(',', $headline, 2)) : [$headline, null];
    if ($hl2 === null) { $w = explode(' ', $headline); $half = (int) ceil(count($w) / 2); $hl1 = implode(' ', array_slice($w, 0, $half)); $hl2 = implode(' ', array_slice($w, $half)); } else { $hl1 .= ','; }
    if ($single) { [$hl1, $hl2] = ['', $headline]; }
    $fit = round(max(1, mb_strlen($headline)) * 0.52, 2);
    $size = $single ? 'text-[2.65rem] text-balance sm:whitespace-nowrap sm:text-[length:min(4.25rem,calc(100cqw/var(--fit)))]' : 'text-[2.65rem] sm:text-6xl lg:text-[4.25rem]';
@endphp
@if($single)<div class="@container">@endif
<h1 class="on-dark hero-rise mt-6 [text-shadow:0_2px_4px_rgb(0_0_0/0.45),0_4px_24px_rgb(0_0_0/0.5)] font-serif font-medium leading-[1.05] !text-white {{ $size }}"@if($single) style="--fit: {{ $fit }}"@endif>
    {{ $hl1 }}@if($hl2 !== '') @if($hl1 !== '')<br>@endif<span class="relative inline-block text-white">{{ $hl2 }}
        <svg viewBox="0 0 300 14" preserveAspectRatio="none" class="pointer-events-none absolute left-0 top-full -mt-1 h-3 w-full text-amber max-sm:left-1/2 max-sm:w-[72%] max-sm:-translate-x-1/2 sm:h-3.5 lg:-mt-0.5 lg:h-4" fill="currentColor" aria-hidden="true"><path d="M1 6.5C70 3 190 1 299 1.4 193 4.6 78 10.6 1 13Z"/></svg>
    </span>@endif
</h1>
@if($single)</div>@endif
