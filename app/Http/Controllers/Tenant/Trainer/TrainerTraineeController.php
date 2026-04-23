<?php

namespace App\Http\Controllers\Tenant\Trainer;

use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Models\Attendance;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Http\Request;

class TrainerTraineeController extends Controller
{
    /**
     * Get enrollment IDs that belong to this trainer's courses.
     */
    private function trainerEnrollmentIds(): \Illuminate\Support\Collection
    {
        $trainerId = auth()->id();

        return Enrollment::whereHas('course.schedules', function ($q) use ($trainerId) {
            $q->where('trainer_id', $trainerId);
        })->pluck('id');
    }

    public function index(Request $request)
    {
        $trainerId     = auth()->id();
        $enrollmentIds = $this->trainerEnrollmentIds();

        // Get unique trainee IDs under this trainer
        $query = Enrollment::with(['trainee', 'course'])
            ->whereIn('id', $enrollmentIds)
            ->whereIn('status', ['approved', 'completed', 'pending', 'dropped']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('trainee', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('course_id')) {
            $query->where('course_id', $request->course_id);
        }

        $enrollments = $query->latest()->paginate(10)->withQueryString();

        // Get distinct courses for filter dropdown
        $courses = \App\Models\Course::whereHas('schedules', function ($q) use ($trainerId) {
            $q->where('trainer_id', $trainerId);
        })->orderBy('name')->get();

        return view('tenants.trainer.trainees.index', compact('enrollments', 'courses'));
    }

    public function show(User $trainee)
    {
        $trainerId     = auth()->id();
        $enrollmentIds = $this->trainerEnrollmentIds();

        // Get only enrollments of this trainee that are under this trainer's courses
        $enrollments = Enrollment::where('trainee_id', $trainee->id)
            ->whereIn('id', $enrollmentIds)
            ->with('course')
            ->get();

        // 403 if trainer has no connection to this trainee
        abort_if($enrollments->isEmpty(), 403, 'You are not authorized to view this trainee.');

        // Attendance summary per enrollment
        $attendanceSummary = [];
        foreach ($enrollments as $enrollment) {
            $records = Attendance::where('enrollment_id', $enrollment->id)->get();
            $attendanceSummary[$enrollment->id] = [
                'total'   => $records->count(),
                'present' => $records->where('status', 'present')->count(),
                'absent'  => $records->where('status', 'absent')->count(),
                'late'    => $records->where('status', 'late')->count(),
            ];
        }

        // Assessments this trainer gave to this trainee
        $assessments = Assessment::where('trainer_id', $trainerId)
            ->whereIn('enrollment_id', $enrollments->pluck('id'))
            ->with('enrollment.course')
            ->latest('assessed_at')
            ->get();

        return view('tenants.trainer.trainees.show', compact(
            'trainee',
            'enrollments',
            'attendanceSummary',
            'assessments'
        ));
    }
}