<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

describe('POST /api/auth/login', function () {
    it('logs in user with valid credentials', function () {
        $user = User::factory()->create([
            'email' => 'john.doe@example.com',
            'password' => ('SecurePassword123!'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'john.doe@example.com',
            'password' => 'SecurePassword123!',
        ]);

        $response->assertOk()
            ->assertJsonStructure(['user' => ['id', 'email']]);

        $this->assertAuthenticatedAs($user);
    });

    it('validates required fields', function ($field, $payload) {
        $response = $this->postJson('/api/auth/login', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors($field);
    })->with([
        'missing email' => ['email', ['password' => 'Password123!']],
        'missing password' => ['password', ['email' => 'test@test.com']],
        'invalid email' => ['email', ['email' => 'invalid', 'password' => 'Password123!']],
    ]);

    it('rejects invalid credentials', function ($email, $password) {
        User::factory()->create([
            'email' => 'john.doe@example.com',
            'password' => Hash::make('SecurePassword123!'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => $email,
            'password' => $password,
        ]);

        $response->assertUnauthorized()
            ->assertJson(['error' => 'Invalid credentials.']);

        $this->assertGuest();
    })->with([
        'wrong email' => ['wrong@example.com', 'SecurePassword123!'],
        'wrong password' => ['john.doe@example.com', 'WrongPassword123!'],
        'non-existent user' => ['nonexistent@example.com', 'SecurePassword123!'],
    ]);

    it('does not expose password in response', function () {
        $user = User::factory()->create([
            'email' => 'john@test.com',
            'password' => Hash::make('Password123!'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'john@test.com',
            'password' => 'Password123!',
        ]);

        expect($response->json('user'))->not->toHaveKey('password');
    });
});
