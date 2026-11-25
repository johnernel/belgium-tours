<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** The Packages page carries the home page's stress-free, payments and testimonials bands, then a FAQ section, under the package cards. */
class PackagesPageBandsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_the_bands_come_after_the_cards_in_the_requested_order_with_the_faqs_last(): void
    {
        $html = $this->get('/packages')->assertOk()->getContent();

        $marks = [
            'package cards' => strrpos($html, 'class="pkg-card group"'),
            'stress-free' => strpos($html, 'aria-labelledby="ht-steps"'),
            'payments' => strpos($html, 'aria-labelledby="ht-pay"'),
            'testimonials' => strpos($html, 'aria-labelledby="ht-quotes"'),
            'faqs' => strpos($html, 'aria-labelledby="pkg-faq-title"'),
            'footer' => strpos($html, '<footer'),
        ];
        foreach ($marks as $name => $pos) {
            $this->assertNotFalse($pos, "$name is on the page");
        }
        $order = $marks;
        asort($order);
        $this->assertSame(array_keys($marks), array_keys($order), 'cards, stress-free, payments, testimonials, FAQs, footer');
    }

    public function test_the_bands_show_the_requested_copy(): void
    {
        $html = $this->get('/packages')->assertOk()->getContent();
        $text = html_entity_decode(preg_replace('~\s+~', ' ', strip_tags($html)));

        foreach ([
            'Stress-free travel',
            'We take care of every detail so you can simply show up, enjoy the canals and cobbled squares, and let Belgium do the rest.',
            'Find your style',
            'Whether you are traveling with friends, family or colleagues, our flexible group payment options make booking your Belgium trip simple, straightforward and completely hassle-free for everyone involved.',
            'Kind words',
            'Stress-Free',
            'Worries Ever',
        ] as $copy) {
            $this->assertStringContainsString($copy, $text);
        }
    }

    public function test_the_faq_section_lists_every_active_question_and_adds_faq_structured_data(): void
    {
        $faqs = \App\Models\Faq::active()->get();
        $this->assertGreaterThan(0, $faqs->count());

        $html = $this->get('/packages')->assertOk()->getContent();

        $this->assertSame($faqs->count(), substr_count($html, 'name="faq-packages"'), 'one accordion item per FAQ');
        $this->assertStringContainsString(e($faqs->first()->question), $html);
        $this->assertStringContainsString('"@type":"FAQPage"', $html);
        $this->assertStringContainsString('Frequently Asked', $html);
    }

    public function test_the_filtered_list_keeps_the_bands_and_the_faqs_page_still_works(): void
    {
        $this->get('/packages?type=self-drive')->assertOk()->assertSee('aria-labelledby="pkg-faq-title"', false);
        $this->get('/faqs')->assertOk()->assertSee('Still have questions?')->assertSee('name="faq-page"', false);
    }
}
