<?php

namespace App\Http\Controllers\Tenant\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Enrollment;
use Illuminate\Http\Request;

class AdminAttendanceController extends Controller
{
    public function index(Request $request)
    {
        $query = Attendance::with(['enrollment.trainee', 'enrollment.course']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('enrollment.trainee', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            })->orWhereHas('enrollment.course', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date')) {
            $query->whereDate('date', $request->date);
        }

        $attendances = $query->latest('date')->paginate(10)->withQueryString();

        return view('tenants.admin.attendances.index', compact('attendances'));
    }

    public function create()
    {
        // Only approved enrollments can have attendance
        $enrollments = Enrollment::with(['trainee', 'course'])
                                 ->where('status', 'approved')
                                 ->orderBy('created_at', 'desc')
                                 ->get();

        return view('tenants.admin.attendances.create', compact('enrollments'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'enrollment_id' => ['required', 'exists:enrollments,id'],
            'date'          => ['required', 'date'],
            'status'        => ['required', 'in:present,absent,late'],
        ]);

        // Prevent duplicate attendance for same enrollment on same date
        $exists = Attendance::where('enrollment_id', $validated['enrollment_id'])
                            ->whereDate('date', $validated['date'])
                            ->exists();

        if ($exists) {
            return back()->withInput()
                         ->withErrors(['date' => 'Attendance for this trainee on this date already exists.']);
        }

        Attendance::create($validated);

        return redirect()->route('admin.attendances.index')
                         ->with('success', 'Attendance recorded successfully.');
    }

    public function show(Attendance $attendance)
    {
        $attendance->load(['enrollment.trainee', 'enrollment.course']);

        return view('tenants.admin.attendances.show', compact('attendance'));
    }

    public function edit(Attendance $attendance)
    {
        $enrollments = Enrollment::with(['trainee', 'course'])
                                 ->where('status', 'approved')
                                 ->orderBy('created_at', 'desc')
                                 ->get();

        return view('tenants.admin.attendances.edit', compact('attendance', 'enrollments'));
    }

    public function update(Request $request, Attendance $attendance)
    {
        $validated = $request->validate([
            'enrollment_id' => ['required', 'exists:enrollments,id'],
            'date'          => ['required', 'date'],
            'status'        => ['required', 'in:present,absent,late'],
        ]);

        // Prevent duplicate but exclude current record
        $exists = Attendance::where('enrollment_id', $validated['enrollment_id'])
                            ->whereDate('date', $validated['date'])
                            ->where('id', '!=', $attendance->id)
                            ->exists();

        if ($exists) {
            return back()->withInput()
                         ->withErrors(['date' => 'Attendance for this trainee on this date already exists.']);
        }

        $attendance->update($validated);

        return redirect()->route('admin.attendances.index')
                         ->with('success', 'Attendance updated successfully.');
    }

    public function destroy(Attendance $attendance)
    {
        $attendance->delete();

        return redirect()->route('admin.attendances.index')
                         ->with('success', 'Attendance deleted successfully.');
    }
}