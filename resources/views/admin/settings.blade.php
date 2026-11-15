@extends('layouts.admin')
@section('title', 'Settings')
@section('heading', 'Site settings')

@section('content')
<form method="POST" enctype="multipart/form-data" action="{{ route('admin.settings.update') }}" class="max-w-3xl space-y-6">
    @csrf @method('PUT')

    <section class="space-y-4 rounded-2xl bg-paper p-6 shadow-card">
        <h2 class="text-xl">Brand</h2>
        <x-admin.input name="brand_name" label="Brand name" :value="$s['brand_name'] ?? ''" :required="true" />
        <x-admin.input name="legal_company_name" label="Legal company name" :value="$s['legal_company_name'] ?? ''" :required="true" help="Used in the consent wording on every form and in the legal pages." />
        <x-admin.input name="tagline" label="Tagline" :value="$s['tagline'] ?? ''" />
        <div class="grid gap-4 sm:grid-cols-2">
            <div class="space-y-1"><p class="text-sm font-semibold">Logo (PNG, JPG or WebP)</p>
                @if(!empty($s['logo']))<img src="{{ asset($s['logo']) }}" alt="Current logo" class="h-12"><label class="text-sm"><input type="checkbox" name="logo_remove" value="1" class="accent-ink"> Remove</label>@endif
                <input type="file" name="logo" accept="image/png,image/jpeg,image/webp" class="block text-sm"></div>
            <div class="space-y-1"><p class="text-sm font-semibold">Favicon (PNG or ICO, square)</p>
                @if(!empty($s['favicon']))<img src="{{ asset($s['favicon']) }}" alt="Current favicon" class="size-8"><label class="text-sm"><input type="checkbox" name="favicon_remove" value="1" class="accent-ink"> Remove</label>@endif
                <input type="file" name="favicon" accept="image/png,image/x-icon" class="block text-sm"></div>
        </div>
    </section>

    <section class="space-y-4 rounded-2xl bg-paper p-6 shadow-card">
        <h2 class="text-xl">Contact details</h2>
        <p class="text-sm text-slate">Anything left blank is hidden on the site.</p>
        <div class="grid gap-4 sm:grid-cols-2">
            <x-admin.input name="phone" label="Phone" :value="$s['phone'] ?? ''" help="Shown as a tap-to-call link. Include the country code." />
            <x-admin.input name="email" type="email" label="Email" :value="$s['email'] ?? ''" />
        </div>
        <x-admin.textarea name="address" label="Address" :value="$s['address'] ?? ''" :rows="2" />
        <div class="grid gap-4 sm:grid-cols-2">
            <x-admin.input name="social_facebook" type="url" label="Facebook page URL" :value="$s['social_facebook'] ?? ''" />
            <x-admin.input name="social_instagram" type="url" label="Instagram URL" :value="$s['social_instagram'] ?? ''" />
        </div>
        <x-admin.textarea name="footer_text" label="Footer text" :value="$s['footer_text'] ?? ''" :rows="2" />
    </section>

    <section class="space-y-4 rounded-2xl bg-paper p-6 shadow-card">
        <h2 class="text-xl">Enquiries and tracking</h2>
        <x-admin.input name="notification_emails" label="Send new-enquiry emails to" :value="$s['notification_emails'] ?? ''" help="Separate several addresses with commas." />
        <x-admin.input name="quote_discount_text" label="Quote popup wording" :value="$s['quote_discount_text'] ?? ''" help='Completes "Fill out this form and ...". Only promise a discount you really offer.' />
        <x-admin.input name="gtm_id" label="Google Tag Manager ID" :value="$s['gtm_id'] ?? ''" placeholder="GTM-XXXXXXX" help="Leave blank to switch tracking off." />
        <div class="grid gap-4 sm:grid-cols-2">
            <x-admin.input name="recaptcha_site_key" label="reCAPTCHA site key" :value="$s['recaptcha_site_key'] ?? ''" help="Blank uses the key in .env." />
            <x-admin.input name="recaptcha_secret_key" type="password" label="reCAPTCHA secret key" value="" autocomplete="off" :help="empty($s['recaptcha_secret_key']) ? 'Blank uses the key in .env.' : 'A secret is saved. Leave blank to keep it.'" />
        </div>
    </section>

    <section class="space-y-3 rounded-2xl bg-paper p-6 shadow-card">
        <h2 class="text-xl">GoHighLevel</h2>
        <p>Status: <strong class="{{ $ghlOn ? 'text-green-800' : 'text-slate' }}">{{ $ghlOn ? 'On, enquiries sync to your CRM' : 'Off' }}</strong></p>
        <ul class="text-sm text-slate"><li>GHL_API_TOKEN: {{ $tokenSet ? 'set' : 'not set' }}</li><li>GHL_LOCATION_ID: {{ $locationSet ? 'set' : 'not set' }}</li></ul>
        <p class="text-sm text-slate">For security these two values live only in the server's .env file, never here. See docs/reference/GHL_INTEGRATION.md.</p>
    </section>

    <section class="space-y-4 rounded-2xl bg-paper p-6 shadow-card">
        <h2 class="text-xl">Search and sharing defaults</h2>
        <x-admin.textarea name="default_meta_description" label="Default description for Google" :value="$s['default_meta_description'] ?? ''" :rows="2" />
        <div class="space-y-1"><p class="text-sm font-semibold">Default social sharing image</p>
            @if(!empty($s['default_social_image']))<img src="{{ asset($s['default_social_image']) }}" alt="" class="h-20 rounded-lg">@endif
            <input type="file" name="default_social_image" accept="image/jpeg,image/png,image/webp" class="block text-sm"></div>
    </section>

    <x-button type="submit">Save settings</x-button>
</form>
@endsection
