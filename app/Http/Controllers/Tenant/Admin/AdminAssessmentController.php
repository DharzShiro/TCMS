<?php

namespace App\Http\Controllers\Tenant\Admin;

use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Http\Request;

class AdminAssessmentController extends Controller
{
    public function index(Request $request)
    {
        $query = Assessment::with(['enrollment.trainee', 'enrollment.course', 'trainer']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('enrollment.trainee', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            })->orWhereHas('enrollment.course', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            })->orWhereHas('trainer', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('result')) {
            $query->where('result', $request->result);
        }

        if ($request->filled('trainer_id')) {
            $query->where('trainer_id', $request->trainer_id);
        }

        $assessments = $query->latest('assessed_at')->paginate(10)->withQueryString();

        $trainers = User::where('role', 'trainer')->orderBy('name')->get();

        return view('tenants.admin.assessments.index', compact('assessments', 'trainers'));
    }

    public function create()
    {
        $enrollments = Enrollment::with(['trainee', 'course'])
                                 ->whereIn('status', ['approved', 'completed'])
                                 ->orderBy('created_at', 'desc')
                                 ->get();

        $trainers = User::where('role', 'trainer')->orderBy('name')->get();

        return view('tenants.admin.assessments.create', compact('enrollments', 'trainers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'enrollment_id' => ['required', 'exists:enrollments,id'],
            'trainer_id'    => ['required', 'exists:users,id'],
            'score'         => ['nullable', 'numeric', 'min:0', 'max:100'],
            'remarks'       => ['nullable', 'string'],
            'result'        => ['required', 'in:competent,not_yet_competent'],
            'assessed_at'   => ['required', 'date'],
        ]);

        // Prevent duplicate assessment for same enrollment + trainer
        $exists = Assessment::where('enrollment_id', $validated['enrollment_id'])
                            ->where('trainer_id', $validated['trainer_id'])
                            ->exists();

        if ($exists) {
            return back()->withInput()
                         ->withErrors(['enrollment_id' => 'An assessment by this trainer for this enrollment already exists.']);
        }

        Assessment::create($validated);

        return redirect()->route('admin.assessments.index')
                         ->with('success', 'Assessment recorded successfully.');
    }

    public function show(Assessment $assessment)
    {
        $assessment->load(['enrollment.trainee', 'enrollment.course', 'trainer']);

        return view('tenants.admin.assessments.show', compact('assessment'));
    }

    public function edit(Assessment $assessment)
    {
        $enrollments = Enrollment::with(['trainee', 'course'])
                                 ->whereIn('status', ['approved', 'completed'])
                                 ->orderBy('created_at', 'desc')
                                 ->get();

        $trainers = User::where('role', 'trainer')->orderBy('name')->get();

        return view('tenants.admin.assessments.edit', compact('assessment', 'enrollments', 'trainers'));
    }

    public function update(Request $request, Assessment $assessment)
    {
        $validated = $request->validate([
            'enrollment_id' => ['required', 'exists:enrollments,id'],
            'trainer_id'    => ['required', 'exists:users,id'],
            'score'         => ['nullable', 'numeric', 'min:0', 'max:100'],
            'remarks'       => ['nullable', 'string'],
            'result'        => ['required', 'in:competent,not_yet_competent'],
            'assessed_at'   => ['required', 'date'],
        ]);

        // Prevent duplicate but exclude current record
        $exists = Assessment::where('enrollment_id', $validated['enrollment_id'])
                            ->where('trainer_id', $validated['trainer_id'])
                            ->where('id', '!=', $assessment->id)
                            ->exists();

        if ($exists) {
            return back()->withInput()
                         ->withErrors(['enrollment_id' => 'An assessment by this trainer for this enrollment already exists.']);
        }

        $assessment->update($validated);

        return redirect()->route('admin.assessments.index')
                         ->with('success', 'Assessment updated successfully.');
    }

    public function destroy(Assessment $assessment)
    {
        $assessment->delete();

        return redirect()->route('admin.assessments.index')
                         ->with('success', 'Assessment deleted successfully.');
    }
}