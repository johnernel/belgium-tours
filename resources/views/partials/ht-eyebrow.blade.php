{{-- Eyebrow: small uppercase label with a three-dash accent underneath (amber, aqua, slate). Include with: label, center (bool), dark (bool), mobileCenter (bool: centred only below md). --}}
<p class="flex flex-col gap-2 {{ ($center ?? false) ? 'items-center text-center' : 'items-start' }} {{ ($mobileCenter ?? false) ? 'max-md:items-center max-md:text-center' : '' }} text-xs font-semibold uppercase tracking-[0.22em] {{ ($dark ?? false) ? 'text-cream/85' : 'text-slate' }}">
    <span>{{ $label }}</span>
    <span class="flex gap-1.5" aria-hidden="true"><span class="h-0.5 w-6 bg-amber"></span><span class="h-0.5 w-6 bg-aqua"></span><span class="h-0.5 w-6 bg-slate"></span></span>
</p>
