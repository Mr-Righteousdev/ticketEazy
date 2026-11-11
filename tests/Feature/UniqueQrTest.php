<?php

namespace Tests\Feature;

use App\Models\UniqueQr;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UniqueQrTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['uniqueqr.secret' => 'owner-phrase', 'uniqueqr.fallback_url' => 'https://default.test']);
    }

    private function admin(): User
    {
        Role::findOrCreate('admin');

        return User::factory()->create()->assignRole('admin');
    }

    public function test_go_without_cookie_uses_record_fallback(): void
    {
        $qr = UniqueQr::create(['owner_url' => 'https://owner.test', 'fallback_url' => 'https://other.test']);

        $this->get("/go/{$qr->code}")->assertRedirect('https://other.test')->assertHeader('Cache-Control', 'no-store, private');
    }

    public function test_go_without_cookie_and_null_fallback_uses_config_default(): void
    {
        $qr = UniqueQr::create(['owner_url' => 'https://owner.test']);

        $this->get("/go/{$qr->code}")->assertRedirect('https://default.test');
    }

    public function test_go_with_owner_cookie_uses_owner_url(): void
    {
        $qr = UniqueQr::create(['owner_url' => 'https://owner.test']);

        $this->withCookie('uqr_owner', '1')->get("/go/{$qr->code}")->assertRedirect('https://owner.test');
    }

    public function test_go_with_unencrypted_cookie_uses_fallback(): void
    {
        $qr = UniqueQr::create(['owner_url' => 'https://owner.test']);

        $this->withUnencryptedCookie('uqr_owner', '1')->get("/go/{$qr->code}")->assertRedirect('https://default.test');
    }

    public function test_go_unknown_code_uses_config_default(): void
    {
        $this->get('/go/nope')->assertRedirect('https://default.test');
    }

    public function test_setup_with_correct_phrase_sets_cookie(): void
    {
        $this->post('/uqr/setup', ['password' => 'owner-phrase'])->assertOk()->assertCookie('uqr_owner', '1');
    }

    public function test_setup_with_wrong_phrase_sets_no_cookie(): void
    {
        $this->post('/uqr/setup', ['password' => 'wrong'])->assertSee('Incorrect phrase')->assertCookieMissing('uqr_owner');
    }

    public function test_setup_fails_when_secret_not_configured(): void
    {
        config(['uniqueqr.secret' => '']);

        $this->post('/uqr/setup', ['password' => ''])->assertCookieMissing('uqr_owner');
    }

    public function test_setup_is_throttled(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->post('/uqr/setup', ['password' => 'wrong']);
        }

        $this->post('/uqr/setup', ['password' => 'owner-phrase'])->assertStatus(429);
    }

    public function test_forget_removes_cookie(): void
    {
        $this->withCookie('uqr_owner', '1')->get('/uqr/forget')->assertCookieExpired('uqr_owner');
    }

    public function test_create_requires_admin(): void
    {
        $this->get('/uqr/create')->assertRedirect('/login');
        $this->post('/uqr/create', ['owner_url' => 'https://owner.test'])->assertRedirect('/login');
        $this->actingAs(User::factory()->create())->get('/uqr/create')->assertForbidden();
        $this->assertDatabaseCount('unique_qrs', 0);
    }

    public function test_create_returns_png_and_saves_record(): void
    {
        $response = $this->actingAs($this->admin())->post('/uqr/create', ['owner_url' => 'https://owner.test']);

        $response->assertOk()->assertHeader('Content-Type', 'image/png');
        $qr = UniqueQr::sole();
        $this->assertStringContainsString("qr-{$qr->code}.png", $response->headers->get('Content-Disposition'));

        [$width] = getimagesizefromstring($response->getContent());
        $this->assertGreaterThanOrEqual(1200, $width);
    }

    public function test_authorized_page_only_for_owner_device(): void
    {
        $this->get('/authorized')->assertRedirect('https://default.test');
        $this->withCookie('uqr_owner', '1')->get('/authorized')->assertOk()->assertSee('Authorized');
    }

    public function test_create_without_private_link_defaults_to_authorized_page(): void
    {
        $this->actingAs($this->admin())->post('/uqr/create')->assertOk();

        $this->assertSame(route('uqr.authorized'), UniqueQr::sole()->owner_url);
    }

    public function test_create_rejects_non_http_links(): void
    {
        $this->actingAs($this->admin())
            ->post('/uqr/create', ['owner_url' => 'javascript:alert(1)', 'fallback_url' => 'ftp://x.test'])
            ->assertSessionHasErrors(['owner_url', 'fallback_url']);

        $this->assertDatabaseCount('unique_qrs', 0);
    }
}
