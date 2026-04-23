<?php

namespace App\Services\Support;

use App\Models\Notification;
use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class SupportNotificationService
{
    /**
     * Notify all superadmin users that a new ticket was opened.
     */
    public function notifyAdminNewTicket(SupportTicket $ticket): void
    {
        $this->notifyAllSuperAdmins(
            title: "New Support Ticket: {$ticket->ticket_number}",
            message: "[{$ticket->requester_name} @ {$ticket->tenant?->name}] submitted a {$ticket->category_label} ticket: "{$ticket->subject}"",
            link: route('superadmin.support.show', $ticket),
        );
    }

    /**
     * Notify superadmin that a tenant replied on an existing ticket.
     */
    public function notifyAdminOfReply(SupportTicket $ticket): void
    {
        $this->notifyAllSuperAdmins(
            title: "New Reply on {$ticket->ticket_number}",
            message: "{$ticket->requester_name} replied on ticket "{$ticket->subject}"",
            link: route('superadmin.support.show', $ticket),
        );
    }

    /**
     * Notify the tenant (via their tenant-side admin user) that the admin replied.
     * Runs in tenant context — writes to the TENANT's notifications table.
     */
    public function notifyTenantOfReply(SupportTicket $ticket): void
    {
        try {
            $tenant = $ticket->tenant;
            if (! $tenant) return;

            $tenant->run(function () use ($ticket) {
                // Inside tenant DB context — find the admin user by email
                $admin = User::where('email', $ticket->requester_email)->first();

                if ($admin) {
                    Notification::create([
                        'user_id' => $admin->id,
                        'title'   => "Support Reply — {$ticket->ticket_number}",
                        'message' => "The support team replied to your ticket: "{$ticket->subject}"",
                        'link'    => '/admin/support/' . $ticket->id,
                    ]);
                }
            });
        } catch (\Throwable $e) {
            // Never crash a page over notification delivery
            Log::warning('[Support] Could not notify tenant of reply', [
                'ticket_id' => $ticket->id,
                'error'     => $e->getMessage(),
            ]);
        }
    }

    /**
     * Notify the tenant admin that their ticket status changed.
     */
    public function notifyTenantStatusChange(SupportTicket $ticket, string $newStatus): void
    {
        try {
            $tenant = $ticket->tenant;
            if (! $tenant) return;

            $label = ucfirst(str_replace('_', ' ', $newStatus));

            $tenant->run(function () use ($ticket, $label) {
                $admin = User::where('email', $ticket->requester_email)->first();

                if ($admin) {
                    Notification::create([
                        'user_id' => $admin->id,
                        'title'   => "Ticket {$ticket->ticket_number} — {$label}",
                        'message' => "Your support ticket "{$ticket->subject}" is now {$label}.",
                        'link'    => '/admin/support/' . $ticket->id,
                    ]);
                }
            });
        } catch (\Throwable $e) {
            Log::warning('[Support] Could not notify tenant of status change', [
                'ticket_id' => $ticket->id,
                'error'     => $e->getMessage(),
            ]);
        }
    }

    private function notifyAllSuperAdmins(string $title, string $message, string $link): void
    {
        // Write to the CENTRAL notifications table for all superadmin users
        User::all()->each(function (User $user) use ($title, $message, $link) {
            try {
                Notification::create([
                    'user_id' => $user->id,
                    'title'   => $title,
                    'message' => $message,
                    'link'    => $link,
                ]);
            } catch (\Throwable $e) {
                Log::warning('[Support] Notification write failed for user ' . $user->id);
            }
        });
    }
}
