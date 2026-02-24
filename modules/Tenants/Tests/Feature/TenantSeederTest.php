<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Event;
use Modules\Tenants\Database\Seeders\TenantSeeder;
use Modules\Tenants\Domain\Models\Tenant;
use Stancl\Tenancy\Events\TenantCreated;

beforeEach(function () {
    // Prevent stancl/tenancy from trying to provision tenant databases during tests
    Event::fake([TenantCreated::class]);

    (new TenantSeeder)->run();
});

describe('tenant count and distribution', function () {
    it('seeds five tenants in total', function () {
        expect(Tenant::all())->toHaveCount(5);
    });

    it('seeds three active tenants with expected slugs', function () {
        $active = Tenant::where('status', 'active')->get();

        expect($active)->toHaveCount(3)
            ->and($active->pluck('slug')->toArray())
            ->toMatchArray(['grand-hotel', 'sunset-resort', 'city-inn']);
    });

    it('seeds two disabled tenants with expected slugs', function () {
        $disabled = Tenant::where('status', 'disabled')->get();

        expect($disabled)->toHaveCount(2)
            ->and($disabled->pluck('slug')->toArray())
            ->toMatchArray(['ocean-view', 'mountain-lodge']);
    });
});

describe('data integrity', function () {
    it('seeds tenants with unique emails', function () {
        $emails = Tenant::pluck('email');

        expect($emails->unique())->toHaveCount(5);
    });

    it('seeds tenants with unique slugs', function () {
        $slugs = Tenant::pluck('slug');

        expect($slugs->unique())->toHaveCount(5);
    });

    it('seeds every tenant with required fields', function () {
        Tenant::all()->each(function (Tenant $tenant) {
            expect($tenant->name)->not->toBeNull()
                ->and($tenant->email)->not->toBeNull()
                ->and($tenant->slug)->not->toBeNull()
                ->and($tenant->owner_id)->toBe(1)
                ->and($tenant->location_id)->toBe(1)
                ->and($tenant->status)->toBeIn(['active', 'disabled']);
        });
    });
});

describe('specific tenant data', function () {
    it('seeds Grand Hotel with correct attributes', function () {
        $grandHotel = Tenant::where('slug', 'grand-hotel')->first();

        expect($grandHotel)->not->toBeNull()
            ->and($grandHotel->name)->toBe('Grand Hotel')
            ->and($grandHotel->email)->toBe('contact@grandhotel.example.com')
            ->and($grandHotel->status)->toBe('active')
            ->and($grandHotel->owner_id)->toBe(1)
            ->and($grandHotel->location_id)->toBe(1);
    });

    it('seeds Grand Hotel with both domains', function () {
        $grandHotel = Tenant::where('slug', 'grand-hotel')->first();
        $domains = $grandHotel->domains->pluck('domain')->toArray();

        expect($grandHotel->domains)->toHaveCount(2)
            ->and($domains)->toContain('grand-hotel.myapp.test')
            ->and($domains)->toContain('grandhotel.localhost');
    });

    it('seeds Ocean View as disabled', function () {
        $oceanView = Tenant::where('slug', 'ocean-view')->first();

        expect($oceanView)->not->toBeNull()
            ->and($oceanView->status)->toBe('disabled')
            ->and($oceanView->isDisabled())->toBeTrue()
            ->and($oceanView->isActive())->toBeFalse();
    });
});

describe('idempotency', function () {
    it('does not create duplicates when run twice', function () {
        // seeder already ran once in beforeEach; run it again
        (new TenantSeeder)->run();

        expect(Tenant::all())->toHaveCount(5);
    });
});
