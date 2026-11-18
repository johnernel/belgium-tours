{{-- Quiz card for the "Homepage Test" hero. Same steps, behaviour and form as partials/quiz.blade.php (Alpine `quiz`), different layout:
     a solid charcoal title band, a white body with the question centred and light option tiles, and a charcoal footer band with reassurance points. --}}
<div class="overflow-hidden bg-white text-ink shadow-lift" id="quiz">
    <div class="bg-ink px-6 py-6 text-center sm:px-8">
        <h2 id="quiz-title" class="on-dark font-serif text-2xl font-normal leading-snug text-cream sm:text-[1.9rem]">@heading('Belgium, Tailored to You in 30 Seconds')</h2>
    </div>

    <div class="min-h-[19rem] p-5 sm:p-7">
        <div x-cloak x-data="quiz(@js($quizSteps), @js($quizPreset ?? []), @js($quizTourType ?? ''))" x-ref="card" tabindex="-1" role="group" aria-labelledby="quiz-title" class="outline-none"
             x-init="window.quizIcons = @js(\App\Support\Icons::all())">
            <template x-if="!onContact">
                <div class="text-center">
                    <p class="font-serif text-xl leading-snug text-ink sm:text-2xl" x-text="step.question" aria-live="polite"></p>

                    <template x-if="step.type === 'tiles'">
                        <div class="mt-5 grid gap-3" :class="step.options.length === 3 ? 'grid-cols-3' : 'grid-cols-2'">
                            <template x-for="opt in step.options" :key="opt.value">
                                <button type="button" @click="pick(opt)"
                                        class="group flex min-h-[5.5rem] flex-col items-center justify-center gap-2 border-2 px-2 py-3 text-center text-[0.95rem] font-semibold leading-tight text-ink transition hover:border-amber hover:shadow-card"
                                        :class="isPicked(opt) ? 'border-amber bg-amber/15' : 'border-ink/15 bg-white'">
                                    <span x-show="opt.icon" class="flex size-11 items-center justify-center rounded-full bg-amber/20 text-amber-ink transition group-hover:bg-amber/35" aria-hidden="true">
                                        <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" x-html="(window.quizIcons || {})[opt.icon] || ''"></svg>
                                    </span>
                                    <span x-text="opt.label"></span>
                                </button>
                            </template>
                        </div>
                    </template>

                    <template x-if="step.type === 'select'">
                        <div class="mt-5">
                            <label class="sr-only" :for="'quiz-select-' + step.key" x-text="step.question"></label>
                            <select :id="'quiz-select-' + step.key" @change="selectChange($event)" @keydown="selectKey($event)" class="min-h-14 w-full border-2 border-ink/15 bg-white px-4 text-base text-ink focus:border-amber focus:outline-none">
                                <option value="">Choose one</option>
                                <template x-for="opt in step.options" :key="opt.value">
                                    <option :value="opt.value" :selected="isPicked(opt)" x-text="opt.label"></option>
                                </template>
                            </select>
                        </div>
                    </template>

                    <button type="button" x-show="i > 0" @click="back()" class="mt-4 min-h-11 text-sm font-semibold text-slate underline underline-offset-4 hover:text-ink">Back</button>
                </div>
            </template>

            <template x-if="onContact">
                <div>
                    <p class="text-center font-serif text-xl text-ink sm:text-2xl">Where should we send your quote?</p>
                    <details class="mt-3 border border-ink/15 bg-cream/60 px-3 py-2 text-sm">
                        <summary class="cursor-pointer font-semibold">Your answers</summary>
                        <ul class="mt-2 space-y-1 text-slate">
                            <template x-for="row in summary" :key="row.q"><li><span x-text="row.q"></span> <strong class="text-ink" x-text="row.a"></strong></li></template>
                        </ul>
                    </details>
                    <div class="mt-4 bg-cream p-4 text-ink [&_button]:!rounded-none [&_input]:!rounded-none [&_select]:!rounded-none [&_textarea]:!rounded-none">
                        <x-enquiry-form form-name="hero-quiz" mode="quiz" id-prefix="q" :package="$quizPackage ?? null" submit-label="Get my free quote" />
                    </div>
                    <button type="button" @click="back()" class="mt-3 min-h-11 text-sm font-semibold text-slate underline underline-offset-4 hover:text-ink">Back</button>
                </div>
            </template>
        </div>

        <div class="no-js-only">
            <p class="mt-3">Tell us about your trip and we will send a free quote.</p>
            <div class="mt-4 bg-cream p-4 text-ink [&_button]:!rounded-none [&_input]:!rounded-none [&_select]:!rounded-none [&_textarea]:!rounded-none"><x-enquiry-form form-name="hero-quiz" id-prefix="ns" :extended="true" :tour-type="$quizTourType ?? null" :package="$quizPackage ?? null" submit-label="Get my free quote" /></div>
        </div>
    </div>

    {{-- Footer band: reassurance points --}}
    <div class="bg-ink px-5 py-4 text-center text-cream sm:px-7">
        <ul class="flex flex-wrap items-center justify-center gap-x-6 gap-y-1 text-sm">
            <li class="inline-flex items-center gap-2">@icon('lock', 'size-4 text-amber') Your details stay private</li>
            <li class="inline-flex items-center gap-2">@icon('circle-check', 'size-4 text-amber') Free, no obligation</li>
        </ul>
        <p class="mt-1.5 text-xs text-cream/80">Takes less than 30 seconds.</p>
    </div>
</div>
