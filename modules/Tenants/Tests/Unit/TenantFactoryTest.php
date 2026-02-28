<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Tenants\Domain\Models\Tenant;

uses(Tests\TestCase::class, RefreshDatabase::class);

describe('basic factory creation', function () {
    it('creates a tenant with explicit attributes', function () {
        $tenant = Tenant::factory()->create([
            'name' => 'Test Hotel',
            'email' => 'test@hotel.com',
            'slug' => 'test-hotel',
        ]);

        expect($tenant)->toBeInstanceOf(Tenant::class)
            ->and($tenant->name)->toBe('Test Hotel')
            ->and($tenant->email)->toBe('test@hotel.com')
            ->and($tenant->slug)->toBe('test-hotel')
            ->and($tenant->status->value)->toBe('active');
    });

    it('creates multiple tenants with unique emails and slugs', function () {
        $tenants = Tenant::factory()->count(5)->create();

        expect($tenants)->toHaveCount(5)
            ->and($tenants->pluck('email')->unique())->toHaveCount(5)
            ->and($tenants->pluck('slug')->unique())->toHaveCount(5);
    });
});

describe('status states', function () {
    it('creates an active tenant via ->active()', function () {
        $tenant = Tenant::factory()->active()->create();

        expect($tenant->status->value)->toBe('active')
            ->and($tenant->isActive())->toBeTrue()
            ->and($tenant->isDisabled())->toBeFalse();
    });

    it('creates a disabled tenant via ->disabled()', function () {
        $tenant = Tenant::factory()->disabled()->create();

        expect($tenant->status->value)->toBe('disabled')
            ->and($tenant->isDisabled())->toBeTrue()
            ->and($tenant->isActive())->toBeFalse();
    });
});

describe('fluent assignment methods', function () {
    it('assigns a specific owner via ->forOwner()', function ($ownerId) {
        $tenant = Tenant::factory()->forOwner($ownerId)->create();

        expect($tenant->owner_id)->toBe($ownerId);
    })->with([
        'owner 1' => [1],
        'owner 42' => [42],
        'owner 99' => [99],
    ]);

    it('assigns a specific location via ->atLocation()', function ($locationId) {
        $tenant = Tenant::factory()->atLocation($locationId)->create();

        expect($tenant->location_id)->toBe($locationId);
    })->with([
        'location 1' => [1],
        'location 5' => [5],
    ]);

    it('sets a custom slug via ->withSlug()', function () {
        $tenant = Tenant::factory()->withSlug('custom-slug')->create();

        expect($tenant->slug)->toBe('custom-slug');
    });

    it('attaches domains via ->withDomains()', function () {
        $domains = ['test1.myapp.test', 'test2.myapp.test'];
        $tenant = Tenant::factory()->withDomains($domains)->create();

        expect($tenant->domains)->toHaveCount(2)
            ->and($tenant->domains->pluck('domain')->toArray())->toBe($domains);
    });
});

describe('database uniqueness constraints', function () {
    it('rejects a duplicate email', function () {
        Tenant::factory()->create(['email' => 'unique@test.com']);

        expect(fn () => Tenant::factory()->create(['email' => 'unique@test.com']))
            ->toThrow(\Illuminate\Database\QueryException::class);
    });

    it('rejects a duplicate slug', function () {
        Tenant::factory()->create(['slug' => 'unique-slug']);

        expect(fn () => Tenant::factory()->create(['slug' => 'unique-slug']))
            ->toThrow(\Illuminate\Database\QueryException::class);
    });
});
