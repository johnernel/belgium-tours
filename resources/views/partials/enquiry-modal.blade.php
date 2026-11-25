{{-- Shared enquiry modal. Sharp, square corners on everything inside (the [&_*]:!rounded-none on the panel). Open it with $store.modal.open({ mode, tourType, packageId, packageName, heading }). --}}
<div x-cloak x-show="$store.modal.show" class="fixed inset-0 z-[90] flex items-end justify-center sm:items-center sm:p-6"
     role="dialog" aria-modal="true" aria-labelledby="modal-title" @keydown.escape.window="$store.modal.close()">
    <div class="absolute inset-0 bg-ink/70" x-show="$store.modal.show" x-transition.opacity @click="$store.modal.close()"></div>
    <div class="relative max-h-[94vh] w-full overflow-y-auto bg-cream p-5 shadow-lift sm:max-w-xl sm:p-8 [&_*]:!rounded-none"
         x-show="$store.modal.show" x-transition:enter="transition duration-200" x-transition:enter-start="translate-y-6 opacity-0" x-transition:enter-end="translate-y-0 opacity-100"
         x-trap.noscroll.inert="$store.modal.show">
        <button type="button" class="absolute right-3 top-3 grid size-11 place-items-center hover:bg-ink/10" @click="$store.modal.close()" aria-label="Close">
            @icon('x')
        </button>
        <h2 id="modal-title" class="pr-10 text-2xl sm:text-3xl"
            x-text="$store.modal.heading || ($store.modal.mode === 'question' ? 'Ask us a question' : 'Fill out this form and {{ $site->discount }}')">Fill out this form and {{ $site->discount }}</h2>
        <p class="mt-1 text-slate" x-show="$store.modal.packageName" x-text="'About: ' + $store.modal.packageName"></p>
        <p class="mb-5 mt-1 text-slate" x-show="!$store.modal.packageName">Tell us a little about your trip. A travel specialist replies with a free quote.</p>
        <div class="mt-4">
            <x-enquiry-form form-name="modal" mode="modal" :extended="true" id-prefix="m" submit-label="Send my request" />
        </div>
    </div>
</div>
