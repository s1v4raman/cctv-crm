<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/profile');

        $response->assertOk();
    }

    public function test_profile_information_can_be_updated(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $user->refresh();

        $this->assertSame('Test User', $user->name);
        $this->assertSame('test@example.com', $user->email);
        $this->assertNull($user->email_verified_at);
    }

    public function test_email_verification_status_is_unchanged_when_the_email_address_is_unchanged(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => $user->email,
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $this->assertNotNull($user->refresh()->email_verified_at);
    }

    public function test_user_can_delete_their_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->delete('/profile', [
                'password' => 'password',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/');

        $this->assertGuest();
        $this->assertNull($user->fresh());
    }

    public function test_correct_password_must_be_provided_to_delete_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from('/profile')
            ->delete('/profile', [
                'password' => 'wrong-password',
            ]);

        $response
            ->assertSessionHasErrorsIn('userDeletion', 'password')
            ->assertRedirect('/profile');

        $this->assertNotNull($user->fresh());
    }

    public function test_customer_profile_update_syncs_to_lead_record(): void
    {
        $lead = \App\Models\Lead::create([
            'customer_name' => 'Original Customer',
            'email' => 'original@client.com',
            'phone' => '9876543210',
            'status' => 'won',
        ]);

        $customer = User::factory()->create([
            'name' => 'Original Customer',
            'email' => 'original@client.com',
            'role' => 'customer',
            'lead_id' => $lead->id,
        ]);

        $response = $this->actingAs($customer)->patch('/profile', [
            'name' => 'Updated Customer Name',
            'email' => 'updated@client.com',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect('/profile');

        $lead->refresh();
        $this->assertSame('Updated Customer Name', $lead->customer_name);
        $this->assertSame('updated@client.com', $lead->email);
    }

    public function test_user_can_upload_and_remove_avatar_photo(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');

        $user = User::factory()->create([
            'role' => 'admin',
        ]);

        $jpegContent = base64_decode('/9j/4AAQSkZJRgABAQEASABIAAD/2wBDAP//////////////////////////////////////////////////////////////////////////////////////wgALCAABAAEBAREA/8QAFBABAAAAAAAAAAAAAAAAAAAAAP/aAAgBAQABPxA=');
        $file = \Illuminate\Http\UploadedFile::fake()->createWithContent('avatar.jpg', $jpegContent);

        $response = $this->actingAs($user)->patch('/profile', [
            'name'   => $user->name,
            'email'  => $user->email,
            'avatar' => $file,
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect('/profile');

        $user->refresh();
        $this->assertNotNull($user->avatar);
        $this->assertNotNull($user->avatar_url);
        \Illuminate\Support\Facades\Storage::disk('public')->assertExists($user->avatar);

        // Test removing avatar
        $removeResponse = $this->actingAs($user)->patch('/profile', [
            'name'          => $user->name,
            'email'         => $user->email,
            'remove_avatar' => 1,
        ]);

        $removeResponse->assertSessionHasNoErrors();
        $user->refresh();
        $this->assertNull($user->avatar);
        $this->assertNull($user->avatar_url);
    }

    public function test_user_can_update_theme_preferences(): void
    {
        $user = User::factory()->create([
            'theme_mode' => 'day',
        ]);

        $response = $this->actingAs($user)->post('/profile/theme', [
            'mode'   => 'night',
            'accent' => '#2563eb',
        ]);

        $response->assertOk();
        $response->assertJson([
            'success'      => true,
            'theme_mode'   => 'night',
            'theme_accent' => '#2563eb',
        ]);
        $response->assertPlainCookie('crm_mode', 'night');

        $user->refresh();
        $this->assertSame('night', $user->theme_mode);
        $this->assertSame('#2563eb', $user->theme_accent);
    }

    public function test_unverified_user_can_also_update_theme_preferences(): void
    {
        $user = User::factory()->unverified()->create([
            'theme_mode' => 'night',
        ]);

        $response = $this->actingAs($user)->post('/profile/theme', [
            'mode' => 'day',
        ]);

        $response->assertOk();
        $response->assertPlainCookie('crm_mode', 'day');

        $user->refresh();
        $this->assertSame('day', $user->theme_mode);
    }
}
