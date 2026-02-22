<?php

declare(strict_types=1);

namespace Modules\Tenants\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Modules\Tenants\Domain\Models\Tenant;

class TenantVerificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $verificationUrl;

    public function __construct(public Tenant $tenant, public string $token)
    {
        $frontendUrl = config('app.frontend_url', 'http://localhost:5173');
        $this->verificationUrl = sprintf('%s/verify/%s', rtrim($frontendUrl, '/'), $this->token);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Verify Your Hotel Registration',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'tenants::emails.verification',
        );
    }
}
