<?php

namespace App\Events;

use App\Models\SystemRelease;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewReleasePublished
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly SystemRelease $release) {}
}
