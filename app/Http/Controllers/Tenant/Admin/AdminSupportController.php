<?php

namespace App\Http\Controllers\Tenant\Admin;

use App\Http\Controllers\Controller;
use App\Models\SupportAttachment;
use App\Models\SupportTicket;
use App\Services\Support\SupportTicketService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class AdminSupportController extends Controller
{
    public function __construct(private readonly SupportTicketService $tickets) {}

    public function index(Request $request)
    {
        $tenantId = tenancy()->tenant->id;

        $query = SupportTicket::where('tenant_id', $tenantId)
            ->latest('last_reply_at');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        $tickets = $query->paginate(15)->withQueryString();

        $stats = [
            'open'      => SupportTicket::where('tenant_id', $tenantId)->where('status', 'open')->count(),
            'total'     => SupportTicket::where('tenant_id', $tenantId)->count(),
            'unread'    => SupportTicket::where('tenant_id', $tenantId)->where('unread_tenant', '>', 0)->count(),
            'resolved'  => SupportTicket::where('tenant_id', $tenantId)->where('status', 'resolved')->count(),
        ];

        return view('admin.support.index', compact('tickets', 'stats'));
    }

    public function create()
    {
        return view('admin.support.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'subject'       => ['required', 'string', 'max:255'],
            'category'      => ['required', Rule::in([
                'bug_report', 'technical_issue', 'account_concern',
                'billing_concern', 'feature_request', 'general_inquiry',
            ])],
            'priority'      => ['nullable', Rule::in(['low', 'medium', 'high', 'urgent'])],
            'message'       => ['required', 'string', 'max:10000'],
            'attachments'   => ['nullable', 'array', 'max:5'],
            'attachments.*' => ['file', 'max:5120', 'mimes:jpg,jpeg,png,gif,webp,pdf,txt'],
        ]);

        $data['attachments'] = $request->file('attachments', []);

        $ticket = $this->tickets->createTicket(
            data: $data,
            tenant: tenancy()->tenant,
            user: Auth::guard('tenant')->user(),
        );

        return redirect()
            ->route('admin.support.show', $ticket->id)
            ->with('success', "Ticket {$ticket->ticket_number} created. Our team will respond shortly.");
    }

    public function show(int $id)
    {
        $ticket = SupportTicket::where('id', $id)
            ->where('tenant_id', tenancy()->tenant->id)
            ->with(['messages.attachments'])
            ->firstOrFail();

        $this->tickets->markReadByTenant($ticket);

        return view('admin.support.show', compact('ticket'));
    }

    public function reply(Request $request, int $id)
    {
        $ticket = SupportTicket::where('id', $id)
            ->where('tenant_id', tenancy()->tenant->id)
            ->firstOrFail();

        if (! $ticket->isOpen()) {
            return back()->with('error', 'This ticket is closed. Please open a new ticket.');
        }

        $data = $request->validate([
            'body'          => ['required', 'string', 'max:10000'],
            'attachments'   => ['nullable', 'array', 'max:5'],
            'attachments.*' => ['file', 'max:5120', 'mimes:jpg,jpeg,png,gif,webp,pdf,txt'],
        ]);

        $user = Auth::guard('tenant')->user();

        $this->tickets->addReply(
            ticket: $ticket,
            body: $data['body'],
            senderType: 'tenant',
            senderName: $user->name,
            senderEmail: $user->email,
            files: $request->file('attachments', []),
        );

        return back()->with('success', 'Reply sent.');
    }

    public function downloadAttachment(int $ticketId, SupportAttachment $attachment)
    {
        // Ensure attachment belongs to this tenant's ticket
        $ticket = SupportTicket::where('id', $ticketId)
            ->where('tenant_id', tenancy()->tenant->id)
            ->firstOrFail();

        if ($attachment->message->ticket_id !== $ticket->id) {
            abort(403);
        }

        if (! $attachment->exists()) {
            abort(404);
        }

        return Storage::disk('support')->download(
            $attachment->stored_path,
            $attachment->original_name,
        );
    }
}
