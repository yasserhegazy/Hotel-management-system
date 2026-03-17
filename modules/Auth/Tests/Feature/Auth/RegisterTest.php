<?php

use App\Models\User;

describe('POST /api/auth/register', function () {
    it('successfully registers new user', function () {
        $response = $this->postJson('/api/auth/register', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
            'password' => 'SecurePassword123!',
            'password_confirmation' => 'SecurePassword123!',
            'phone' => '+1234567890',
            'preferred_language' => 'en',
        ]);

        $response->assertCreated()
            ->assertJsonStructure([
                'id',
                'first_name',
                'last_name',
                'email',
                'phone',
                'preferred_language',
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'john.doe@example.com',
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);
    });

    it('validates required fields', function ($field, $payload) {
        $response = $this->postJson('/api/auth/register', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors($field);
    })->with([
        'missing first_name' => ['first_name', [
            'last_name' => 'Doe',
            'email' => 'test@test.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]],
        'missing email' => ['email', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]],
        'invalid email' => ['email', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'invalid-email',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]],
        'password too short' => ['password', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'test@test.com',
            'password' => 'short',
            'password_confirmation' => 'short',
        ]],
        'password mismatch' => ['password', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'test@test.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Different123!',
        ]],
    ]);

    it('prevents duplicate email registration', function () {
        User::factory()->create(['email' => 'existing@example.com']);

        $response = $this->postJson('/api/auth/register', [
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'email' => 'existing@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('email');
    });

    it('hashes password before storing', function () {
        $this->postJson('/api/auth/register', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'password' => 'SecurePassword123!',
            'password_confirmation' => 'SecurePassword123!',
        ]);

        $user = User::where('email', 'john@example.com')->first();

        expect($user->password)->not->toBe('SecurePassword123!');
        expect(password_verify('SecurePassword123!', $user->password))->toBeTrue();
    });

    it('authenticates user after registration', function () {
        $response = $this->postJson('/api/auth/register', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'password' => 'SecurePassword123!',
            'password_confirmation' => 'SecurePassword123!',
        ]);

        $response->assertCreated();
        $this->assertAuthenticated();
    });

    it('sets default preferred_language to en when not provided', function () {
        $response = $this->postJson('/api/auth/register', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'password' => 'SecurePassword123!',
            'password_confirmation' => 'SecurePassword123!',
        ]);

        $response->assertCreated()
            ->assertJson(['preferred_language' => 'en']);
    });
});
