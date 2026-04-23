<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TenantRejectionMail extends Mailable
{
    use Queueable, SerializesModels;

    public $tenant;

    public function __construct($tenant)
    {
        $this->tenant = $tenant;
    }

    public function build()
    {
        return $this->markdown('emails.tenant.rejected')
                    ->subject('Your Tenant Application Status');
    }
}
