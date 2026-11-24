{{-- Eyebrow: small uppercase label with a three-dash accent underneath (amber, aqua, slate). Include with: label, center (bool), dark (bool), mobileCenter (bool: centred only below md). --}}
<p class="flex flex-col gap-2 {{ ($center ?? false) ? 'items-center text-center' : 'items-start' }} {{ ($mobileCenter ?? false) ? 'max-md:items-center max-md:text-center' : '' }} text-xs font-semibold uppercase tracking-[0.22em] {{ ($dark ?? false) ? 'text-cream/85' : 'text-slate' }}">
    <span>{{ $label }}</span>
    <span class="flex items-center gap-2" aria-hidden="true"><span class="h-px w-8 bg-gradient-to-r from-transparent {{ ($dark ?? false) ? 'to-cream/60' : 'to-slate/50' }}"></span><span class="size-2 rotate-45 bg-amber"></span><span class="h-px w-8 bg-gradient-to-l from-transparent {{ ($dark ?? false) ? 'to-cream/60' : 'to-slate/50' }}"></span></span>
</p>
