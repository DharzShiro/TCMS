<?php

namespace App\Http\Controllers\Tenant\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemFeedback;
use Illuminate\Http\Request;

class AdminFeedbackController extends Controller
{
    public function index()
    {
        $tenant   = tenancy()->tenant;
        $feedback = SystemFeedback::where('tenant_id', $tenant->id)
            ->latest()
            ->paginate(10);

        return view('admin.feedback.index', compact('feedback'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'rating'   => 'required|integer|min:1|max:5',
            'category' => 'required|in:' . implode(',', array_keys(SystemFeedback::categories())),
            'message'  => 'nullable|string|max:1000',
        ]);

        $tenant = tenancy()->tenant;

        SystemFeedback::create([
            'tenant_id'    => $tenant->id,
            'submitted_by' => auth()->user()->name ?? 'Admin',
            'rating'       => $data['rating'],
            'category'     => $data['category'],
            'message'       => $data['message'] ?? null,
        ]);

        return redirect()->route('admin.feedback.index')
            ->with('success', 'Thank you! Your feedback has been submitted.');
    }
}
