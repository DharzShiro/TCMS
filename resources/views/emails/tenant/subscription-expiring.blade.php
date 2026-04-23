@component('mail::message')

<div style="text-align: center; margin-bottom: 24px;">
    @if($daysLeft <= 1)
        <h1 style="color: #CE1126; font-size: 24px; font-weight: 800; margin-bottom: 4px;">
            🔴 Urgent: Subscription Expiring Today!
        </h1>
    @elseif($daysLeft <= 3)
        <h1 style="color: #b38a00; font-size: 24px; font-weight: 800; margin-bottom: 4px;">
            ⚠️ Subscription Expiring Very Soon
        </h1>
    @else
        <h1 style="color: #003087; font-size: 24px; font-weight: 800; margin-bottom: 4px;">
            📢 Subscription Renewal Reminder
        </h1>
    @endif
    <p style="color: #5a7aaa; font-size: 14px;">
        {{ config('app.name') }} — Subscription Notice
    </p>
</div>

---

Dear **{{ $tenant->name }}**,

@if($daysLeft <= 1)
This is an **urgent notice**. Your subscription expires **today**. Please renew immediately to avoid any interruption to your service.
@elseif($daysLeft <= 3)
Your subscription is expiring **very soon** — in just **{{ $daysLeft }} day(s)**. Please renew as soon as possible to avoid any service interruption.
@else
This is a friendly reminder that your **{{ config('app.name') }}** subscription is expiring in **{{ $daysLeft }} day(s)**. Please renew before the expiry date to ensure uninterrupted access.
@endif

@component('mail::panel')
**🏢 Organization:** {{ $tenant->name }}
**📋 Plan:** {{ ucfirst($tenant->subscription) }} Plan
**📅 Expiry Date:** {{ $tenant->expires_at->format('F d, Y') }}
**⏳ Days Remaining:** {{ $daysLeft }} day(s)
@endcomponent

@if($daysLeft <= 3)
> 🚨 **Action Required:** Your access to all features will be suspended once your subscription expires. Renew now to keep your training center running without interruption.
@else
> 💡 **Tip:** Renewing early ensures your trainees, trainers, and courses remain fully accessible without any downtime.
@endif

@component('mail::button', ['url' => 'http://' . $tenant->subdomain . '.tcm.com:8000/admin/subscription', 'color' => $daysLeft <= 3 ? 'red' : 'blue'])
Renew My Subscription
@endcomponent

**What happens if I don't renew?**

Once your subscription expires, your account will be locked and your team will lose access to:
- Trainee enrollment and attendance records
- Course management and training schedules
- Assessments and certifications
- All reports and exports

Your data will be **safely retained** — you just won't be able to access it until you renew.

If you have any questions or need assistance with renewal, please contact our support team.

Thanks,
**{{ config('app.name') }} Team**

---
<p style="font-size: 12px; color: #9aaccc;">
    You are receiving this email because you are the administrator of {{ $tenant->name }} on {{ config('app.name') }}.
</p>

@endcomponent
