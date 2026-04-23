<?php

namespace App\Http\Controllers\Tenant\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Assessment;
use App\Models\TrainingSchedule;

class AdminController extends Controller
{
    public function index()
    {
        return view('tenants.admin.dashboard', [
            'totalTrainers'          => User::where('role', 'trainer')->count(),
            'totalTrainees'          => User::where('role', 'trainee')->count(),
            'totalCourses'           => Course::where('status', 'active')->count(),
            'totalEnrollments'       => Enrollment::count(),

            'pendingEnrollments'     => Enrollment::where('status', 'pending')->count(),
            'approvedEnrollments'    => Enrollment::where('status', 'approved')->count(),
            'completedEnrollments'   => Enrollment::where('status', 'completed')->count(),
            'droppedEnrollments'     => Enrollment::where('status', 'dropped')->count(),

            'upcomingSchedules'      => TrainingSchedule::where('status', 'upcoming')->count(),
            'ongoingSchedules'       => TrainingSchedule::where('status', 'ongoing')->count(),
            'completedSchedules'     => TrainingSchedule::where('status', 'completed')->count(),
            'cancelledSchedules'     => TrainingSchedule::where('status', 'cancelled')->count(),

            'competentCount'         => Assessment::where('result', 'competent')->count(),
            'notYetCompetentCount'   => Assessment::where('result', 'not_yet_competent')->count(),
            'totalAssessments'       => Assessment::count(),

            'recentEnrollments'      => Enrollment::with(['trainee', 'course'])
                                                  ->latest()
                                                  ->take(5)
                                                  ->get(),
        ]);
    }
}