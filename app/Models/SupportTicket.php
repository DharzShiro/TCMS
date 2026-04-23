<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupportTicket extends CentralModel
{
    protected $fillable = [
        'ticket_number',
        'tenant_id',
        'requester_name',
        'requester_email',
        'tenant_user_id',
        'subject',
        'category',
        'status',
        'priority',
        'assignee_id',
        'last_reply_at',
        'last_reply_by',
        'unread_admin',
        'unread_tenant',
    ];

    protected $casts = [
        'last_reply_at' => 'datetime',
        'unread_admin'  => 'integer',
        'unread_tenant' => 'integer',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $ticket) {
            $ticket->ticket_number = static::generateTicketNumber();
        });
    }

    protected static function generateTicketNumber(): string
    {
        $last = static::max('id') ?? 0;
        return 'TKT-' . str_pad($last + 1, 5, '0', STR_PAD_LEFT);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id', 'id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(SupportMessage::class, 'ticket_id')->orderBy('created_at');
    }

    public function latestMessage(): HasMany
    {
        return $this->hasMany(SupportMessage::class, 'ticket_id')->latestOfMany();
    }

    public function isOpen(): bool
    {
        return in_array($this->status, ['open', 'in_progress']);
    }

    public function getCategoryLabelAttribute(): string
    {
        return match ($this->category) {
            'bug_report'      => 'Bug Report',
            'technical_issue' => 'Technical Issue',
            'account_concern' => 'Account Concern',
            'billing_concern' => 'Billing Concern',
            'feature_request' => 'Feature Request',
            'general_inquiry' => 'General Inquiry',
            default           => ucfirst(str_replace('_', ' ', $this->category)),
        };
    }

    public function getPriorityColorAttribute(): string
    {
        return match ($this->priority) {
            'urgent' => '#CE1126',
            'high'   => '#d97706',
            'medium' => '#0057B8',
            'low'    => '#16a34a',
            default  => '#6b7280',
        };
    }
}
