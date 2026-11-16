<?php

namespace App\Providers;

use App\Models\Setting;
use App\Services\Recaptcha;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // @icon('phone', 'size-5 text-amber'): inline SVG without the cost of a component render.
        \Illuminate\Support\Facades\Blade::directive('icon', fn ($expression) => "<?php echo \App\Support\Icons::svg($expression); ?>");

        // @heading('Three Ways to *Travel* Belgium'): two-colour headline (see App\Support\Heading).
        \Illuminate\Support\Facades\Blade::directive('heading', fn ($expression) => "<?php echo \\App\\Support\\Heading::html($expression); ?>");

        // Per-IP limits: enquiries and admin login attempts.
        RateLimiter::for('enquiry', fn (Request $r) => Limit::perMinute(6)->by($r->ip()));
        RateLimiter::for('admin-login', fn (Request $r) => Limit::perMinute(5)->by($r->ip().'|'.strtolower((string) $r->input('email'))));

        // Site-wide values available to every public view as $site.
        View::composer(['layouts.*', 'partials.*', 'components.*', 'pages.*', 'errors.*', 'errors::*'], function ($view) {
            $site = (object) [
                'brand' => Setting::get('brand_name', config('app.name')),
                'company' => Setting::get('legal_company_name', Setting::get('brand_name', config('app.name'))),
                'tagline' => Setting::get('tagline', ''),
                'phone' => Setting::get('phone'),
                'phone_href' => preg_replace('/[^\d+]/', '', (string) Setting::get('phone')),
                'email' => Setting::get('email'),
                'address' => Setting::get('address'),
                'facebook' => Setting::get('social_facebook'),
                'instagram' => Setting::get('social_instagram'),
                'gtm' => Setting::get('gtm_id'),
                'footer_text' => Setting::get('footer_text'),
                'discount' => Setting::get('quote_discount_text', 'save on your trip'),
                'meta_description' => Setting::get('default_meta_description'),
                'logo' => Setting::get('logo'),
                'favicon' => Setting::get('favicon'),
                'social_image' => Setting::get('default_social_image'),
                'recaptcha_key' => Recaptcha::siteKey(),
            ];
            $view->with('site', $site);
            // Error pages (404) render outside the session middleware, so give them an empty error bag.
            if (! array_key_exists('errors', $view->getData())) {
                $view->with('errors', new \Illuminate\Support\ViewErrorBag);
            }
        });
    }
}
