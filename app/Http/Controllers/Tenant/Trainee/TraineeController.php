<?php

namespace App\Http\Controllers\Tenant\Trainee;

use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Models\Certificate;
use App\Models\Enrollment;

class TraineeController extends Controller
{
    public function dashboard()
    {
        $traineeId = auth()->id();

        // All enrollments for this trainee
        $enrollments = Enrollment::where('trainee_id', $traineeId)
            ->with('course')
            ->latest()
            ->get();

        // Enrollment status counts
        $pendingCount   = $enrollments->where('status', 'pending')->count();
        $approvedCount  = $enrollments->where('status', 'approved')->count();
        $completedCount = $enrollments->where('status', 'completed')->count();
        $droppedCount   = $enrollments->where('status', 'dropped')->count();

        $enrollmentIds = $enrollments->pluck('id');

        // Assessment summary
        $assessments          = Assessment::whereIn('enrollment_id', $enrollmentIds)->get();
        $competentCount       = $assessments->where('result', 'competent')->count();
        $notYetCompetentCount = $assessments->where('result', 'not_yet_competent')->count();

        // Certificates earned
        $certificatesCount = Certificate::whereIn('enrollment_id', $enrollmentIds)->count();

        // Recent enrollments (latest 5)
        $recentEnrollments = $enrollments->take(5);

        return view('tenants.trainee.dashboard', compact(
            'pendingCount',
            'approvedCount',
            'completedCount',
            'droppedCount',
            'competentCount',
            'notYetCompetentCount',
            'certificatesCount',
            'recentEnrollments'
        ));
    }
}