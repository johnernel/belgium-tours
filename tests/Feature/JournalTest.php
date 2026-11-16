<?php

namespace Tests\Feature;

use App\Models\AdminUser;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JournalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_home_has_the_blog_section_after_the_faq_with_three_cards(): void
    {
        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('<em class="heading-accent">Lowlands.</em>', $html);
        $start = strpos($html, 'aria-labelledby="blog-title"');
        $section = substr($html, $start, strpos($html, '</section>', $start) - $start);
        $this->assertSame(3, substr_count($section, '<article'));
        $this->assertStringContainsString('href="'.route('journal.index').'"', $section);
        $first = Post::published()->first();
        $this->assertStringContainsString('href="'.route('journal.show', $first->slug).'"', $section);
        $this->assertStringContainsString('min read', $section);
        $this->assertLessThan($start, strpos($html, 'aria-labelledby="faq-title"'));
        $this->assertStringNotContainsString('aria-labelledby="cta-title"', $html); // the blog is the last section before the footer
    }

    public function test_journal_list_and_article_pages_work_and_sample_articles_are_hidden_from_google(): void
    {
        $post = Post::published()->first();

        $this->get('/journal')->assertOk()->assertSee($post->title)->assertSee('name="robots"', false);
        $this->get('/journal/'.$post->slug)->assertOk()->assertSee($post->title)->assertSee('Sample article', false)->assertSee('noindex', false);
        $this->get('/journal/not-a-real-article')->assertNotFound();

        $sitemap = $this->get('/sitemap.xml')->assertOk()->getContent();
        $this->assertStringContainsString(route('journal.index'), $sitemap);
        $this->assertStringNotContainsString('/journal/'.$post->slug, $sitemap);
    }

    public function test_real_articles_are_indexable_and_in_the_sitemap_but_hidden_or_scheduled_ones_are_not_shown(): void
    {
        $real = Post::create(['title' => 'A real story', 'slug' => 'a-real-story', 'body' => '<p>Hello</p>', 'is_placeholder' => false, 'published_at' => now()->subDay()]);
        Post::create(['title' => 'Hidden story', 'slug' => 'hidden-story', 'is_placeholder' => false, 'active' => false]);
        Post::create(['title' => 'Future story', 'slug' => 'future-story', 'is_placeholder' => false, 'published_at' => now()->addWeek()]);

        $this->get('/journal/a-real-story')->assertOk()->assertDontSee('Sample article', false);
        $this->get('/journal/hidden-story')->assertNotFound();
        $this->get('/journal/future-story')->assertNotFound();
        $this->get('/journal')->assertOk()->assertSee('A real story')->assertDontSee('Hidden story')->assertDontSee('Future story');
        $this->assertStringContainsString('/journal/a-real-story', $this->get('/sitemap.xml')->getContent());
        $this->assertStringContainsString('"BlogPosting"', $this->get('/journal/'.$real->slug)->getContent());
    }

    public function test_admin_can_create_an_article_and_the_url_name_comes_from_the_title(): void
    {
        $this->actingAs(AdminUser::where('email', 'admin@test.example')->firstOrFail());

        $this->post('/admin/posts', ['title' => 'Chocolate in Bruges', 'category' => 'Table', 'excerpt' => 'Short.', 'body' => '<p>Body</p>', 'read_minutes' => 5])->assertRedirect();

        $this->assertDatabaseHas('posts', ['title' => 'Chocolate in Bruges', 'slug' => 'chocolate-in-bruges', 'read_minutes' => 5]);
    }
}
