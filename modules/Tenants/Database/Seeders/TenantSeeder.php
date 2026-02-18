<?php

declare(strict_types=1);

namespace Modules\Tenants\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Tenants\Domain\Models\Tenant;

class TenantSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->environment('production')) {
            if ($this->command) {
                $this->command->warn('Skipping tenant seeding in production environment.');
            }

            return;
        }

        $this->createActiveTenants();
        $this->createDisabledTenants();
    }

    private function createActiveTenants(): void
    {
        $tenants = [
            ['name' => 'Grand Hotel',   'slug' => 'grand-hotel',   'email' => 'contact@grandhotel.example.com',   'domains' => ['grand-hotel.myapp.test', 'grandhotel.localhost']],
            ['name' => 'Sunset Resort', 'slug' => 'sunset-resort', 'email' => 'contact@sunsetresort.example.com', 'domains' => ['sunset-resort.myapp.test', 'sunset.localhost']],
            ['name' => 'City Inn',      'slug' => 'city-inn',      'email' => 'contact@cityinn.example.com',      'domains' => ['city-inn.myapp.test', 'cityinn.localhost']],
        ];

        foreach ($tenants as $data) {
            $this->seed($data, 'active');
        }
    }

    private function createDisabledTenants(): void
    {
        $tenants = [
            ['name' => 'Ocean View Hotel', 'slug' => 'ocean-view',     'email' => 'contact@oceanview.example.com',     'domains' => ['ocean-view.myapp.test']],
            ['name' => 'Mountain Lodge',   'slug' => 'mountain-lodge', 'email' => 'contact@mountainlodge.example.com', 'domains' => ['mountain-lodge.myapp.test']],
        ];

        foreach ($tenants as $data) {
            $this->seed($data, 'disabled');
        }
    }

    /** @param array<string, mixed> $data */
    private function seed(array $data, string $status): void
    {
        if (Tenant::where('slug', $data['slug'])->exists()) {
            return;
        }

        $domains = $data['domains'];
        unset($data['domains']);

        Tenant::factory()
            ->withSlug($data['slug'])
            ->forOwner(1)
            ->atLocation(1)
            ->withDomains($domains)
            ->{$status}()
            ->create([
                'name' => $data['name'],
                'email' => $data['email'],
                'status' => $status,
            ]);
    }
}
