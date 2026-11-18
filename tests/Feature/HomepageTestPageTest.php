<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomepageTestPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_the_design_trial_loads_is_hidden_from_search_and_uses_the_trial_header_and_footer(): void
    {
        $this->get('/homepage-test')->assertOk()
            ->assertSee('noindex', false)
            ->assertSee('Enquire Now')
            ->assertSee('images/logo', false);
        $this->get('/sitemap.xml')->assertDontSee('homepage-test');
    }

    public function test_the_real_home_page_is_unchanged(): void
    {
        $this->get('/')->assertOk()->assertDontSee('Plan Your Belgium Trip', false)->assertDontSee('noindex', false);
    }
}
