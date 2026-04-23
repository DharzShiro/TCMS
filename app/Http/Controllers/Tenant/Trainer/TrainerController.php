<?php

namespace App\Http\Controllers\Tenant\Trainer;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Assessment;
use App\Models\Enrollment;
use App\Models\TrainingSchedule;

class TrainerController extends Controller
{
    public function dashboard()
    {
        $trainer   = auth()->user();
        $trainerId = $trainer->id;

        // Schedules assigned to this trainer
        $upcomingSchedules = TrainingSchedule::where('trainer_id', $trainerId)
            ->where('status', 'upcoming')
            ->with('course')
            ->orderBy('start_date')
            ->take(5)
            ->get();

        $ongoingSchedules = TrainingSchedule::where('trainer_id', $trainerId)
            ->where('status', 'ongoing')
            ->with('course')
            ->orderBy('start_date')
            ->get();

        // Enrollments under this trainer's courses
        $enrollmentIds = Enrollment::whereHas('course.schedules', function ($q) use ($trainerId) {
            $q->where('trainer_id', $trainerId);
        })->pluck('id');

        // Attendance stats
        $totalAttendance  = Attendance::whereIn('enrollment_id', $enrollmentIds)->count();
        $presentCount     = Attendance::whereIn('enrollment_id', $enrollmentIds)->where('status', 'present')->count();
        $absentCount      = Attendance::whereIn('enrollment_id', $enrollmentIds)->where('status', 'absent')->count();
        $lateCount        = Attendance::whereIn('enrollment_id', $enrollmentIds)->where('status', 'late')->count();

        // Assessment stats
        $totalAssessments       = Assessment::where('trainer_id', $trainerId)->count();
        $competentCount         = Assessment::where('trainer_id', $trainerId)->where('result', 'competent')->count();
        $notYetCompetentCount   = Assessment::where('trainer_id', $trainerId)->where('result', 'not_yet_competent')->count();

        // Trainees count
        $totalTrainees = Enrollment::whereIn('id', $enrollmentIds)
            ->whereIn('status', ['approved', 'completed'])
            ->distinct('trainee_id')
            ->count('trainee_id');

        // Recent attendance recorded by this trainer (latest 5)
        $recentAttendance = Attendance::whereIn('enrollment_id', $enrollmentIds)
            ->with(['enrollment.trainee', 'enrollment.course'])
            ->latest('date')
            ->take(5)
            ->get();

        return view('tenants.trainer.dashboard', compact(
            'upcomingSchedules',
            'ongoingSchedules',
            'totalAttendance',
            'presentCount',
            'absentCount',
            'lateCount',
            'totalAssessments',
            'competentCount',
            'notYetCompetentCount',
            'totalTrainees',
            'recentAttendance'
        ));
    }
}