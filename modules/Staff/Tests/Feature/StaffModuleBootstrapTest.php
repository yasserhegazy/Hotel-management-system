<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Modules\Staff\Domain\Models\TenantUser;

describe('Staff Module Bootstrap', function () {

    it('has the tenant guard configured', function () {
        $guard = config('auth.guards.tenant');

        expect($guard)->not->toBeNull()
            ->and($guard['driver'])->toBe('session')
            ->and($guard['provider'])->toBe('tenant_users');
    });

    it('has the tenant_users provider configured', function () {
        $provider = config('auth.providers.tenant_users');

        expect($provider)->not->toBeNull()
            ->and($provider['driver'])->toBe('eloquent')
            ->and($provider['model'])->toBe(TenantUser::class);
    });

    it('includes tenant guard in sanctum guards', function () {
        $guards = config('sanctum.guard');

        expect($guards)->toContain('tenant');
    });

    it('has the tenant_users table with all required columns', function () {
        expect(Schema::hasTable('tenant_users'))->toBeTrue();

        $expectedColumns = [
            'id', 'first_name', 'last_name', 'email', 'phone',
            'password', 'email_verified_at', 'preferred_language',
            'last_login_at', 'is_active', 'activated_at',
            'setup_token', 'setup_token_expires_at',
            'remember_token', 'created_at', 'updated_at',
        ];

        foreach ($expectedColumns as $column) {
            expect(Schema::hasColumn('tenant_users', $column))
                ->toBeTrue("Missing column: {$column}");
        }
    });

    it('creates an active tenant user via factory', function () {
        $user = TenantUser::factory()->create();

        expect($user)->toBeInstanceOf(TenantUser::class)
            ->and($user->is_active)->toBeTrue()
            ->and($user->activated_at)->not->toBeNull()
            ->and($user->setup_token)->toBeNull()
            ->and($user->password)->not->toBeNull();

        $this->assertDatabaseHas('tenant_users', [
            'id' => $user->id,
            'email' => $user->email,
        ]);
    });

    it('creates an inactive staff via factory with setup token', function () {
        $user = TenantUser::factory()->inactive()->create();

        expect($user->is_active)->toBeFalse()
            ->and($user->activated_at)->toBeNull()
            ->and($user->password)->toBeNull()
            ->and($user->setup_token)->not->toBeNull()
            ->and($user->setup_token_expires_at)->not->toBeNull()
            ->and($user->plain_setup_token)->toBeString();

        expect(hash('sha256', $user->plain_setup_token))
            ->toBe($user->getRawOriginal('setup_token'));
    });

    it('creates a staff with expired setup token via factory', function () {
        $user = TenantUser::factory()->expiredToken()->create();

        expect($user->is_active)->toBeFalse()
            ->and($user->setup_token_expires_at->isPast())->toBeTrue()
            ->and($user->hasValidSetupToken())->toBeFalse();
    });

    it('creates a deactivated staff via factory', function () {
        $user = TenantUser::factory()->deactivated()->create();

        expect($user->is_active)->toBeFalse()
            ->and($user->activated_at)->not->toBeNull()
            ->and($user->setup_token)->toBeNull()
            ->and($user->isActivated())->toBeFalse();
    });

    it('hides sensitive attributes on serialization', function () {
        $user = TenantUser::factory()->create();
        $array = $user->toArray();

        expect($array)->not->toHaveKey('password')
            ->and($array)->not->toHaveKey('remember_token')
            ->and($array)->not->toHaveKey('setup_token');
    });
});
