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
        public string $tenantId = '',
    ) {
        $this->to($tenantUser->email);

        $frontendUrl = rtrim(config('app.frontend_url', 'http://localhost:5173'), '/');
        $params = [
            'token' => $this->plainToken,
            'email' => $this->tenantUser->email,
        ];

        if ($this->tenantId !== '') {
            $params['tenant'] = $this->tenantId;
        }

        $this->setupUrl = sprintf(
            '%s/staff/setup-password?%s',
            $frontendUrl,
            http_build_query($params),
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
