<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\SupportAttachment;
use App\Models\SupportTicket;
use App\Models\Tenant;
use App\Services\Support\SupportNotificationService;
use App\Services\Support\SupportTicketService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class SuperAdminSupportController extends Controller
{
    public function __construct(
        private readonly SupportTicketService $tickets,
        private readonly SupportNotificationService $notifications,
    ) {}

    public function index(Request $request)
    {
        $query = SupportTicket::with(['tenant'])
            ->latest('last_reply_at');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('tenant_id')) {
            $query->where('tenant_id', $request->tenant_id);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('subject', 'like', '%' . $request->search . '%')
                  ->orWhere('ticket_number', 'like', '%' . $request->search . '%')
                  ->orWhere('requester_name', 'like', '%' . $request->search . '%');
            });
        }

        $tickets = $query->paginate(20)->withQueryString();
        $tenants = Tenant::where('status', 'approved')->orderBy('name')->get();

        $stats = [
            'open'        => SupportTicket::where('status', 'open')->count(),
            'in_progress' => SupportTicket::where('status', 'in_progress')->count(),
            'resolved'    => SupportTicket::where('status', 'resolved')->count(),
            'unread'      => SupportTicket::where('unread_admin', '>', 0)->count(),
        ];

        return view('superadmin.support.index', compact('tickets', 'tenants', 'stats'));
    }

    public function show(SupportTicket $ticket)
    {
        $ticket->load(['messages.attachments', 'tenant']);
        $this->tickets->markReadByAdmin($ticket);

        return view('superadmin.support.show', compact('ticket'));
    }

    public function reply(Request $request, SupportTicket $ticket)
    {
        $data = $request->validate([
            'body'          => ['required', 'string', 'max:10000'],
            'is_internal'   => ['boolean'],
            'attachments'   => ['nullable', 'array', 'max:5'],
            'attachments.*' => ['file', 'max:5120', 'mimes:jpg,jpeg,png,gif,webp,pdf,txt'],
        ]);

        $admin = Auth::user();

        $this->tickets->addReply(
            ticket: $ticket,
            body: $data['body'],
            senderType: 'admin',
            senderName: $admin->name,
            senderEmail: $admin->email,
            files: $request->file('attachments', []),
            isInternal: $request->boolean('is_internal'),
        );

        return back()->with('success', 'Reply sent.');
    }

    public function updateStatus(Request $request, SupportTicket $ticket)
    {
        $request->validate([
            'status' => ['required', Rule::in(['open', 'in_progress', 'resolved', 'closed'])],
        ]);

        $old = $ticket->status;
        $this->tickets->updateStatus($ticket, $request->status);

        if ($old !== $request->status) {
            $this->notifications->notifyTenantStatusChange($ticket, $request->status);
        }

        return back()->with('success', 'Ticket status updated.');
    }

    public function updatePriority(Request $request, SupportTicket $ticket)
    {
        $request->validate([
            'priority' => ['required', Rule::in(['low', 'medium', 'high', 'urgent'])],
        ]);

        $this->tickets->updatePriority($ticket, $request->priority);

        return back()->with('success', 'Priority updated.');
    }

    public function downloadAttachment(SupportAttachment $attachment)
    {
        if (! $attachment->exists()) {
            abort(404, 'Attachment not found.');
        }

        return Storage::disk('support')->download(
            $attachment->stored_path,
            $attachment->original_name,
        );
    }
}
