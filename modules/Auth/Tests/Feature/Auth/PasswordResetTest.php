<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Password;

beforeEach(function () {
    Notification::fake();
});

describe('POST /auth/forgot-password', function () {
    it('returns success & notifies the user when email exists', function () {
        $user = User::factory()->create(['email' => 'john@example.com']);

        $response = $this->postJson('/auth/forgot-password', [
            'email' => $user->email,
        ]);

        $response->assertOk()
            ->assertJson(['status' => Password::RESET_LINK_SENT]);

        Notification::assertSentTo($user, ResetPassword::class, function ($notification, $channels) use ($user) {
            // The email in the notification should match the user’s email
            return $notification->toMail($user)->to[0]['address'] === $user->email;
        });
    });

    it('responds with success even if email does not exist', function () {
        $response = $this->postJson('/auth/forgot-password', [
            'email' => 'missing@example.com',
        ]);

        $response->assertOk()
            ->assertJson(['status' => Password::RESET_LINK_SENT]);

        Notification::assertNothingSent();
    });

    it('validates email format', function () {
        $response = $this->postJson('/auth/forgot-password', [
            'email' => 'bad-format',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('email');
    });
});

describe('POST /auth/reset-password', function () {
    it('resets password when email and token are valid', function () {
        $user = User::factory()->create([
            'email' => 'john@example.com',
            'password' => Hash::make('OldPass123!'),
        ]);

        // Generate a valid token and capture it
        Password::sendResetLink(['email' => $user->email]);
        Notification::assertSentTo($user, ResetPassword::class);

        $token = Notification::sent($user, ResetPassword::class)->first()->token;

        $response = $this->postJson('/auth/reset-password', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'NewPass123!',
            'password_confirmation' => 'NewPass123!',
        ]);

        $response->assertOk()
            ->assertJson(['status' => Password::PASSWORD_RESET]);

        $user->refresh();
        expect(Hash::check('NewPass123!', $user->password))->toBeTrue();
    });

    it('validates required fields with correct email presence', function ($field, $payload) {
        $response = $this->postJson('/auth/reset-password', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors($field);
    })->with([
        'missing token' => ['token', [
            'email' => 'john@example.com',
            'password' => 'Pass123!',
            'password_confirmation' => 'Pass123!',
        ]],
        'missing email' => ['email', [
            'token' => 'dummy',
            'password' => 'Pass123!',
            'password_confirmation' => 'Pass123!',
        ]],
        'missing password' => ['password', [
            'token' => 'dummy',
            'email' => 'john@example.com',
            'password_confirmation' => 'Pass123!',
        ]],
        'password mismatch' => ['password', [
            'token' => 'dummy',
            'email' => 'john@example.com',
            'password' => 'Pass123!',
            'password_confirmation' => 'Mismatch123!',
        ]],
    ]);

    it('rejects invalid token even when email is provided', function () {
        $user = User::factory()->create(['email' => 'john@example.com']);

        $response = $this->postJson('/auth/reset-password', [
            'token' => 'not-a-real-token',
            'email' => $user->email,
            'password' => 'NewPass123!',
            'password_confirmation' => 'NewPass123!',
        ]);

        $response->assertStatus(422)
            ->assertJson(['status' => Password::INVALID_TOKEN]);
    });

    it('allows login after successful reset with email', function () {
        $user = User::factory()->create(['email' => 'john@example.com']);

        Password::sendResetLink(['email' => $user->email]);
        $token = Notification::sent($user, ResetPassword::class)->first()->token;

        // Reset
        $this->postJson('/auth/reset-password', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'NewPass123!',
            'password_confirmation' => 'NewPass123!',
        ]);

        // Try login with email & new password
        $loginResponse = $this->postJson('/auth/login', [
            'email' => $user->email,
            'password' => 'NewPass123!',
        ]);

        $loginResponse->assertOk();
    });
});
