@props([])
{{-- Hero quiz card. Steps come from the database (Admin > Quiz). JS-only; <noscript> shows the plain form.
     Layout: frosted-glass panel with a dark title band, small uppercase question labels, glass option pills and a full-width
     button. The final contact step and the no-JS form sit on a light inner panel so the shared enquiry-form stays unchanged. --}}
<div class="on-dark relative overflow-hidden rounded-[1.75rem] border border-white/25 bg-ink/75 text-cream shadow-lift backdrop-blur-xl" id="quiz">
    <div class="bg-ink/90 px-5 py-5 sm:px-7 sm:py-6">
        <h2 id="quiz-title" class="text-center font-serif text-2xl font-light leading-snug text-cream sm:text-[1.75rem]">@heading('Belgium, Tailored to You in 30 Seconds')</h2>
    </div>

    <div class="p-5 sm:p-7">
        <div x-cloak x-data="quiz(@js($quizSteps), @js($quizPreset ?? []), @js($quizTourType ?? ''))" x-ref="card" tabindex="-1" role="group" aria-labelledby="quiz-title" class="outline-none"
             x-init="window.quizIcons = @js(\App\Support\Icons::all())">
            {{-- Question steps --}}
            <template x-if="!onContact">
                <div>
                    <p class="text-[0.95rem] font-bold uppercase leading-relaxed tracking-[0.14em] text-white" x-text="step.question" aria-live="polite"></p>

                    <template x-if="step.type === 'tiles'">
                        <div class="mt-4 grid gap-3" :class="step.options.length === 3 ? 'grid-cols-3' : 'grid-cols-2'">
                            <template x-for="opt in step.options" :key="opt.value">
                                <button type="button" @click="pick(opt)"
                                        class="group flex min-h-[4.5rem] flex-col items-center justify-center gap-2 rounded-xl border px-2 py-3 text-center text-[0.95rem] font-semibold leading-tight text-cream transition hover:border-amber hover:bg-white/15"
                                        :class="isPicked(opt) ? 'border-amber bg-amber/25' : 'border-white/30 bg-white/10'">
                                    <span x-show="opt.icon" class="flex size-11 items-center justify-center rounded-full bg-amber/20 text-amber ring-1 ring-amber/40 transition group-hover:bg-amber/30" aria-hidden="true">
                                        <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" x-html="(window.quizIcons || {})[opt.icon] || ''"></svg>
                                    </span>
                                    <span x-text="opt.label"></span>
                                </button>
                            </template>
                        </div>
                    </template>

                    <template x-if="step.type === 'select'">
                        <div class="mt-4">
                            <label class="sr-only" :for="'quiz-select-' + step.key" x-text="step.question"></label>
                            <select :id="'quiz-select-' + step.key" @change="selectChange($event)" @keydown="selectKey($event)" class="min-h-14 w-full rounded-xl border border-white/30 bg-white/10 px-4 text-base text-cream focus:border-amber focus:outline-none [&>option]:text-ink">
                                <option value="">Choose one</option>
                                <template x-for="opt in step.options" :key="opt.value">
                                    <option :value="opt.value" :selected="isPicked(opt)" x-text="opt.label"></option>
                                </template>
                            </select>
                        </div>
                    </template>

                    <button type="button" x-show="i > 0" @click="back()" class="mt-4 min-h-11 text-sm font-semibold text-white underline underline-offset-4 hover:text-amber">Back</button>
                </div>
            </template>

            {{-- Final step: contact form with the answers attached (light inner panel keeps the shared form readable) --}}
            <template x-if="onContact">
                <div>
                    <p class="text-[0.95rem] font-bold uppercase leading-relaxed tracking-[0.14em] text-white">Where should we send your quote?</p>
                    <details class="mt-3 rounded-xl border border-white/25 bg-white/10 px-3 py-2 text-sm">
                        <summary class="cursor-pointer font-semibold">Your answers</summary>
                        <ul class="mt-2 space-y-1 text-white">
                            <template x-for="row in summary" :key="row.q"><li><span x-text="row.q"></span> <strong class="text-cream" x-text="row.a"></strong></li></template>
                        </ul>
                    </details>
                    <div class="mt-4 rounded-2xl bg-cream p-4 text-ink">
                        <x-enquiry-form form-name="hero-quiz" mode="quiz" id-prefix="q" submit-label="Get my free quote" />
                    </div>
                    <button type="button" @click="back()" class="mt-3 min-h-11 text-sm font-semibold text-white underline underline-offset-4 hover:text-amber">Back</button>
                </div>
            </template>
        </div>

        <div class="no-js-only">
            <p class="mt-3 text-white">Tell us about your trip and we will send a free quote.</p>
            <div class="mt-4 rounded-2xl bg-cream p-4 text-ink"><x-enquiry-form form-name="hero-quiz" id-prefix="ns" :extended="true" :tour-type="$quizTourType ?? null" submit-label="Get my free quote" /></div>
        </div>

        <p class="mt-6 flex items-center justify-center gap-2 text-center text-sm text-white">
            @icon('lock', 'size-4') Takes less than 30 seconds. Your details stay private.
        </p>
    </div>
</div>
