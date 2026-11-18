<?php

namespace Tests\Feature;

use App\Models\AdminUser;
use App\Models\Setting;
use App\Services\Ghl\GhlFields;
use App\Services\GhlClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GhlAdminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        config(['gotour.ghl.token' => null, 'gotour.ghl.location_id' => null]);
        $this->actingAs(AdminUser::where('email', 'admin@test.example')->firstOrFail());
    }

    public function test_the_screen_loads_and_is_off_until_credentials_are_saved(): void
    {
        $this->get('/admin/ghl')->assertOk()->assertSee('GoHighLevel integration')->assertSee('Off: add the Location ID');
        $this->assertFalse(app(GhlClient::class)->enabled());
    }

    public function test_saving_stores_the_token_encrypted_and_switches_the_sync_on(): void
    {
        $this->put('/admin/ghl', ['location_id' => 'LOC123abc', 'api_token' => 'pit-secret-token-dec1', 'send' => '1'])->assertRedirect();

        $stored = Setting::get('ghl_api_token');
        $this->assertNotSame('pit-secret-token-dec1', $stored, 'never stored in plain text');
        $this->assertStringNotContainsString('pit-secret', (string) $stored);

        $ghl = app(GhlClient::class);
        $this->assertSame('pit-secret-token-dec1', $ghl->token());
        $this->assertSame('LOC123abc', $ghl->locationId());
        $this->assertTrue($ghl->enabled());

        $page = $this->get('/admin/ghl')->assertOk()->assertSee('On: enquiries are sent')->assertSee('dec1');
        $this->assertStringNotContainsString('pit-secret-token', $page->getContent(), 'the full token is never shown again');
    }

    public function test_a_blank_token_keeps_the_saved_one_and_remove_clears_it(): void
    {
        $this->put('/admin/ghl', ['location_id' => 'LOC1', 'api_token' => 'tok-one-1234', 'send' => '1']);
        $this->put('/admin/ghl', ['location_id' => 'LOC2', 'api_token' => '', 'send' => '1']);
        $this->assertSame('tok-one-1234', app(GhlClient::class)->token());
        $this->assertSame('LOC2', app(GhlClient::class)->locationId());

        $this->put('/admin/ghl', ['location_id' => 'LOC2', 'token_remove' => '1', 'send' => '1']);
        $this->assertNull(app(GhlClient::class)->token());
        $this->assertFalse(app(GhlClient::class)->enabled());
    }

    public function test_unticking_send_pauses_the_sync_but_keeps_the_credentials(): void
    {
        $this->put('/admin/ghl', ['location_id' => 'LOC1', 'api_token' => 'tok-1234', 'send' => '1']);
        $this->put('/admin/ghl', ['location_id' => 'LOC1', 'api_token' => '']);

        $ghl = app(GhlClient::class);
        $this->assertTrue($ghl->paused());
        $this->assertFalse($ghl->enabled());
        $this->assertSame('tok-1234', $ghl->token());
        $this->get('/admin/ghl')->assertSee('Paused');
    }

    public function test_the_location_id_is_validated(): void
    {
        $this->put('/admin/ghl', ['location_id' => 'bad id!', 'api_token' => 'x'])->assertSessionHasErrors('location_id');
    }

    public function test_connection_test_reports_success_and_missing_fields(): void
    {
        $this->put('/admin/ghl', ['location_id' => 'LOC1', 'api_token' => 'tok-1234', 'send' => '1']);
        Http::fake([
            '*/locations/LOC1/customFields*' => Http::response(['customFields' => [['id' => 'f1', 'name' => 'Package']]]),
            '*/locations/LOC1' => Http::response(['location' => ['name' => 'Go Belgium']]),
        ]);

        $this->post('/admin/ghl/test')->assertRedirect()->assertSessionHas('status', fn ($s) => str_starts_with($s, 'Connected to Go Belgium') && str_contains($s, 'missing') && str_contains($s, ' are missing'));
        Http::assertSent(fn ($r) => $r->hasHeader('Authorization', 'Bearer tok-1234'));
    }

    public function test_connection_test_explains_a_rejected_token(): void
    {
        $this->put('/admin/ghl', ['location_id' => 'LOC1', 'api_token' => 'bad-token', 'send' => '1']);
        Http::fake(['*' => Http::response(['message' => 'Unauthorized'], 401)]);

        $this->post('/admin/ghl/test')->assertRedirect()->assertSessionHasErrors('ghl');
        $this->assertStringContainsString('rejected the token', session('errors')->first('ghl'));
    }

    public function test_connection_is_ok_when_only_the_view_locations_scope_is_missing(): void
    {
        $this->put('/admin/ghl', ['location_id' => 'LOC1', 'api_token' => 'tok-1234', 'send' => '1']);
        Http::fake([
            '*/locations/LOC1/customFields*' => Http::response(['customFields' => [['id' => 'f1', 'name' => 'Package']]]),
            '*/locations/LOC1' => Http::response(['statusCode' => 401, 'message' => 'The token is not authorized for this scope.'], 401),
        ]);

        $this->post('/admin/ghl/test')->assertRedirect()->assertSessionHasNoErrors()
            ->assertSessionHas('status', fn ($s) => str_starts_with($s, 'Connected.') && str_contains($s, 'custom fields and contacts'));
    }

    public function test_the_refusal_message_from_gohighlevel_is_shown(): void
    {
        $this->put('/admin/ghl', ['location_id' => 'LOC1', 'api_token' => 'bad-token', 'send' => '1']);
        Http::fake(['*' => Http::response(['statusCode' => 401, 'message' => 'The token is not authorized for this scope.'], 401)]);

        $this->post('/admin/ghl/test')->assertSessionHasErrors('ghl');
        $this->assertStringContainsString('The token is not authorized for this scope.', session('errors')->first('ghl'));
    }

    public function test_a_pasted_token_is_cleaned_of_quotes_spaces_and_the_bearer_prefix(): void
    {
        $this->put('/admin/ghl', ['location_id' => "  LOC1\n", 'api_token' => "Bearer \"pit-abc123 \n\"\n", 'send' => '1']);

        $this->assertSame('pit-abc123', app(GhlClient::class)->token());
        $this->assertSame('LOC1', app(GhlClient::class)->locationId());
    }

    public function test_create_missing_custom_fields_only_creates_what_is_missing(): void
    {
        $this->put('/admin/ghl', ['location_id' => 'LOC1', 'api_token' => 'tok-1234', 'send' => '1']);
        $all = array_keys(GhlFields::definitions());
        $existing = collect(array_slice($all, 0, 5))->map(fn ($n, $i) => ['id' => "f$i", 'name' => $n])->all();
        Http::fake([
            '*/locations/LOC1/customFields*' => fn ($r) => $r->method() === 'POST'
                ? Http::response(['customField' => ['id' => 'new']])
                : Http::response(['customFields' => $existing]),
        ]);

        $this->post('/admin/ghl/setup')->assertRedirect()->assertSessionHas('status', fn ($s) => str_contains($s, 'created '.(count($all) - 5)) && str_contains($s, 'already existed 5'));
        Http::assertSentCount(1 + (count($all) - 5) + 1); // list, one create per missing field, refresh
    }

    public function test_actions_need_credentials_first(): void
    {
        $this->post('/admin/ghl/test')->assertSessionHasErrors('ghl');
        $this->post('/admin/ghl/setup')->assertSessionHasErrors('ghl');
    }

    public function test_the_sidebar_links_to_the_screen_and_requires_login(): void
    {
        $this->get('/admin')->assertSee('GoHighLevel');
        auth()->logout();
        $this->get('/admin/ghl')->assertRedirect(route('admin.login'));
    }
}
