<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Symfony\Component\Mailer\Exception\TransportException;
use Tests\TestCase;

class SignInAndResetEmailTest extends TestCase
{
    use RefreshDatabase;

    public function test_email_typed_with_capitals_or_spaces_still_signs_in(): void
    {
        User::factory()->create(['email' => 'raman@example.com', 'password' => 'correct-horse-9', 'role' => 'technician']);

        $this->post('/login', ['email' => '  Raman@Example.com ', 'password' => 'correct-horse-9']);

        $this->assertAuthenticated();
    }

    public function test_reset_link_is_sent_for_an_email_typed_with_capitals(): void
    {
        Notification::fake();
        $user = User::factory()->create(['email' => 'raman@example.com']);

        $this->post('/forgot-password', ['email' => 'RAMAN@example.com'])->assertSessionHas('status');

        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_server_without_a_mail_service_says_no_email_was_sent(): void
    {
        Notification::fake();
        $this->app['env'] = 'production';
        config(['mail.default' => 'log']);
        User::factory()->create(['email' => 'raman@example.com']);

        // Outside "testing" the CSRF check is back on, so send a token
        $this->withSession(['_token' => 'reset-test'])->from('/forgot-password')
            ->post('/forgot-password', ['_token' => 'reset-test', 'email' => 'raman@example.com'])
            ->assertSessionHasErrors(['email' => 'Password reset emails are not set up on this server yet. Ask your administrator to set a new password for you.'])
            ->assertSessionMissing('status');

        Notification::assertNothingSent();
    }

    public function test_mail_server_failure_shows_a_message_instead_of_an_error_page(): void
    {
        Password::shouldReceive('sendResetLink')->once()->andThrow(new TransportException('Connection could not be established with host "smtp.example.com:587"'));

        $this->from('/forgot-password')->post('/forgot-password', ['email' => 'raman@example.com'])
            ->assertRedirect('/forgot-password')
            ->assertSessionHasErrors(['email' => 'The reset email could not be sent. Try again in a few minutes, or ask your administrator to set a new password for you.']);
    }

    public function test_user_forms_do_not_let_the_browser_fill_in_the_admins_own_login(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $tech = User::factory()->create(['role' => 'technician']);

        foreach ([route('admin.users.create'), route('admin.users.edit', $tech)] as $url) {
            $this->actingAs($admin)->get($url)
                ->assertOk()
                ->assertSee('name="email" autocomplete="off"', false)
                ->assertSee('name="password" autocomplete="new-password"', false)
                ->assertSee('name="password_confirmation" autocomplete="new-password"', false);
        }
    }
}
