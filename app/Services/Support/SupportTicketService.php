<?php

namespace App\Services\Support;

use App\Models\SupportAttachment;
use App\Models\SupportMessage;
use App\Models\SupportTicket;
use App\Models\Tenant;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SupportTicketService
{
    public function __construct(
        private readonly SupportNotificationService $notifications
    ) {}

    /**
     * Create a new ticket from a tenant admin. $user is the tenant-side User.
     */
    public function createTicket(array $data, Tenant $tenant, object $user): SupportTicket
    {
        return DB::transaction(function () use ($data, $tenant, $user) {
            $ticket = SupportTicket::create([
                'tenant_id'       => $tenant->id,
                'requester_name'  => $user->name,
                'requester_email' => $user->email,
                'tenant_user_id'  => $user->id,
                'subject'         => $data['subject'],
                'category'        => $data['category'],
                'priority'        => $data['priority'] ?? 'medium',
                'status'          => 'open',
                'last_reply_at'   => now(),
                'last_reply_by'   => 'tenant',
                'unread_admin'    => 1,
            ]);

            $message = $this->addMessageToTicket(
                ticket: $ticket,
                body: $data['message'],
                senderType: 'tenant',
                senderName: $user->name,
                senderEmail: $user->email,
                files: $data['attachments'] ?? [],
            );

            $this->notifications->notifyAdminNewTicket($ticket);

            return $ticket;
        });
    }

    /**
     * Add a reply to an existing ticket. Works from both admin and tenant side.
     */
    public function addReply(
        SupportTicket $ticket,
        string $body,
        string $senderType,
        string $senderName,
        string $senderEmail,
        array $files = [],
        bool $isInternal = false,
    ): SupportMessage {
        return DB::transaction(function () use (
            $ticket, $body, $senderType, $senderName, $senderEmail, $files, $isInternal
        ) {
            $message = $this->addMessageToTicket(
                ticket: $ticket,
                body: $body,
                senderType: $senderType,
                senderName: $senderName,
                senderEmail: $senderEmail,
                files: $files,
                isInternal: $isInternal,
            );

            // Update ticket meta
            $update = [
                'last_reply_at' => now(),
                'last_reply_by' => $senderType,
            ];

            if ($senderType === 'admin') {
                // Admin replied → tenant has unread messages
                $update['unread_tenant'] = $ticket->unread_tenant + 1;
                $update['unread_admin']  = 0;

                if ($ticket->status === 'open') {
                    $update['status'] = 'in_progress';
                }

                $this->notifications->notifyTenantOfReply($ticket);
            } else {
                // Tenant replied → admin has unread messages
                $update['unread_admin']  = $ticket->unread_admin + 1;
                $update['unread_tenant'] = 0;
                $this->notifications->notifyAdminOfReply($ticket);
            }

            $ticket->update($update);

            return $message;
        });
    }

    public function updateStatus(SupportTicket $ticket, string $status): void
    {
        $ticket->update(['status' => $status]);
    }

    public function updatePriority(SupportTicket $ticket, string $priority): void
    {
        $ticket->update(['priority' => $priority]);
    }

    public function markReadByAdmin(SupportTicket $ticket): void
    {
        $ticket->update(['unread_admin' => 0]);
    }

    public function markReadByTenant(SupportTicket $ticket): void
    {
        $ticket->update(['unread_tenant' => 0]);
    }

    private function addMessageToTicket(
        SupportTicket $ticket,
        string $body,
        string $senderType,
        string $senderName,
        string $senderEmail,
        array $files = [],
        bool $isInternal = false,
    ): SupportMessage {
        $message = SupportMessage::create([
            'ticket_id'   => $ticket->id,
            'sender_type' => $senderType,
            'sender_name' => $senderName,
            'sender_email' => $senderEmail,
            'body'        => $body,
            'is_internal' => $isInternal,
        ]);

        foreach ($files as $file) {
            if ($file instanceof UploadedFile && $file->isValid()) {
                $this->storeAttachment($message, $file);
            }
        }

        return $message;
    }

    private function storeAttachment(SupportMessage $message, UploadedFile $file): SupportAttachment
    {
        // Use the central 'support' disk — never tenant-scoped — so both
        // superadmin and tenant admin can access the file without needing
        // a tenant storage context.
        $directory = "ticket-{$message->ticket_id}";
        $path      = $file->store($directory, 'support');

        return SupportAttachment::create([
            'message_id'    => $message->id,
            'original_name' => $file->getClientOriginalName(),
            'stored_path'   => $path,
            'mime_type'     => $file->getMimeType(),
            'file_size'     => $file->getSize(),
        ]);
    }

    public function deleteAttachment(SupportAttachment $attachment): void
    {
        Storage::disk('support')->delete($attachment->stored_path);
        $attachment->delete();
    }
}
