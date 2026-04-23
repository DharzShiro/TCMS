@component('mail::message')

{{-- Header --}}
<div style="text-align: center; margin-bottom: 24px;">
    <h1 style="color: #CE1126; font-size: 24px; font-weight: 800; margin-bottom: 4px;">
        Application Update
    </h1>
    <p style="color: #5a7aaa; font-size: 14px;">
        Regarding your {{ config('app.name') }} registration
    </p>
</div>

---

Dear **{{ $tenant->name }}**,

Thank you for your interest in joining **{{ config('app.name') }}**. After carefully reviewing your application, we regret to inform you that your registration has **not been approved** at this time.

@component('mail::panel')
**Organization:** {{ $tenant->name }}
**Admin Email:** {{ $tenant->admin_email }}
**Subdomain Requested:** {{ $tenant->subdomain }}.tcm.com
**Status:** ❌ Not Approved
@endcomponent

**What you can do next:**
- Review your application details and ensure all information is accurate
- Ensure your organization meets TESDA accreditation requirements
- Contact our support team for clarification on the decision
- Re-apply with updated or corrected information if applicable

@component('mail::button', ['url' => 'http://tcm.com:8000', 'color' => 'red'])
Contact Support
@endcomponent

If you believe this decision was made in error or would like further information, please don't hesitate to reach out. We appreciate your interest and hope to work with your organization in the future.

Thanks,
**{{ config('app.name') }} Team**

@endcomponent