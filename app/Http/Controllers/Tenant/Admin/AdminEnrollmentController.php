<?php

namespace App\Http\Controllers\Tenant\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Http\Request;

class AdminEnrollmentController extends Controller
{
    public function index(Request $request)
    {
        $query = Enrollment::with(['trainee', 'course']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('trainee', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            })->orWhereHas('course', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $enrollments = $query->latest()->paginate(10)->withQueryString();

        return view('tenants.admin.enrollments.index', compact('enrollments'));
    }

    public function create()
    {
        $trainees = User::where('role', 'trainee')->orderBy('name')->get();
        $courses  = Course::where('status', 'active')->orderBy('name')->get();

        return view('tenants.admin.enrollments.create', compact('trainees', 'courses'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'trainee_id'  => ['required', 'exists:users,id'],
            'course_id'   => ['required', 'exists:courses,id'],
            'status'      => ['required', 'in:pending,approved,completed,dropped'],
            'enrolled_at' => ['nullable', 'date'],
        ]);

        // Prevent duplicate enrollment
        $exists = Enrollment::where('trainee_id', $validated['trainee_id'])
                            ->where('course_id', $validated['course_id'])
                            ->exists();

        if ($exists) {
            return back()->withInput()
                         ->withErrors(['trainee_id' => 'This trainee is already enrolled in the selected course.']);
        }

        Enrollment::create([
            'trainee_id'  => $validated['trainee_id'],
            'course_id'   => $validated['course_id'],
            'status'      => $validated['status'],
            'enrolled_at' => $validated['enrolled_at'] ?? now(),
        ]);

        return redirect()->route('admin.enrollments.index')
                         ->with('success', 'Enrollment created successfully.');
    }

    public function show(Enrollment $enrollment)
    {
        $enrollment->load(['trainee', 'course', 'attendanceRecords']);

        return view('tenants.admin.enrollments.show', compact('enrollment'));
    }

    public function edit(Enrollment $enrollment)
    {
        $trainees = User::where('role', 'trainee')->orderBy('name')->get();
        $courses  = Course::where('status', 'active')->orderBy('name')->get();

        return view('tenants.admin.enrollments.edit', compact('enrollment', 'trainees', 'courses'));
    }

    public function update(Request $request, Enrollment $enrollment)
    {
        $validated = $request->validate([
            'trainee_id'  => ['required', 'exists:users,id'],
            'course_id'   => ['required', 'exists:courses,id'],
            'status'      => ['required', 'in:pending,approved,completed,dropped'],
            'enrolled_at' => ['nullable', 'date'],
        ]);

        // Prevent duplicate but exclude current enrollment
        $exists = Enrollment::where('trainee_id', $validated['trainee_id'])
                            ->where('course_id', $validated['course_id'])
                            ->where('id', '!=', $enrollment->id)
                            ->exists();

        if ($exists) {
            return back()->withInput()
                         ->withErrors(['trainee_id' => 'This trainee is already enrolled in the selected course.']);
        }

        $enrollment->update($validated);

        return redirect()->route('admin.enrollments.index')
                         ->with('success', 'Enrollment updated successfully.');
    }

    public function destroy(Enrollment $enrollment)
    {
        $enrollment->delete();

        return redirect()->route('admin.enrollments.index')
                         ->with('success', 'Enrollment deleted successfully.');
    }
}