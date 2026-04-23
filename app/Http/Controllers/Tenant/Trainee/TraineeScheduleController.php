<?php

namespace App\Http\Controllers\Tenant\Trainee;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\TrainingSchedule;
use Illuminate\Http\Request;

class TraineeScheduleController extends Controller
{
    /**
     * Show schedules only for courses the trainee is enrolled in.
     */
    public function index(Request $request)
    {
        $traineeId = auth()->id();

        // Get course IDs the trainee is enrolled in (approved or completed)
        $enrolledCourseIds = Enrollment::where('trainee_id', $traineeId)
            ->whereIn('status', ['approved', 'completed', 'pending'])
            ->pluck('course_id');

        $query = TrainingSchedule::with(['course', 'trainer'])
            ->whereIn('course_id', $enrolledCourseIds);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('location', 'like', "%{$search}%")
                  ->orWhereHas('course', function ($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $schedules = $query->orderBy('start_date')->paginate(10)->withQueryString();

        return view('tenants.trainee.schedules.index', compact('schedules'));
    }

    /**
     * View details of a specific schedule.
     */
    public function show(TrainingSchedule $trainingSchedule)
    {
        $traineeId = auth()->id();

        // Ensure the trainee is enrolled in this schedule's course
        $isEnrolled = Enrollment::where('trainee_id', $traineeId)
            ->where('course_id', $trainingSchedule->course_id)
            ->whereIn('status', ['approved', 'completed', 'pending'])
            ->exists();

        abort_if(! $isEnrolled, 403, 'You are not enrolled in this course.');

        $trainingSchedule->load(['course', 'trainer']);

        return view('tenants.trainee.schedules.show', compact('trainingSchedule'));
    }
}