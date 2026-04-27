<?php

namespace App\Models;

class SystemFeedback extends CentralModel
{
    protected $table = 'system_feedback';

    protected $fillable = [
        'tenant_id',
        'submitted_by',
        'rating',
        'category',
        'message',
    ];

    protected $casts = [
        'rating' => 'integer',
    ];

    public static function categories(): array
    {
        return [
            'general'     => 'General',
            'ui'          => 'User Interface',
            'performance' => 'Performance',
            'feature'     => 'Feature Request',
            'bug'         => 'Bug Report',
        ];
    }

    public function starLabel(): string
    {
        return match ($this->rating) {
            5 => 'Excellent',
            4 => 'Good',
            3 => 'Average',
            2 => 'Poor',
            1 => 'Very Poor',
            default => 'Unknown',
        };
    }
}
