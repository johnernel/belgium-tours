@props([])
{{-- Hero quiz card. Steps come from the database (Admin > Quiz). JS-only; <noscript> shows the plain form. --}}
<div class="relative rounded-[1.75rem] border-4 border-amber bg-cream p-5 text-ink shadow-lift sm:p-7" id="quiz">
    <h2 id="quiz-title" class="text-2xl sm:text-[1.75rem]">@heading('Start Your Adventure')</h2>

    <div x-cloak x-data="quiz(@js($quizSteps), @js($quizPreset ?? []), @js($quizTourType ?? ''))" x-ref="card" tabindex="-1" role="group" aria-labelledby="quiz-title" class="mt-3 outline-none"
         x-init="window.quizIcons = @js(\App\Support\Icons::all())">
        <div class="mb-4 flex items-center gap-3" aria-live="polite">
            <div class="h-2 flex-1 overflow-hidden rounded-full bg-ink/10" role="progressbar" aria-valuemin="0" aria-valuemax="100" :aria-valuenow="progress" aria-label="Quiz progress">
                <div class="h-full rounded-full bg-amber transition-all duration-300" :style="`width:${progress}%`"></div>
            </div>
            <span class="text-sm font-semibold text-slate" x-text="`Step ${i + 1} of ${total}`"></span>
        </div>

        {{-- Question steps --}}
        <template x-if="!onContact">
            <div>
                <p class="font-display text-xl font-medium" x-text="step.question" aria-live="polite"></p>

                <template x-if="step.type === 'tiles'">
                    <div class="mt-4 grid gap-3" :class="step.options.length === 2 ? 'grid-cols-2' : 'grid-cols-2'">
                        <template x-for="opt in step.options" :key="opt.value">
                            <button type="button" @click="pick(opt)"
                                    class="flex min-h-[4.25rem] flex-col items-center justify-center gap-1.5 rounded-2xl border-2 bg-white px-3 py-3 text-center font-semibold leading-tight transition hover:border-ink"
                                    :class="isPicked(opt) ? 'border-ink bg-sky-soft' : 'border-slate/25'">
                                <svg x-show="opt.icon" class="size-7 text-slate" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" x-html="(window.quizIcons || {})[opt.icon] || ''"></svg>
                                <span x-text="opt.label"></span>
                            </button>
                        </template>
                    </div>
                </template>

                <template x-if="step.type === 'select'">
                    <div class="mt-4">
                        <label class="sr-only" :for="'quiz-select-' + step.key" x-text="step.question"></label>
                        <select :id="'quiz-select-' + step.key" @change="selectChange($event)" class="min-h-12 w-full rounded-xl border-2 border-slate/30 bg-white px-3.5 text-base focus:border-ink focus:outline-none">
                            <option value="">Choose one</option>
                            <template x-for="opt in step.options" :key="opt.value">
                                <option :value="opt.value" :selected="isPicked(opt)" x-text="opt.label"></option>
                            </template>
                        </select>
                        <x-button class="mt-4 w-full" icon="arrow-right" @click="next()" x-bind:disabled="!answers[step.key]" x-bind:class="!answers[step.key] && 'opacity-50 cursor-not-allowed'">Next</x-button>
                    </div>
                </template>

                <button type="button" x-show="i > 0" @click="back()" class="mt-4 min-h-11 text-sm font-semibold text-slate underline underline-offset-4 hover:text-ink">Back</button>
            </div>
        </template>

        {{-- Final step: contact form with the answers attached --}}
        <template x-if="onContact">
            <div>
                <p class="font-display text-xl font-medium">Where should we send your quote?</p>
                <details class="mt-2 rounded-xl bg-white/70 px-3 py-2 text-sm">
                    <summary class="cursor-pointer font-semibold">Your answers</summary>
                    <ul class="mt-2 space-y-1 text-slate">
                        <template x-for="row in summary" :key="row.q"><li><span x-text="row.q"></span> <strong class="text-ink" x-text="row.a"></strong></li></template>
                    </ul>
                </details>
                <div class="mt-4">
                    <x-enquiry-form form-name="hero-quiz" mode="quiz" id-prefix="q" submit-label="Get my free quote" />
                </div>
                <button type="button" @click="back()" class="mt-3 min-h-11 text-sm font-semibold text-slate underline underline-offset-4 hover:text-ink">Back</button>
            </div>
        </template>
    </div>

    <div class="no-js-only">
        <p class="mt-3 text-slate">Tell us about your trip and we will send a free quote.</p>
        <div class="mt-4"><x-enquiry-form form-name="hero-quiz" id-prefix="ns" :extended="true" :tour-type="$quizTourType ?? null" submit-label="Get my free quote" /></div>
    </div>

    <p class="mt-5 flex items-center gap-2 text-sm text-slate">
        @icon('lock', 'size-4') Takes less than 30 seconds. Your details stay private.
    </p>
</div>
