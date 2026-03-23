<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Mail;
use Modules\Staff\Domain\Enums\StaffRole;
use Modules\Staff\Domain\Mail\StaffSetupMail;

beforeEach(function () {
    seedStaffRolesAndPermissions();
    Mail::fake();
});

describe('POST /api/v1/staff/{staff_id}/resend-setup', function () {

    it('resends setup email for inactive non-activated staff', function () {
        $admin = createAdminStaff();
        $staff = createInactiveStaff(['email' => 'pending@hotel.test']);

        $response = $this->actingAs($admin, 'tenant')
            ->postJson("/api/v1/staff/{$staff->id}/resend-setup");

        $response->assertOk()
            ->assertJson(['message' => 'Setup email resent successfully.']);

        Mail::assertQueued(StaffSetupMail::class, fn ($mail) => $mail->hasTo('pending@hotel.test'));
    });

    it('generates a fresh setup token replacing the old one', function () {
        $admin = createAdminStaff();
        $staff = createInactiveStaff(['email' => 'refresh@hotel.test']);
        $oldToken = $staff->setup_token;

        $response = $this->actingAs($admin, 'tenant')
            ->postJson("/api/v1/staff/{$staff->id}/resend-setup");

        $response->assertOk();

        $staff->refresh();

        expect($staff->setup_token)->not->toBe($oldToken)
            ->and($staff->setup_token)->not->toBeNull()
            ->and($staff->setup_token_expires_at->isFuture())->toBeTrue();
    });

    it('rejects resend for already activated staff', function () {
        $admin = createAdminStaff();
        $activeStaff = createTenantUser(['email' => 'active@hotel.test']);

        $response = $this->actingAs($admin, 'tenant')
            ->postJson("/api/v1/staff/{$activeStaff->id}/resend-setup");

        $response->assertStatus(409)
            ->assertJson(['error' => 'Staff member is already activated.']);

        Mail::assertNotQueued(StaffSetupMail::class);
    });

    it('returns 404 for non-existent staff member', function () {
        $admin = createAdminStaff();

        $this->actingAs($admin, 'tenant')
            ->postJson('/api/v1/staff/99999/resend-setup')
            ->assertNotFound();
    });

    it('rejects non-authorized actor', function (string $scenario, Closure $actorSetup, int $expectedStatus) {
        $actor = $actorSetup();
        $target = createInactiveStaff(['email' => 'target@hotel.test']);

        $request = $actor
            ? $this->actingAs($actor, 'tenant')
            : $this;

        $request->postJson("/api/v1/staff/{$target->id}/resend-setup")
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
