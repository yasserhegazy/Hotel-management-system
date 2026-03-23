<?php

declare(strict_types=1);

namespace Modules\Staff\Domain\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Modules\Staff\Domain\Models\TenantUser;

class StaffSetupMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $setupUrl;

    public function __construct(
        public TenantUser $tenantUser,
        public string $plainToken,
    ) {
        $this->to($tenantUser->email);

        $frontendUrl = rtrim(config('app.frontend_url', 'http://localhost:5173'), '/');
        $this->setupUrl = sprintf(
            '%s/staff/setup-password?token=%s&email=%s',
            $frontendUrl,
            urlencode($this->plainToken),
            urlencode($this->tenantUser->email),
        );
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Set Up Your Account',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'staff::emails.setup',
        );
    }
}
