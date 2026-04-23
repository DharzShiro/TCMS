<?php

namespace App\Http\Controllers\Tenant\Trainee;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Enrollment;
use Illuminate\Http\Request;

class TraineeEnrollmentController extends Controller
{
    /**
     * List all enrollments belonging to the authenticated trainee.
     */
    public function index(Request $request)
    {
        $query = Enrollment::where('trainee_id', auth()->id())
            ->with('course');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('course', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $enrollments = $query->latest()->paginate(10)->withQueryString();

        return view('tenants.trainee.enrollments.index', compact('enrollments'));
    }

    /**
     * View details of a specific enrollment including attendance records.
     */
    public function show(Enrollment $enrollment)
    {
        // Ensure this enrollment belongs to the authenticated trainee
        abort_if($enrollment->trainee_id !== auth()->id(), 403);

        $enrollment->load(['course', 'attendanceRecords']);

        // Attendance breakdown
        $attendanceSummary = [
            'total'   => $enrollment->attendanceRecords->count(),
            'present' => $enrollment->attendanceRecords->where('status', 'present')->count(),
            'absent'  => $enrollment->attendanceRecords->where('status', 'absent')->count(),
            'late'    => $enrollment->attendanceRecords->where('status', 'late')->count(),
        ];

        // Attendance rate percentage
        $attendanceSummary['rate'] = $attendanceSummary['total'] > 0
            ? round(($attendanceSummary['present'] / $attendanceSummary['total']) * 100, 1)
            : 0;

        return view('tenants.trainee.enrollments.show', compact('enrollment', 'attendanceSummary'));
    }
}