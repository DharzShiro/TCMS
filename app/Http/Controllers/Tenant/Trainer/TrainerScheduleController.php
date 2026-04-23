<?php

namespace App\Http\Controllers\Tenant\Trainer;

use App\Http\Controllers\Controller;
use App\Models\TrainingSchedule;
use Illuminate\Http\Request;

class TrainerScheduleController extends Controller
{
    public function index(Request $request)
    {
        $trainerId = auth()->id();

        $query = TrainingSchedule::with(['course'])
            ->where('trainer_id', $trainerId);

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

        $schedules = $query->orderBy('start_date', 'desc')->paginate(10)->withQueryString();

        return view('tenants.trainer.schedules.index', compact('schedules'));
    }

    public function show(TrainingSchedule $trainingSchedule)
    {
        // Ensure this schedule belongs to the authenticated trainer
        abort_if($trainingSchedule->trainer_id !== auth()->id(), 403);

        $trainingSchedule->load(['course', 'trainer']);

        // Load enrollments for this schedule's course
        $enrollments = \App\Models\Enrollment::where('course_id', $trainingSchedule->course_id)
            ->whereIn('status', ['approved', 'completed'])
            ->with('trainee')
            ->get();

        return view('tenants.trainer.schedules.show', compact('trainingSchedule', 'enrollments'));
    }
}