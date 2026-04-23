<?php

namespace App\Mail;

use App\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SubscriptionExpiringMail extends Mailable
{
    use Queueable, SerializesModels;

    public Tenant $tenant;
    public int $daysLeft;

    public function __construct(Tenant $tenant, int $daysLeft)
    {
        $this->tenant = $tenant;
        $this->daysLeft = $daysLeft;
    }

    public function build(): static
    {
        $urgency = match(true) {
            $this->daysLeft <= 1 => '🔴 URGENT: ',
            $this->daysLeft <= 3 => '⚠️ ',
            $this->daysLeft <= 7 => '📢 ',
            default              => '',
        };

        return $this
            ->subject("{$urgency}Your Subscription Expires in {$this->daysLeft} Day(s) — " . config('app.name'))
            ->markdown('emails.tenant.subscription-expiring')
            ->with([
                'tenant'   => $this->tenant,
                'daysLeft' => $this->daysLeft,
            ]);
    }
}