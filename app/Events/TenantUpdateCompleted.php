<?php

namespace App\Events;

use App\Models\SystemRelease;
use App\Models\Tenant;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TenantUpdateCompleted
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Tenant $tenant,
        public readonly SystemRelease $release,
    ) {}
}
