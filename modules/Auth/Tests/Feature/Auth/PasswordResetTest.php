<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;

beforeEach(function () {
    Mail::fake();
});

describe('POST /auth/forgot-password', function () {
    it('sends reset link for existing email', function () {
        $user = User::factory()->create(['email' => 'john.doe@example.com']);

        $response = $this->postJson('/auth/forgot-password', [
            'email' => 'john.doe@example.com',
        ]);

        $response->assertOk()
            ->assertJson(['message' => 'Password reset link sent if the email exists.']);

        Mail::assertSent(fn($mail) => $mail->hasTo('john.doe@example.com'));
    });

    it('does not reveal non-existent emails', function () {
        $response = $this->postJson('/auth/forgot-password', [
            'email' => 'nonexistent@example.com',
        ]);

        $response->assertOk()
            ->assertJson(['message' => 'Password reset link sent if the email exists.']);

        Mail::assertNothingSent();
    });

    it('validates email format', function () {
        $response = $this->postJson('/auth/forgot-password', [
            'email' => 'invalid-email',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('email');
    });
});

describe('POST /auth/reset-password', function () {
    it('resets password with valid token', function () {
        $user = User::factory()->create([
            'email' => 'john.doe@example.com',
            'password' => Hash::make('OldPassword123!'),
        ]);
        $token = Password::createToken($user);

        $response = $this->postJson('/auth/reset-password', [
            'token' => $token,
            'email' => 'john.doe@example.com',
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ]);

        $response->assertOk()
            ->assertJson(['message' => 'Password has been reset successfully.']);

        $user->refresh();
        expect(Hash::check('NewPassword123!', $user->password))->toBeTrue();
    });

    it('validates required fields', function ($field, $payload) {
        $response = $this->postJson('/auth/reset-password', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors($field);
    })->with([
        'missing token' => ['token', [
            'email' => 'test@test.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]],
        'missing email' => ['email', [
            'token' => 'token',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]],
        'password too short' => ['password', [
            'token' => 'token',
            'email' => 'test@test.com',
            'password' => 'short',
            'password_confirmation' => 'short',
        ]],
        'password mismatch' => ['password', [
            'token' => 'token',
            'email' => 'test@test.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Different123!',
        ]],
    ]);

    it('rejects invalid tokens', function () {
        $response = $this->postJson('/auth/reset-password', [
            'token' => 'invalid-token',
            'email' => 'john.doe@example.com',
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ]);

        $response->assertStatus(400);
    });

    it('allows login with new password', function () {
        $user = User::factory()->create(['email' => 'john@test.com']);
        $token = Password::createToken($user);

        $this->postJson('/auth/reset-password', [
            'token' => $token,
            'email' => 'john@test.com',
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ]);

        $loginResponse = $this->postJson('/auth/login', [
            'email' => 'john@test.com',
            'password' => 'NewPassword123!',
        ]);

        $loginResponse->assertOk();
    });
});

describe('POST /auth/forgot-password', function () {
    beforeEach(function () {
        Mail::fake();
    });

    it('successfully sends password reset link for existing email', function () {
        $user = User::factory()->create(['email' => 'john.doe@example.com']);

        $payload = [
            'email' => 'john.doe@example.com',
        ];

        $response = $this->postJson('/auth/forgot-password', $payload);

        $response->assertStatus(200)
            ->assertJsonStructure(['message'])
            ->assertJson([
                'message' => 'Password reset link sent if the email exists.',
            ]);
    });

    it('sends password reset email to user', function () {
        $user = User::factory()->create(['email' => 'john.doe@example.com']);

        $payload = [
            'email' => 'john.doe@example.com',
        ];

        $this->postJson('/auth/forgot-password', $payload);

        Mail::assertSent(function ($mail) use ($user) {
            return $mail->hasTo($user->email);
        });
    });

    it('creates password reset token in database', function () {
        $user = User::factory()->create(['email' => 'john.doe@example.com']);

        $payload = [
            'email' => 'john.doe@example.com',
        ];

        $this->postJson('/auth/forgot-password', $payload);

        $this->assertDatabaseHas('password_reset_tokens', [
            'email' => 'john.doe@example.com',
        ]);
    });

    it('returns success message even for non-existent email', function () {
        $payload = [
            'email' => 'nonexistent@example.com',
        ];

        $response = $this->postJson('/auth/forgot-password', $payload);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Password reset link sent if the email exists.',
            ]);
    });

    it('does not send email for non-existent email', function () {
        $payload = [
            'email' => 'nonexistent@example.com',
        ];

        $this->postJson('/auth/forgot-password', $payload);

        Mail::assertNothingSent();
    });

    it('does not reveal if email exists in system', function () {
        User::factory()->create(['email' => 'existing@example.com']);

        $existingResponse = $this->postJson('/auth/forgot-password', [
            'email' => 'existing@example.com',
        ]);

        $nonExistentResponse = $this->postJson('/auth/forgot-password', [
            'email' => 'nonexistent@example.com',
        ]);

        // Both should return the same message
        expect($existingResponse->json('message'))
            ->toBe($nonExistentResponse->json('message'));
    });

    it('fails validation when email is missing', function () {
        $payload = [];

        $response = $this->postJson('/auth/forgot-password', $payload);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => ['email'],
            ]);
    });

    it('fails validation when email format is invalid', function () {
        $payload = [
            'email' => 'invalid-email-format',
        ];

        $response = $this->postJson('/auth/forgot-password', $payload);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => ['email'],
            ]);
    });

    it('fails validation when email exceeds maximum length', function () {
        $payload = [
            'email' => str_repeat('a', 120) . '@test.com', // Exceeds 128 chars
        ];

        $response = $this->postJson('/auth/forgot-password', $payload);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => ['email'],
            ]);
    });

    it('allows multiple password reset requests', function () {
        $user = User::factory()->create(['email' => 'john.doe@example.com']);

        $payload = ['email' => 'john.doe@example.com'];

        $firstResponse = $this->postJson('/auth/forgot-password', $payload);
        $secondResponse = $this->postJson('/auth/forgot-password', $payload);

        $firstResponse->assertStatus(200);
        $secondResponse->assertStatus(200);
    });

    it('updates existing reset token for same email', function () {
        $user = User::factory()->create(['email' => 'john.doe@example.com']);

        $payload = ['email' => 'john.doe@example.com'];

        // First request
        $this->postJson('/auth/forgot-password', $payload);

        // Wait a moment
        sleep(1);

        // Second request
        $this->postJson('/auth/forgot-password', $payload);

        // Should still have only one token for this email
        $tokenCount = \DB::table('password_reset_tokens')
            ->where('email', 'john.doe@example.com')
            ->count();

        expect($tokenCount)->toBe(1);
    });

    it('handles server error gracefully', function () {
        $this->mockMailerFailure();

        $user = User::factory()->create(['email' => 'john.doe@example.com']);

        $payload = ['email' => 'john.doe@example.com'];

        $response = $this->postJson('/auth/forgot-password', $payload);

        $response->assertStatus(500)
            ->assertJsonStructure(['error']);
    });

    it('is case-insensitive for email', function () {
        User::factory()->create(['email' => 'John.Doe@example.com']);

        $payload = [
            'email' => 'john.doe@EXAMPLE.COM',
        ];

        $response = $this->postJson('/auth/forgot-password', $payload);

        $response->assertStatus(200);
    });
});

describe('POST /auth/reset-password', function () {
    it('successfully resets password with valid token', function () {
        $user = User::factory()->create(['email' => 'john.doe@example.com']);
        $token = Password::createToken($user);

        $payload = [
            'token' => $token,
            'email' => 'john.doe@example.com',
            'password' => 'NewSecurePassword123!',
            'password_confirmation' => 'NewSecurePassword123!',
        ];

        $response = $this->postJson('/auth/reset-password', $payload);

        $response->assertStatus(200)
            ->assertJsonStructure(['message'])
            ->assertJson([
                'message' => 'Password has been reset successfully.',
            ]);
    });

    it('updates password in database after reset', function () {
        $user = User::factory()->create([
            'email' => 'john.doe@example.com',
            'password' => Hash::make('OldPassword123!'),
        ]);
        $token = Password::createToken($user);

        $payload = [
            'token' => $token,
            'email' => 'john.doe@example.com',
            'password' => 'NewSecurePassword123!',
            'password_confirmation' => 'NewSecurePassword123!',
        ];

        $this->postJson('/auth/reset-password', $payload);

        $user->refresh();

        expect(Hash::check('NewSecurePassword123!', $user->password))->toBeTrue();
        expect(Hash::check('OldPassword123!', $user->password))->toBeFalse();
    });

    it('allows login with new password after reset', function () {
        $user = User::factory()->create(['email' => 'john.doe@example.com']);
        $token = Password::createToken($user);

        $resetPayload = [
            'token' => $token,
            'email' => 'john.doe@example.com',
            'password' => 'NewSecurePassword123!',
            'password_confirmation' => 'NewSecurePassword123!',
        ];

        $this->postJson('/auth/reset-password', $resetPayload);

        // Try to login with new password
        $loginResponse = $this->postJson('/auth/login', [
            'email' => 'john.doe@example.com',
            'password' => 'NewSecurePassword123!',
        ]);

        $loginResponse->assertStatus(200);
        $this->assertAuthenticated();
    });

    it('deletes reset token after successful password reset', function () {
        $user = User::factory()->create(['email' => 'john.doe@example.com']);
        $token = Password::createToken($user);

        $payload = [
            'token' => $token,
            'email' => 'john.doe@example.com',
            'password' => 'NewSecurePassword123!',
            'password_confirmation' => 'NewSecurePassword123!',
        ];

        $this->postJson('/auth/reset-password', $payload);

        $this->assertDatabaseMissing('password_reset_tokens', [
            'email' => 'john.doe@example.com',
        ]);
    });

    it('fails validation when token is missing', function () {
        $payload = [
            'email' => 'john.doe@example.com',
            'password' => 'NewSecurePassword123!',
            'password_confirmation' => 'NewSecurePassword123!',
        ];

        $response = $this->postJson('/auth/reset-password', $payload);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => ['token'],
            ]);
    });

    it('fails validation when email is missing', function () {
        $payload = [
            'token' => 'some-token',
            'password' => 'NewSecurePassword123!',
            'password_confirmation' => 'NewSecurePassword123!',
        ];

        $response = $this->postJson('/auth/reset-password', $payload);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => ['email'],
            ]);
    });

    it('fails validation when password is missing', function () {
        $payload = [
            'token' => 'some-token',
            'email' => 'john.doe@example.com',
            'password_confirmation' => 'NewSecurePassword123!',
        ];

        $response = $this->postJson('/auth/reset-password', $payload);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => ['password'],
            ]);
    });

    it('fails validation when password_confirmation is missing', function () {
        $payload = [
            'token' => 'some-token',
            'email' => 'john.doe@example.com',
            'password' => 'NewSecurePassword123!',
        ];

        $response = $this->postJson('/auth/reset-password', $payload);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => ['password'],
            ]);
    });

    it('fails validation when email format is invalid', function () {
        $payload = [
            'token' => 'some-token',
            'email' => 'invalid-email-format',
            'password' => 'NewSecurePassword123!',
            'password_confirmation' => 'NewSecurePassword123!',
        ];

        $response = $this->postJson('/auth/reset-password', $payload);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => ['email'],
            ]);
    });

    it('fails validation when password is too short', function () {
        $user = User::factory()->create(['email' => 'john.doe@example.com']);
        $token = Password::createToken($user);

        $payload = [
            'token' => $token,
            'email' => 'john.doe@example.com',
            'password' => 'short',
            'password_confirmation' => 'short',
        ];

        $response = $this->postJson('/auth/reset-password', $payload);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => ['password'],
            ]);
    });

    it('fails validation when password confirmation does not match', function () {
        $user = User::factory()->create(['email' => 'john.doe@example.com']);
        $token = Password::createToken($user);

        $payload = [
            'token' => $token,
            'email' => 'john.doe@example.com',
            'password' => 'NewSecurePassword123!',
            'password_confirmation' => 'DifferentPassword123!',
        ];

        $response = $this->postJson('/auth/reset-password', $payload);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => ['password'],
            ]);
    });

    it('returns error for invalid token', function () {
        $user = User::factory()->create(['email' => 'john.doe@example.com']);

        $payload = [
            'token' => 'invalid-token-xyz',
            'email' => 'john.doe@example.com',
            'password' => 'NewSecurePassword123!',
            'password_confirmation' => 'NewSecurePassword123!',
        ];

        $response = $this->postJson('/auth/reset-password', $payload);

        $response->assertStatus(400)
            ->assertJsonStructure(['error']);
    });

    it('returns error for expired token', function () {
        $user = User::factory()->create(['email' => 'john.doe@example.com']);
        $token = Password::createToken($user);

        // Simulate expired token by manipulating the database
        \DB::table('password_reset_tokens')
            ->where('email', 'john.doe@example.com')
            ->update(['created_at' => now()->subHours(2)]); // Assuming 1 hour expiry

        $payload = [
            'token' => $token,
            'email' => 'john.doe@example.com',
            'password' => 'NewSecurePassword123!',
            'password_confirmation' => 'NewSecurePassword123!',
        ];

        $response = $this->postJson('/auth/reset-password', $payload);

        $response->assertStatus(422)
            ->assertJsonStructure(['message', 'errors']);
    });

    it('returns error when email does not match token', function () {
        $user = User::factory()->create(['email' => 'john.doe@example.com']);
        $token = Password::createToken($user);

        $payload = [
            'token' => $token,
            'email' => 'different@example.com',
            'password' => 'NewSecurePassword123!',
            'password_confirmation' => 'NewSecurePassword123!',
        ];

        $response = $this->postJson('/auth/reset-password', $payload);

        $response->assertStatus(400)
            ->assertJsonStructure(['error']);
    });

    it('cannot reuse same token after password reset', function () {
        $user = User::factory()->create(['email' => 'john.doe@example.com']);
        $token = Password::createToken($user);

        $payload = [
            'token' => $token,
            'email' => 'john.doe@example.com',
            'password' => 'NewSecurePassword123!',
            'password_confirmation' => 'NewSecurePassword123!',
        ];

        // First reset
        $this->postJson('/auth/reset-password', $payload);

        // Try to reuse the same token
        $secondResponse = $this->postJson('/auth/reset-password', $payload);

        $secondResponse->assertStatus(400);
    });

    it('handles server error gracefully', function () {
        $this->mockDatabaseFailure();

        $payload = [
            'token' => 'some-token',
            'email' => 'john.doe@example.com',
            'password' => 'NewSecurePassword123!',
            'password_confirmation' => 'NewSecurePassword123!',
        ];

        $response = $this->postJson('/auth/reset-password', $payload);

        $response->assertStatus(500)
            ->assertJsonStructure(['error']);
    });

    it('is case-insensitive for email', function () {
        $user = User::factory()->create(['email' => 'John.Doe@example.com']);
        $token = Password::createToken($user);

        $payload = [
            'token' => $token,
            'email' => 'john.doe@EXAMPLE.COM',
            'password' => 'NewSecurePassword123!',
            'password_confirmation' => 'NewSecurePassword123!',
        ];

        $response = $this->postJson('/auth/reset-password', $payload);

        $response->assertStatus(200);
    });
});

// Helper functions for test setup
function mockMailerFailure(): void
{
    // Mock mailer failure for error handling tests
    // Implementation depends on your testing approach
}

function mockDatabaseFailure(): void
{
    // Mock database failure for error handling tests
    // Implementation depends on your testing approach
}
