<?php

namespace Database\Seeders;

use App\Models\Destination;
use App\Models\Faq;
use App\Models\Page;
use App\Models\Testimonial;
use App\Models\TourType;
use Illuminate\Database\Seeder;

class ContentSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([['Belgium', 'belgium'], ['Belgium and the Netherlands', 'belgium-and-netherlands'], ['Tailor-made Europe', 'tailor-made-europe']] as $i => [$name, $slug]) {
            Destination::firstOrCreate(['slug' => $slug], ['name' => $name, 'sort' => $i + 1]);
        }

        $types = [
            ['Self Drive', 'self-drive', 'Your own pace, your own route. We book the hotels, plan the roads and send you off with everything in hand.',
                'Self drive Belgium, at your own pace',
                'A rental car, hotels and a route planned for you. Stop where you like and stay as long as you like, with everything arranged before you go.',
                'Self Drive Tours of Belgium | Go Belgium Tours',
                'Plan a self drive vacation in Belgium: hotels, routes and a rental car arranged for you. Explore Bruges, Ghent and Brussels at your own pace. Free quote.'],
            ['Chauffeur Driven', 'chauffeur-driven', 'Sit back while a private driver-guide handles the roads, the parking and the stories behind each stop.',
                'Belgium with a private driver-guide',
                'Sit back while your driver handles the roads and the parking, and tells you the stories behind each stop. Private, flexible and planned around your group.',
                'Chauffeur Driven Tours of Belgium | Go Belgium Tours',
                'See Belgium with a private driver-guide: no driving, no parking, just the best of Bruges, Ghent, Brussels and the Ardennes. Request a free quote.'],
            ['Coach Tours', 'coach-tours', 'Small-group touring with a guide, ready-made company and no driving. Simple to book, easy to enjoy.',
                'Belgium by small-group coach',
                'Join a guided small-group tour with hotels and transport organized. No driving and no planning, with easy company along the way.',
                'Small-Group Coach Tours of Belgium | Go Belgium Tours',
                'Guided small-group coach tours of Belgium with hotels and transport organized. No driving, no planning. Get a free quote.'],
        ];
        $tabLabels = ['self-drive' => 'Self-Drive', 'chauffeur-driven' => 'Chauffeur', 'coach-tours' => 'Coach Tour'];
        $images = [
            'self-drive' => ['images/photos/countryside', 'A quiet tree-lined road through green Belgian farmland'],
            'chauffeur-driven' => ['images/photos/la-roche', 'A river town in the wooded hills of the Belgian Ardennes'],
            'coach-tours' => ['images/photos/ghent-graslei', 'Visitors relaxing beside the canal at Graslei in Ghent'],
        ];
        $menuTexts = ['self-drive' => 'Your own pace, your own route.', 'chauffeur-driven' => 'A private driver-guide at the wheel.', 'coach-tours' => 'Small-group touring, no driving.'];
        foreach ($types as $i => [$name, $slug, $summary, $headline, $intro, $seoTitle, $metaDescription]) {
            TourType::firstOrCreate(['slug' => $slug], [
                'name' => $name, 'tab_label' => $tabLabels[$slug] ?? null, 'summary' => $summary, 'menu_text' => $menuTexts[$slug] ?? null, 'sort' => $i + 1,
                'headline' => $headline, 'intro' => $intro, 'seo_title' => $seoTitle, 'meta_description' => $metaDescription,
                'image' => $images[$slug][0], 'image_alt' => $images[$slug][1],
            ]);
        }

        $this->pages();
        $this->testimonials();
        $this->posts();
        $this->faqs();
    }

    private function pages(): void
    {
        $company = '[Company Name]'; // replaced at render time by the legal company name setting

        $pages = [
            'home' => [
                'title' => 'Belgium Tours & Tailor-Made European Vacations',
                'seo_title' => 'Belgium Tours: Self Drive, Chauffeur Driven & Coach | Go Belgium Tours',
                'meta' => 'Plan your Belgium vacation with local experts. Self drive, chauffeur driven and coach tours of Bruges, Ghent, Brussels and beyond. Free quote in minutes.',
                'content' => [
                    'hero_tagline' => 'Belgium tours, tailor-made',
                    'marquee_1' => 'Free, no-obligation quote',
                    'marquee_2' => 'Tailor-made itineraries',
                    'marquee_3' => 'Self drive, chauffeur driven and coach tours',
                    'marquee_4' => 'Hotels, routes and transfers planned for you',
                    'hero_h1' => 'Belgium, planned around you',
                    'hero_sub' => 'Self drive, chauffeur driven or small-group coach tours of Bruges, Ghent, Brussels, Antwerp and the Ardennes. Tell us what you like and get a free quote.',
                    'why_eyebrow' => 'Tailor-made in Belgium',
                    'why_heading' => 'Every journey, personally crafted.',
                    'why_sub' => 'We are a small Bruges studio of six. We plan a handful of journeys each month, so every morning, every table and every guide is chosen for you rather than for a group.',
                    'why_1_title' => 'Bespoke itineraries',
                    'why_1_text' => 'Built from a conversation, not a catalog.',
                    'why_2_title' => 'Handpicked stays',
                    'why_2_text' => 'Small hotels, townhouses and country houses we know by name.',
                    'why_3_title' => 'Local expert guides',
                    'why_3_text' => 'Historians, chocolatiers and cyclists who live the route.',
                    'why_cta' => 'Plan My Journey',
                    'journey_title_1' => 'The Journey',
                    'journey_title_2' => 'Never Ends',
                    'journey_text' => 'From hidden canals to medieval towns, every journey adds a new chapter to your story.',
                    'journey_cta' => 'Plan my journey now',
                    'blog_eyebrow' => 'The Journal',
                    'blog_heading' => 'Stories from the *Lowlands.*',
                    'blog_all' => 'All articles',
                    'gal_eyebrow' => 'From the road',
                    'gal_heading' => 'Belgium in *pictures*',
                    'gal_text' => 'A few glimpses of the places on our routes, from canal-side Bruges to the green hills of the Ardennes.',
                    'gal_cta' => 'Start planning',
                    'qb_heading' => 'Belgium, *your way*',
                    'qb_sub' => 'From medieval canals to fairytale towns.',
                    'qb_text' => 'Tell us how you like to travel and we will shape the journey around you.',
                    'qb_styles' => 'Self drive, Private chauffeur, Coach tours',
                    'qb_cta' => 'Plan my trip',
                    'qb_cta2' => 'See Packages',
                    'wcu_eyebrow' => 'Why travel with us',
                    'wcu_heading' => 'Quietly *exceptional* service.',
                    'wcu_sub' => 'The details you notice are the ones we plan for. The rest simply never reaches you.',
                    'wcu_1_title' => 'Local expertise',
                    'wcu_1_text' => 'Routes and stops chosen by people who know Belgium well.',
                    'wcu_2_title' => 'Tailor-made',
                    'wcu_2_text' => 'Every day built around your pace, palate and curiosities.',
                    'wcu_3_title' => 'Handpicked stays',
                    'wcu_3_text' => 'Small hotels and townhouses chosen with care.',
                    'wcu_4_title' => 'Safe & seamless',
                    'wcu_4_text' => 'One planner, one number, from first idea to last train.',
                    // Figures stay blank until the client confirms real numbers: the stats band and badge only show when filled.
                    'wcu_stat_1_value' => '', 'wcu_stat_1_label' => '',
                    'wcu_stat_2_value' => '', 'wcu_stat_2_label' => '',
                    'wcu_stat_3_value' => '', 'wcu_stat_3_label' => '',
                    'wcu_stat_4_value' => '', 'wcu_stat_4_label' => '',
                    'wcu_badge_value' => '', 'wcu_badge_label' => '',
                    'way_eyebrow' => 'Pick your pace',
                    'way_heading' => 'Travel *Your Way*',
                    'dest_eyebrow' => 'Where we go',
                    'dest_heading' => 'Six places worth *slowing down* for.',
                    'dest_cta' => 'View All Destinations',
                    'ways_eyebrow' => 'Signature Journeys',
                    'ways_heading' => 'Three Ways to *Travel* Belgium',
                    'ways_sub' => 'Choose how you move. We handle everything else.',
                    'steps_eyebrow' => '3 easy steps',
                    'steps_heading' => 'How it works',
                    'steps_sub' => 'Tell us what you have in mind, and we take care of the rest.',
                    'steps_cta' => 'Get my free quote',
                    'step_1_title' => 'Tell us your plans',
                    'step_1_text' => 'Answer a few quick questions or send us a message. It takes under a minute.',
                    'step_2_title' => 'Get your quote',
                    'step_2_text' => 'We build a trip around your dates, group and budget and send it to you, free.',
                    'step_3_title' => 'Book and go',
                    'step_3_text' => 'Happy with it? We confirm everything and you travel with your documents ready.',
                    'testimonials_eyebrow' => 'Kind words',
                    'testimonials_heading' => 'What travelers say',
                    'faq_eyebrow' => 'FAQ',
                    'faq_heading' => 'Questions, answered',
                    'faq_card_title' => 'Still have questions?',
                    'faq_card_text' => 'We are here to help with any queries. Reach out and get clear answers directly from our team.',
                    'faq_call' => 'Call us',
                    'faq_cta' => 'Get Quote Now',
                    'cta_heading' => 'Ready to see Belgium?',
                    'cta_text' => 'Tell us when you want to go and who is coming. We will send a free quote.',
                    'badge_1' => 'Best price promise [CONFIRM: wording]',
                    'badge_2' => 'Belgium specialists',
                    'badge_3' => 'Reviews [CONFIRM: review source]',
                ],
            ],
            'about' => [
                'title' => 'About Us',
                'seo_title' => 'About Go Belgium Tours',
                'meta' => 'Meet the team behind Go Belgium Tours and see how we plan self drive, chauffeur driven and coach tours of Belgium.',
                'content' => ['hero_sub' => 'How we plan self drive, chauffeur driven and coach tours of Belgium.'],
                'body' => '<p>Go Belgium Tours plans vacations in Belgium and tailor-made trips across Europe. We offer three ways to travel: self drive, chauffeur driven and small-group coach tours.</p><p>[CONFIRM: company story, team, years in business and any accreditations. Replace this paragraph with real details.]</p><h2>What we do</h2><p>We listen to how you like to travel, then build the trip: hotels, routes, transfers and the extras that make it feel local. You get one quote and one point of contact.</p>',
            ],
            'contact' => [
                'title' => 'Contact Us',
                'seo_title' => 'Contact Go Belgium Tours',
                'meta' => 'Ask a question or request a free Belgium tour quote. Send your details and we will get back to you.',
                'content' => ['hero_sub' => 'Ask a question or request a free quote. We reply as soon as we can.'],
                'body' => '<p>Questions about a trip or ready for a quote? Send us a message and we will reply as soon as we can.</p>',
            ],
            'destinations' => [
                'title' => 'Destinations',
                'seo_title' => 'Belgium Destinations: Bruges, Ghent, Brussels and More | Go Belgium Tours',
                'meta' => 'Bruges, Ghent, Brussels, Antwerp, Leuven and the Ardennes. Pick a place and see the Belgium tours that go there.',
                'content' => [
                    'hero_headline' => 'Destinations worth *slowing down* for',
                    'hero_sub' => 'Six places to build a Belgium trip around. Pick one and we will show you the tours that go there.',
                ],
            ],
            'terms' => [
                'title' => 'Terms and Conditions',
                'seo_title' => 'Terms and Conditions',
                'meta' => 'The terms and conditions that apply when you inquire or book with Go Belgium Tours.',
                'body' => "<p><strong>Starter template only. Have it reviewed by a qualified professional before launch.</strong></p><h2>1. About these terms</h2><p>These terms apply to inquiries and bookings made with $company. By sending an inquiry you agree to them.</p><h2>2. Inquiries and quotes</h2><p>A quote is not a booking. Prices and availability are confirmed in writing and may change until you pay a deposit. [CONFIRM: quote validity period]</p><h2>3. Payments and cancellations</h2><p>[CONFIRM: deposit, final payment and cancellation terms]</p><h2>4. SMS and email messages</h2><p>If you give us your phone number and agree, we may send SMS messages about your inquiry, booking updates, itinerary updates, reminders and customer support. Message and data rates may apply. Reply STOP to unsubscribe or HELP for assistance. Consent is not a condition of purchase.</p><h2>5. Liability</h2><p>[CONFIRM: liability, force majeure and insurance wording]</p><h2>6. Contact</h2><p>Questions about these terms? Use the details on our contact page.</p>",
            ],
            'privacy' => [
                'title' => 'Privacy Policy',
                'seo_title' => 'Privacy Policy',
                'meta' => 'How Go Belgium Tours collects, uses and protects your personal information.',
                'body' => "<p><strong>Starter template only. Have it reviewed by a qualified professional before launch.</strong></p><h2>What we collect</h2><p>When you send an inquiry, $company collects your name, email address, phone number, travel preferences and anything you write to us. We also record technical information such as your IP address, browser, the page you used and the advertising or referral source that brought you here.</p><h2>Why we collect it</h2><p>To reply to your inquiry, prepare quotes, manage bookings, send the messages you agreed to, and measure our advertising.</p><h2>Who we share it with</h2><p>Service providers that help us run the business, such as our email, customer-relationship and analytics tools. [CONFIRM: full list of providers and countries]</p><h2>Your choices</h2><p>You can ask us to access, correct or delete your information, or to stop contacting you, at any time. Reply STOP to any SMS to unsubscribe.</p><h2>Cookies</h2><p>We use cookies to remember how you arrived on the site and, if enabled, for analytics and advertising measurement. [CONFIRM: cookie banner requirement for target markets]</p><h2>Contact</h2><p>[CONFIRM: privacy contact email and postal address]</p>",
            ],
        ];

        foreach ($pages as $slug => $p) {
            Page::firstOrCreate(['slug' => $slug], [
                'title' => $p['title'],
                'seo_title' => $p['seo_title'],
                'meta_description' => $p['meta'],
                'body' => $p['body'] ?? null,
                'content' => $p['content'] ?? null,
            ]);
        }
    }

    private function testimonials(): void
    {
        if (Testimonial::exists()) {
            return;
        }
        foreach ([1, 2, 3] as $i) {
            Testimonial::create([
                'name' => '[PLACEHOLDER: traveler name]',
                'location' => '[PLACEHOLDER: home town]',
                'quote' => '[PLACEHOLDER REVIEW '.$i.': replace with a real, permitted customer review before launch.]',
                'is_placeholder' => true,
                'sort' => $i,
            ]);
        }
    }

    /** Sample Journal articles: general, non-factual wording, flagged as placeholders until real ones are written. */
    private function posts(): void
    {
        if (\App\Models\Post::exists()) {
            return;
        }
        $note = '<p><em>[CONFIRM: this is a sample article. Replace it with a real story before launch.]</em></p>';
        $posts = [
            ['A morning in Bruges, before the boats', 'City notes', 3, 'images/photos/bruges-canal', 'Brick houses beside a canal in Bruges',
                'Why an early walk along the canals is the gentlest way to meet the city, and where to start.',
                '<p>Bruges is at its quietest early in the day. Walk the canals while the streets are still empty and the water is still, and the old brick houses look just as they do on the postcards.</p><p>Start at the edge of the old town and let the canals lead you toward the squares. There is no need for a plan: the best views tend to be the ones you stumble on.</p>'],
            ['Slow days in the Ardennes', 'Countryside', 4, 'images/photos/la-roche', 'A river town in the wooded hills of the Belgian Ardennes',
                'Wooded valleys, river towns and the unhurried pace of the Belgian countryside, away from the cities.',
                '<p>South of the big cities, Belgium changes. Roads wind through wooded valleys and small towns sit beside the river, and a day here moves at a different speed.</p><p>It is a region that suits a car or a private driver best, so you can stop wherever a view or a village catches your eye.</p>'],
            ['The Grand Place, from morning to twilight', 'Brussels', 4, 'images/photos/brussels-grand-place', 'The Grand Place in Brussels at twilight',
                'How the main square of Brussels changes through the day, and when it is at its best.',
                '<p>The Grand Place is a square worth seeing more than once. In the morning it is calm and bright, and as evening falls the carved fronts are lit and the square fills with quiet conversation.</p><p>Give it two visits if you can: once early, once at dusk.</p>'],
            ['Ghent on foot: quays, castle and a long evening', 'City notes', 5, 'images/photos/ghent-graslei', 'Visitors relaxing beside the canal at Graslei in Ghent',
                'A relaxed walking day in Ghent, from the old quays to the castle and a late dinner.',
                '<p>Ghent rewards walking. The old quays along the canal, the castle and the narrow streets between them are all close together, so you can see a lot without hurrying.</p><p>Finish on the quays in the evening, when the buildings are lit and the water reflects them.</p>'],
        ];
        foreach ($posts as $i => [$title, $category, $minutes, $image, $alt, $excerpt, $body]) {
            \App\Models\Post::create([
                'title' => $title, 'slug' => \Illuminate\Support\Str::slug($title), 'category' => $category, 'read_minutes' => $minutes,
                'image' => $image, 'image_alt' => $alt, 'excerpt' => $excerpt, 'body' => $body.$note,
                'published_at' => now()->subDays($i + 1), 'is_placeholder' => true, 'active' => true,
            ]);
        }
    }

    private function faqs(): void
    {
        if (Faq::exists()) {
            return;
        }
        $faqs = [
            ['How does the free quote work?', 'Answer a few quick questions or fill in the short form. We build a trip around your answers and send you a quote with no obligation to book.'],
            ['What is the difference between self drive, chauffeur driven and coach tours?', 'Self drive gives you a rental car and a planned route. Chauffeur driven adds a private driver-guide. Coach tours are small-group trips with a guide, where you do not drive.'],
            ['Can you change an itinerary to suit us?', 'Yes. Every sample trip on this site is a starting point. Tell us your dates, pace and interests and we will adjust it.'],
            ['When is the best time to visit Belgium?', 'Spring and early fall are popular for mild weather and fewer crowds. Summer is lively and winter has Christmas markets. We can advise on your dates.'],
            ['What is included in the price?', 'Each quote lists exactly what is included, such as hotels, transfers and tickets. [CONFIRM: standard inclusions and exclusions]'],
            ['Do I need to book flights first?', 'No. You can request a quote before booking flights. We will help you time your arrival and departure around the itinerary.'],
        ];
        foreach ($faqs as $i => [$q, $a]) {
            Faq::create(['question' => $q, 'answer' => $a, 'sort' => $i + 1]);
        }
    }
}
