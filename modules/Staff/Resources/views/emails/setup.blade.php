<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Set Up Your Account</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; background-color: #f9fafb;">

    {{-- Header --}}
    <div style="background: linear-gradient(135deg, #059669 0%, #047857 100%); padding: 32px 30px; border-radius: 12px 12px 0 0; text-align: center;">
        <h1 style="color: #ffffff; margin: 0; font-size: 24px; font-weight: 700; letter-spacing: -0.3px;">
            Set Up Your Account
        </h1>
        <p style="color: #d1fae5; margin: 8px 0 0; font-size: 14px;">
            You've been invited to join {{ config('app.name') }}
        </p>
    </div>

    {{-- Body --}}
    <div style="background-color: #ffffff; padding: 36px 30px; border-left: 1px solid #e5e7eb; border-right: 1px solid #e5e7eb;">
        <p style="margin-top: 0;">Hello {{ $tenantUser->first_name }},</p>

        <p>
            An account has been created for you on <strong>{{ config('app.name') }}</strong>.
            Please click the button below to set up your password and activate your account.
        </p>

        <div style="text-align: center; margin: 36px 0;">
            <a href="{{ $setupUrl }}"
               style="background-color: #059669; color: #ffffff; padding: 14px 36px; text-decoration: none; border-radius: 8px; display: inline-block; font-weight: 700; font-size: 15px; letter-spacing: 0.2px;">
                Set Up Password
            </a>
        </div>

        <div style="background-color: #f0fdf4; border-left: 4px solid #059669; padding: 14px 18px; border-radius: 0 6px 6px 0; margin: 24px 0;">
            <p style="margin: 0; color: #047857; font-size: 14px;">
                ⏱ This setup link will expire in <strong>48 hours</strong>.
            </p>
        </div>

        <p style="color: #6b7280; font-size: 14px;">
            If you did not expect this invitation, no further action is required.
        </p>
    </div>

    {{-- Footer --}}
    <div style="background-color: #f9fafb; padding: 20px 30px; border: 1px solid #e5e7eb; border-top: none; border-radius: 0 0 12px 12px;">
        <p style="color: #9ca3af; font-size: 12px; margin: 0 0 8px;">
            If you're having trouble clicking the button, copy and paste this URL into your browser:
        </p>
        <p style="color: #9ca3af; font-size: 12px; word-break: break-all; margin: 0 0 16px;">
            {{ $setupUrl }}
        </p>
        <hr style="border: none; border-top: 1px solid #e5e7eb; margin: 0 0 16px;">
        <p style="color: #9ca3af; font-size: 12px; margin: 0;">
            © {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
        </p>
    </div>

</body>
</html>
