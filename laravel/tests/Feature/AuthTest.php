<?php

namespace Tests\Feature;

use App\Models\User;
use Filament\Auth\Pages\Login;
use Filament\Facades\Filament;
use Filament\Http\Middleware\AuthenticateSession;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Livewire\Livewire;
use Tests\TestCase;

class AuthTest extends TestCase
{
    public function test_guests_are_sent_to_the_login_page(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
        $this->get('/admin/packages')->assertRedirect('/admin/login');
        $this->get('/preview/')->assertRedirect('/admin/login');
    }

    public function test_there_is_no_public_registration_or_email_reset(): void
    {
        $this->get('/admin/register')->assertNotFound();
        $this->get('/admin/password-reset/request')->assertNotFound();
    }

    public function test_active_admin_can_open_the_panel(): void
    {
        $this->actingAs($this->admin())->get('/admin')->assertOk();
    }

    public function test_deactivated_admin_is_refused(): void
    {
        $user = $this->admin(['is_active' => false]);
        $this->actingAs($user)->get('/admin')->assertForbidden();
        $this->actingAs($user)->get('/preview/')->assertRedirect('/admin/login');
    }

    public function test_passwords_are_hashed(): void
    {
        $user = User::factory()->create(['password' => 'correct horse battery 42']);
        $this->assertNotSame('correct horse battery 42', $user->getRawOriginal('password'));
        $this->assertTrue(password_verify('correct horse battery 42', $user->getRawOriginal('password')));
    }

    public function test_login_is_throttled_after_repeated_failures(): void
    {
        User::factory()->create(['email' => 'admin@example.test', 'password' => 'right-password-123']);
        $component = Livewire::test(Login::class);
        foreach (range(1, 5) as $_) {
            $component->fillForm(['email' => 'admin@example.test', 'password' => 'wrong'])->call('authenticate');
        }
        $component->fillForm(['email' => 'admin@example.test', 'password' => 'right-password-123'])->call('authenticate');
        $this->assertGuest();
    }

    public function test_admin_panel_uses_csrf_and_session_protection(): void
    {
        // Laravel skips CSRF checks while running tests, so assert the middleware is configured.
        $middleware = Filament::getPanel('admin')->getMiddleware();
        $this->assertContains(PreventRequestForgery::class, $middleware);
        $this->assertContains(AuthenticateSession::class, $middleware);
    }
}
