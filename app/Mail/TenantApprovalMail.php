<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TenantApprovalMail extends Mailable
{
    use Queueable, SerializesModels;

    public $tenant;
    public $password;

    public function __construct($tenant, $password)
    {
        $this->tenant = $tenant;
        $this->password = $password;
    }

    public function build()
    {
        return $this->markdown('emails.tenant.approved')
                    ->subject('Your Tenant Application Has Been Approved');
    }
}
