<?php

declare(strict_types=1);

namespace Modules\Staff\Domain\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Envelope;
use Modules\Staff\Domain\Models\TenantUser;

class StaffSetupMail extends Mailable
{
    public function __construct(
        public TenantUser $tenantUser,
        public string $plainToken,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Set Up Your Account',
        );
    }
}
