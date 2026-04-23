@component('mail::message')

{{-- Header Logo/Title --}}
<div style="text-align: center; margin-bottom: 24px;">
    <h1 style="color: #003087; font-size: 24px; font-weight: 800; margin-bottom: 4px;">
        🎉 Application Approved!
    </h1>
    <p style="color: #5a7aaa; font-size: 14px;">
        Welcome to {{ config('app.name') }}
    </p>
</div>

---

Dear **{{ $tenant->name }}**,

We are pleased to inform you that your training center registration on **{{ config('app.name') }}** has been **approved**! Your account is now active and ready to use.

Here are your login credentials:

@component('mail::panel')
**📧 Admin Email:** {{ $tenant->admin_email }}
**🔐 Temporary Password:** `{{ $password }}`
**🌐 Subdomain:** {{ $tenant->subdomain }}.tcm.com
**📋 Subscription Plan:** {{ ucfirst($tenant->subscription) }}
**📅 Expires At:** {{ $tenant->expires_at ? $tenant->expires_at->format('F d, Y') : 'N/A' }}
@endcomponent

@component('mail::button', ['url' => 'http://' . $tenant->subdomain . '.tcm.com:8000/login', 'color' => 'blue'])
Login to Your Account
@endcomponent

> ⚠️ **Important:** Please log in and **change your password immediately** after your first login. Do not share your credentials with anyone.

**Getting Started:**
- Log in using the credentials above
- Change your password on first login
- Set up your courses and trainers
- Start enrolling trainees

If you have any questions or need assistance, feel free to contact our support team.

Thanks,
**{{ config('app.name') }} Team**

@endcomponent