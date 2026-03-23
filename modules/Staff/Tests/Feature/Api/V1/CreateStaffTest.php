<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Mail;
use Modules\Staff\Domain\Enums\StaffRole;
use Modules\Staff\Domain\Mail\StaffSetupMail;
use Modules\Staff\Domain\Models\TenantUser;

beforeEach(function () {
    seedStaffRolesAndPermissions();
    Mail::fake();
});

describe('POST /api/v1/staff', function () {

    it('creates staff member with a single role', function () {
        $admin = createAdminStaff();

        $payload = [
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'email' => 'jane@hotel.test',
            'phone' => '+1234567890',
            'roles' => [StaffRole::Receptionist->value],
        ];

        $response = $this->actingAs($admin, 'tenant')
            ->postJson('/api/v1/staff', $payload);

        $response->assertCreated()
            ->assertJsonStructure([
                'message',
                'data' => [
                    'id',
                    'first_name',
                    'last_name',
                    'email',
                    'phone',
                    'preferred_language',
                    'is_active',
                    'activated_at',
                    'last_login_at',
                    'roles',
                    'created_at',
                    'updated_at',
                ],
            ])
            ->assertJsonPath('data.email', 'jane@hotel.test')
            ->assertJsonPath('data.roles', ['receptionist']);

        $this->assertDatabaseHas('tenant_users', ['email' => 'jane@hotel.test']);
    });

    it('creates staff member with multiple roles', function () {
        $admin = createAdminStaff();

        $payload = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@hotel.test',
            'phone' => '+1234567890',
            'roles' => [StaffRole::Receptionist->value, StaffRole::Housekeeper->value],
        ];

        $response = $this->actingAs($admin, 'tenant')
            ->postJson('/api/v1/staff', $payload);

        $response->assertCreated();

        $roles = $response->json('data.roles');
        expect($roles)->toContain('receptionist');
        expect($roles)->toContain('housekeeper');
    });

    it('creates staff as inactive with no password initially', function () {
        $admin = createAdminStaff();

        $payload = [
            'first_name' => 'New',
            'last_name' => 'Staff',
            'email' => 'newstaff@hotel.test',
            'phone' => '+1234567890',
            'roles' => [StaffRole::Receptionist->value],
        ];

        $response = $this->actingAs($admin, 'tenant')
            ->postJson('/api/v1/staff', $payload);

        $response->assertCreated()
            ->assertJsonPath('data.is_active', false)
            ->assertJsonPath('data.activated_at', null);

        $staff = TenantUser::where('email', 'newstaff@hotel.test')->first();
        expect($staff->password)->toBeNull();
    });

    it('generates a password setup token on creation', function () {
        $admin = createAdminStaff();

        $payload = [
            'first_name' => 'Token',
            'last_name' => 'Staff',
            'email' => 'tokenstaff@hotel.test',
            'phone' => '+1234567890',
            'roles' => [StaffRole::Receptionist->value],
        ];

        $this->actingAs($admin, 'tenant')
            ->postJson('/api/v1/staff', $payload)
            ->assertCreated();

        $staff = TenantUser::where('email', 'tokenstaff@hotel.test')->first();

        expect($staff->setup_token)->not->toBeNull();
        expect($staff->setup_token_expires_at)->not->toBeNull();
        expect($staff->setup_token_expires_at->isFuture())->toBeTrue();
    });

    it('dispatches setup email to new staff member', function () {
        $admin = createAdminStaff();

        $payload = [
            'first_name' => 'Email',
            'last_name' => 'Staff',
            'email' => 'newstaff@hotel.test',
            'phone' => '+1234567890',
            'roles' => [StaffRole::Receptionist->value],
        ];

        $this->actingAs($admin, 'tenant')
            ->postJson('/api/v1/staff', $payload)
            ->assertCreated();

        Mail::assertQueued(StaffSetupMail::class, fn ($mail) => $mail->hasTo('newstaff@hotel.test'));
    });

    it('rejects duplicate tenant email', function () {
        $admin = createAdminStaff();

        createTenantUser(['email' => 'existing@hotel.test']);

        $payload = [
            'first_name' => 'Duplicate',
            'last_name' => 'Email',
            'email' => 'existing@hotel.test',
            'phone' => '+1234567890',
            'roles' => [StaffRole::Receptionist->value],
        ];

        $this->actingAs($admin, 'tenant')
            ->postJson('/api/v1/staff', $payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors('email');
    });

    it('rejects invalid or missing roles', function (string $scenario, array $payload) {
        $admin = createAdminStaff();

        $this->actingAs($admin, 'tenant')
            ->postJson('/api/v1/staff', $payload)
            ->assertStatus(422);
    })->with([
        'missing roles field' => ['missing roles field', [
            'first_name' => 'Test', 'last_name' => 'User',
            'email' => 'test@hotel.test',
        ]],
        'empty roles array' => ['empty roles array', [
            'first_name' => 'Test', 'last_name' => 'User',
            'email' => 'test@hotel.test', 'roles' => [],
        ]],
        'invalid role name' => ['invalid role name', [
            'first_name' => 'Test', 'last_name' => 'User',
            'email' => 'test@hotel.test', 'roles' => ['nonexistent_role'],
        ]],
    ]);

    it('rejects non-authorized actor', function (string $scenario, Closure $actorSetup, int $expectedStatus) {
        $actor = $actorSetup();

        $payload = [
            'first_name' => 'Unauthorized',
            'last_name' => 'User',
            'email' => 'unauthorized@hotel.test',
            'phone' => '+1234567890',
            'roles' => [StaffRole::Receptionist->value],
        ];

        $request = $actor
            ? $this->actingAs($actor, 'tenant')
            : $this;

        $request->postJson('/api/v1/staff', $payload)
            ->assertStatus($expectedStatus);
    })->with([
        'unauthenticated' => [
            'unauthenticated',
            function () {
                return null;
            },
            401,
        ],
        'without manage_staff permission' => [
            'without manage_staff permission',
            function () {
                $staff = createTenantUser();
                $staff->assignRole(StaffRole::Receptionist);

                return $staff;
            },
            403,
        ],
    ]);
});
