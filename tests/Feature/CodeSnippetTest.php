<?php

namespace Tests\Feature;

use App\Models\AdminUser;
use App\Models\CodeSnippet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CodeSnippetTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    private function snippet(array $attrs = []): CodeSnippet
    {
        return CodeSnippet::create($attrs + ['name' => 'Test', 'type' => 'html', 'placement' => 'head', 'code' => '<meta name="x-test" content="1">', 'scope' => 'all', 'pages' => [], 'sort' => 1, 'is_active' => true]);
    }

    private function admin(): void
    {
        $this->actingAs(AdminUser::where('email', 'admin@test.example')->firstOrFail());
    }

    public function test_code_is_output_exactly_as_entered_in_each_placement(): void
    {
        $this->snippet(['name' => 'H', 'placement' => 'head', 'code' => '<meta name="head-snippet" content="a &amp; b">']);
        $this->snippet(['name' => 'S', 'placement' => 'body_start', 'code' => '<noscript><img src="https://px.example/a.gif?x=1&y=2" alt=""></noscript>']);
        $this->snippet(['name' => 'E', 'placement' => 'body_end', 'code' => '<div id="widget-end" data-q=\'"quoted"\'></div>']);

        $html = $this->get('/contact')->assertOk()->getContent();

        $this->assertStringContainsString('<meta name="head-snippet" content="a &amp; b">', $html);
        $this->assertStringContainsString('<noscript><img src="https://px.example/a.gif?x=1&y=2" alt=""></noscript>', $html);
        $this->assertStringContainsString('<div id="widget-end" data-q=\'"quoted"\'></div>', $html);

        $head = strpos($html, 'head-snippet');
        $this->assertLessThan(strpos($html, '</head>'), $head, 'head snippet is inside the head');
        $bodyOpen = strpos($html, '<body');
        $startPos = strpos($html, 'px.example/a.gif');
        $this->assertGreaterThan($bodyOpen, $startPos);
        $this->assertLessThan(strpos($html, 'id="main"'), $startPos, 'body-start snippet comes before the page content');
        $endPos = strpos($html, 'widget-end');
        $this->assertGreaterThan(strpos($html, '</main>'), $endPos, 'body-end snippet comes after the page content');
        $this->assertLessThan(strpos($html, '</body>'), $endPos);
    }

    public function test_bare_code_is_wrapped_but_code_that_starts_with_a_tag_is_left_alone(): void
    {
        $this->snippet(['name' => 'JS', 'type' => 'script', 'code' => "window.__bare = 'yes';"]);
        $this->snippet(['name' => 'CSS', 'type' => 'style', 'code' => '.bare-style { color: red; }']);
        $this->snippet(['name' => 'Tag', 'type' => 'script', 'code' => '<script src="https://cdn.example/lib.js" async></script>']);

        $html = $this->get('/contact')->getContent();

        $this->assertStringContainsString("<script>\nwindow.__bare = 'yes';\n</script>", $html);
        $this->assertStringContainsString("<style>\n.bare-style { color: red; }\n</style>", $html);
        $this->assertSame(1, substr_count($html, '<script src="https://cdn.example/lib.js" async></script>'), 'used exactly as pasted, not wrapped again');
    }

    public function test_disabled_snippets_are_skipped_and_order_decides_who_loads_first(): void
    {
        $this->snippet(['name' => 'Off', 'is_active' => false, 'code' => '<meta name="off-snippet">']);
        $this->snippet(['name' => 'Second', 'sort' => 5, 'code' => '<meta name="second-snippet">']);
        $this->snippet(['name' => 'First', 'sort' => 2, 'code' => '<meta name="first-snippet">']);

        $html = $this->get('/contact')->getContent();

        $this->assertStringNotContainsString('off-snippet', $html);
        $this->assertLessThan(strpos($html, 'second-snippet'), strpos($html, 'first-snippet'));
    }

    public function test_only_these_pages_and_everywhere_except_scopes_with_wildcards(): void
    {
        $this->snippet(['name' => 'Only', 'scope' => 'only', 'pages' => ['/contact', '/packages/*'], 'code' => '<meta name="only-snippet">']);
        $this->snippet(['name' => 'Except', 'scope' => 'except', 'pages' => ['/', '/contact'], 'code' => '<meta name="except-snippet">']);

        $slug = \App\Models\Package::where('is_published', true)->value('slug');

        $contact = $this->get('/contact')->getContent();
        $this->assertStringContainsString('only-snippet', $contact);
        $this->assertStringNotContainsString('except-snippet', $contact);

        $home = $this->get('/')->getContent();
        $this->assertStringNotContainsString('only-snippet', $home);
        $this->assertStringNotContainsString('except-snippet', $home);

        $package = $this->get("/packages/$slug")->getContent();
        $this->assertStringContainsString('only-snippet', $package, 'wildcard matches package pages');
        $this->assertStringContainsString('except-snippet', $package);

        $about = $this->get('/about')->getContent();
        $this->assertStringNotContainsString('only-snippet', $about);
        $this->assertStringContainsString('except-snippet', $about);
    }

    public function test_page_addresses_are_normalised(): void
    {
        $s = new CodeSnippet(['scope' => 'only', 'pages' => [CodeSnippet::normalizePath('https://example.com/Contact/?a=1#x'), CodeSnippet::normalizePath('faqs')]]);
        $this->assertSame(['/contact', '/faqs'], $s->pages);
        $this->assertTrue($s->appliesTo('/contact'));
        $this->assertTrue($s->appliesTo('/faqs/'));
        $this->assertFalse($s->appliesTo('/about'));
    }

    public function test_the_existing_site_scripts_and_styles_keep_working(): void
    {
        $this->snippet(['code' => '<meta name="extra">']);

        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('/build/assets/app-', $html, 'the site stylesheet and script are still loaded');
        $this->assertStringContainsString('application/ld+json', $html, 'structured data is still there');
    }

    public function test_nothing_is_output_inside_the_admin(): void
    {
        $this->snippet(['code' => '<meta name="admin-leak">']);
        $this->admin();

        $this->get('/admin')->assertOk()->assertDontSee('admin-leak', false);
    }

    public function test_the_screens_need_an_admin_login(): void
    {
        $this->get('/admin/snippets')->assertRedirect(route('admin.login'));
        $this->post('/admin/snippets', ['name' => 'x'])->assertRedirect(route('admin.login'));
        $this->post('/admin/snippets/1/toggle')->assertRedirect(route('admin.login'));
    }

    public function test_an_admin_can_add_edit_toggle_and_delete_a_snippet(): void
    {
        $this->admin();

        $this->get('/admin/snippets')->assertOk()->assertSee('Careful: this code runs on your live website.')->assertSee('No code added yet');
        $this->get('/admin/snippets/create')->assertOk()->assertSee('Runs on')->assertSee('Place it')->assertSee('Entire website');

        $code = "<script>\n  if (a < b && c > d) { console.log(\"x\"); }\n</script>";
        $this->post('/admin/snippets', [
            'name' => 'Facebook Pixel', 'type' => 'script', 'placement' => 'head', 'code' => $code,
            'scope' => 'only', 'pages_pick' => ['/contact'], 'pages_extra' => "https://x.test/Packages/*\nfaqs", 'sort' => 3, 'is_active' => '1',
        ])->assertRedirect(route('admin.snippets.index'));

        $s = CodeSnippet::firstOrFail();
        $this->assertSame($code, $s->code, 'saved exactly as entered, not sanitised');
        $this->assertSame(['/contact', '/packages/*', '/faqs'], $s->pages);
        $this->assertTrue($s->is_active);

        $this->get('/admin/snippets')->assertOk()->assertSee('Facebook Pixel')->assertSee('Only on 3 pages')->assertSee('In &lt;head&gt;', false);

        $this->put("/admin/snippets/{$s->id}", ['name' => 'Pixel v2', 'type' => 'script', 'placement' => 'body_end', 'code' => 'alert(1)', 'scope' => 'all', 'sort' => 1, 'is_active' => '0'])->assertRedirect();
        $s->refresh();
        $this->assertSame('Pixel v2', $s->name);
        $this->assertSame('body_end', $s->placement);
        $this->assertSame([], $s->pages);
        $this->assertFalse($s->is_active);

        $this->post("/admin/snippets/{$s->id}/toggle")->assertRedirect();
        $this->assertTrue($s->fresh()->is_active);

        $this->delete("/admin/snippets/{$s->id}")->assertRedirect(route('admin.snippets.index'));
        $this->assertSame(0, CodeSnippet::count());
    }

    public function test_validation_asks_for_the_essentials(): void
    {
        $this->admin();

        $this->post('/admin/snippets', ['name' => '', 'code' => '', 'type' => 'script', 'placement' => 'head', 'scope' => 'all'])->assertSessionHasErrors(['name', 'code']);
        $this->post('/admin/snippets', ['name' => 'x', 'code' => 'y', 'type' => 'script', 'placement' => 'nowhere', 'scope' => 'all'])->assertSessionHasErrors('placement');
        $this->post('/admin/snippets', ['name' => 'x', 'code' => 'y', 'type' => 'script', 'placement' => 'head', 'scope' => 'only'])->assertSessionHasErrors('pages_extra');
        $this->assertSame(0, CodeSnippet::count());
    }

    public function test_the_sidebar_links_to_header_and_footer(): void
    {
        $this->admin();
        $this->get('/admin')->assertSee('Header &amp; Footer', false);
    }
}
