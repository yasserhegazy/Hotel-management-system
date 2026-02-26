<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to {{ config('app.name') }}</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; background-color: #f9fafb;">

    {{-- Header --}}
    <div style="background: linear-gradient(135deg, #059669 0%, #047857 100%); padding: 32px 30px; border-radius: 12px 12px 0 0; text-align: center;">
        <h1 style="color: #ffffff; margin: 0; font-size: 26px; font-weight: 700; letter-spacing: -0.3px;">
            Welcome to {{ config('app.name') }}! 🎉
        </h1>
        <p style="color: #d1fae5; margin: 8px 0 0; font-size: 14px;">
            Your hotel is live and ready to go
        </p>
    </div>

    {{-- Body --}}
    <div style="background-color: #ffffff; padding: 36px 30px; border-left: 1px solid #e5e7eb; border-right: 1px solid #e5e7eb;">
        <p style="margin-top: 0;">Hello <strong>{{ $ownerName }}</strong>,</p>

        <p>
            Congratulations! <strong>{{ $tenant->name }}</strong> has been successfully registered and is now active on {{ config('app.name') }}.
        </p>

        {{-- Account details card --}}
        <div style="background-color: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 20px 24px; margin: 24px 0;">
            <h2 style="color: #047857; margin: 0 0 14px; font-size: 16px; font-weight: 700;">
                Your Account Details
            </h2>
            <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
                <tr>
                    <td style="color: #6b7280; padding: 4px 0; width: 120px;">Hotel Name</td>
                    <td style="color: #111827; font-weight: 600;">{{ $tenant->name }}</td>
                </tr>
                <tr>
                    <td style="color: #6b7280; padding: 4px 0;">Email</td>
                    <td style="color: #111827;">{{ $tenant->email }}</td>
                </tr>
                <tr>
                    <td style="color: #6b7280; padding: 4px 0;">Status</td>
                    <td>
                        <span style="background-color: #d1fae5; color: #047857; padding: 2px 10px; border-radius: 9999px; font-size: 12px; font-weight: 600;">
                            Active
                        </span>
                    </td>
                </tr>
            </table>
        </div>

        {{-- What's next --}}
        <h3 style="color: #059669; font-size: 15px; margin: 24px 0 12px;">What's next?</h3>
        <ul style="padding-left: 20px; color: #374151; margin: 0 0 24px;">
            <li style="margin-bottom: 6px;">Complete your hotel profile</li>
            <li style="margin-bottom: 6px;">Add your rooms and amenities</li>
            <li style="margin-bottom: 6px;">Configure your booking settings</li>
            <li style="margin-bottom: 6px;">Start accepting reservations</li>
        </ul>

        <div style="text-align: center; margin: 32px 0 8px;">
            <a href="{{ config('app.url') }}"
               style="background-color: #059669; color: #ffffff; padding: 14px 36px; text-decoration: none; border-radius: 8px; display: inline-block; font-weight: 700; font-size: 15px; letter-spacing: 0.2px;">
                Go to Dashboard
            </a>
        </div>

        <p style="margin-top: 24px;">
            If you have any questions, feel free to reach out to our support team.
        </p>

        <p style="margin-bottom: 0;">
            Best regards,<br>
            <strong>The {{ config('app.name') }} Team</strong>
        </p>
    </div>

    {{-- Footer --}}
    <div style="background-color: #f9fafb; padding: 20px 30px; border: 1px solid #e5e7eb; border-top: none; border-radius: 0 0 12px 12px;">
        <hr style="border: none; border-top: 1px solid #e5e7eb; margin: 0 0 16px;">
        <p style="color: #9ca3af; font-size: 12px; margin: 0;">
            © {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
        </p>
    </div>

</body>
</html>

