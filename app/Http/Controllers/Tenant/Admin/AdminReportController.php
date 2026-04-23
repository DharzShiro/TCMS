<?php

namespace App\Http\Controllers\Tenant\Admin;

use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Models\Attendance;
use App\Models\Certificate;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\TrainingSchedule;
use App\Models\User;
use App\Services\Reports\ReportExportService;
use Illuminate\Http\Request;

class AdminReportController extends Controller
{
    // -------------------------------------------------------------------------
    // Dashboard / Index
    // -------------------------------------------------------------------------

    public function index()
    {
        $tenant      = tenancy()->tenant;
        $plan        = $tenant->subscription ?? 'basic';

        // ── Trainee Stats ──────────────────────────────────────────────────
        $totalTrainees  = User::where('role', 'trainee')->count();
        $totalTrainers  = User::where('role', 'trainer')->count();
        $totalCourses   = Course::count();
        $activeCourses  = Course::where('status', 'active')->count();

        // ── Enrollment Stats ───────────────────────────────────────────────
        $enrollmentStats = [
            'total'     => Enrollment::count(),
            'pending'   => Enrollment::where('status', 'pending')->count(),
            'approved'  => Enrollment::where('status', 'approved')->count(),
            'completed' => Enrollment::where('status', 'completed')->count(),
            'dropped'   => Enrollment::where('status', 'dropped')->count(),
        ];

        // ── Attendance Stats ───────────────────────────────────────────────
        $attendanceStats = [
            'total'   => Attendance::count(),
            'present' => Attendance::where('status', 'present')->count(),
            'absent'  => Attendance::where('status', 'absent')->count(),
            'late'    => Attendance::where('status', 'late')->count(),
        ];

        // ── Assessment Stats ───────────────────────────────────────────────
        $assessmentStats = [
            'total'             => Assessment::count(),
            'competent'         => Assessment::where('result', 'competent')->count(),
            'not_yet_competent' => Assessment::where('result', 'not_yet_competent')->count(),
        ];

        // ── Certificate Stats (premium) ────────────────────────────────────
        $certificateStats = [
            'total'   => Certificate::count(),
            'expired' => Certificate::whereNotNull('expires_at')->whereDate('expires_at', '<', now())->count(),
            'active'  => Certificate::where(function ($q) {
                $q->whereNull('expires_at')->orWhereDate('expires_at', '>=', now());
            })->count(),
        ];

        // ── Top Courses by Enrollment ──────────────────────────────────────
        $topCourses = Course::withCount('enrollments')
            ->orderByDesc('enrollments_count')
            ->take(5)
            ->get();

        // ── Monthly Enrollments (last 6 months) ────────────────────────────
        $monthlyEnrollments = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $monthlyEnrollments[] = [
                'label' => $month->format('M Y'),
                'count' => Enrollment::whereYear('enrolled_at', $month->year)
                               ->whereMonth('enrolled_at', $month->month)
                               ->count(),
            ];
        }

        // ── Schedule Stats ─────────────────────────────────────────────────
        $scheduleStats = [
            'upcoming'   => TrainingSchedule::where('status', 'upcoming')->count(),
            'ongoing'    => TrainingSchedule::where('status', 'ongoing')->count(),
            'completed'  => TrainingSchedule::where('status', 'completed')->count(),
            'cancelled'  => TrainingSchedule::where('status', 'cancelled')->count(),
        ];

        return view('tenants.admin.reports.index', compact(
            'plan',
            'totalTrainees',
            'totalTrainers',
            'totalCourses',
            'activeCourses',
            'enrollmentStats',
            'attendanceStats',
            'assessmentStats',
            'certificateStats',
            'topCourses',
            'monthlyEnrollments',
            'scheduleStats'
        ));
    }

    // -------------------------------------------------------------------------
    // Exports
    // -------------------------------------------------------------------------

    /**
     * Export trainees list — Basic+
     */
    public function exportTrainees(Request $request)
    {
        $tenant = tenancy()->tenant;
        $plan   = $tenant->subscription ?? 'basic';

        $format = $request->input('format', 'csv');

        $data = User::where('role', 'trainee')
            ->withCount('enrollments')
            ->orderBy('name')
            ->get()
            ->map(fn($u) => [
                'ID'          => $u->id,
                'Name'        => $u->name,
                'Email'       => $u->email,
                'Enrollments' => $u->enrollments_count,
                'Joined'      => $u->created_at->format('Y-m-d'),
            ]);

        return (new ReportExportService())->export(
            data: $data->toArray(),
            filename: 'trainees-report',
            format: $format,
            title: 'Trainees Report',
            plan: $plan
        );
    }

    /**
     * Export enrollments — Basic+
     */
    public function exportEnrollments(Request $request)
    {
        $tenant = tenancy()->tenant;
        $plan   = $tenant->subscription ?? 'basic';

        $format = $request->input('format', 'csv');

        $data = Enrollment::with(['trainee', 'course'])
            ->orderByDesc('enrolled_at')
            ->get()
            ->map(fn($e) => [
                'ID'          => $e->id,
                'Trainee'     => $e->trainee->name ?? '—',
                'Email'       => $e->trainee->email ?? '—',
                'Course'      => $e->course->name ?? '—',
                'Course Code' => $e->course->code ?? '—',
                'Status'      => ucfirst($e->status),
                'Enrolled At' => $e->enrolled_at?->format('Y-m-d') ?? '—',
            ]);

        return (new ReportExportService())->export(
            data: $data->toArray(),
            filename: 'enrollments-report',
            format: $format,
            title: 'Enrollments Report',
            plan: $plan
        );
    }

    /**
     * Export attendance — Basic+
     */
    public function exportAttendances(Request $request)
    {
        $tenant = tenancy()->tenant;
        $plan   = $tenant->subscription ?? 'basic';

        $format = $request->input('format', 'csv');

        $data = Attendance::with(['enrollment.trainee', 'enrollment.course'])
            ->orderByDesc('date')
            ->get()
            ->map(fn($a) => [
                'ID'      => $a->id,
                'Trainee' => $a->enrollment->trainee->name ?? '—',
                'Course'  => $a->enrollment->course->name ?? '—',
                'Date'    => $a->date->format('Y-m-d'),
                'Status'  => ucfirst($a->status),
            ]);

        return (new ReportExportService())->export(
            data: $data->toArray(),
            filename: 'attendance-report',
            format: $format,
            title: 'Attendance Report',
            plan: $plan
        );
    }

    /**
     * Export assessments — Standard+
     */
    public function exportAssessments(Request $request)
    {
        $tenant = tenancy()->tenant;
        $plan   = $tenant->subscription ?? 'basic';

        $format = $request->input('format', 'csv');

        $data = Assessment::with(['enrollment.trainee', 'enrollment.course', 'trainer'])
            ->orderByDesc('assessed_at')
            ->get()
            ->map(fn($a) => [
                'ID'          => $a->id,
                'Trainee'     => $a->enrollment->trainee->name ?? '—',
                'Course'      => $a->enrollment->course->name ?? '—',
                'Trainer'     => $a->trainer->name ?? '—',
                'Score'       => $a->score ?? '—',
                'Result'      => ucwords(str_replace('_', ' ', $a->result)),
                'Remarks'     => $a->remarks ?? '—',
                'Assessed At' => $a->assessed_at?->format('Y-m-d') ?? '—',
            ]);

        return (new ReportExportService())->export(
            data: $data->toArray(),
            filename: 'assessments-report',
            format: $format,
            title: 'Assessments Report',
            plan: $plan
        );
    }

    /**
     * Export certificates — Premium only
     */
    public function exportCertificates(Request $request)
    {
        $tenant = tenancy()->tenant;
        $plan   = $tenant->subscription ?? 'basic';

        $format = $request->input('format', 'csv');

        $data = Certificate::with(['enrollment.trainee', 'enrollment.course', 'trainer'])
            ->orderByDesc('issued_at')
            ->get()
            ->map(fn($c) => [
                'Cert No.'    => $c->certificate_number,
                'Trainee'     => $c->enrollment->trainee->name ?? '—',
                'Course'      => $c->enrollment->course->name ?? '—',
                'Trainer'     => $c->trainer->name ?? '—',
                'Issued At'   => $c->issued_at->format('Y-m-d'),
                'Expires At'  => $c->expires_at?->format('Y-m-d') ?? 'No Expiry',
            ]);

        return (new ReportExportService())->export(
            data: $data->toArray(),
            filename: 'certificates-report',
            format: $format,
            title: 'Certificates Report',
            plan: $plan
        );
    }

    /**
     * Export trainers — Standard+
     */
    public function exportTrainers(Request $request)
    {
        $tenant = tenancy()->tenant;
        $plan   = $tenant->subscription ?? 'basic';

        $format = $request->input('format', 'csv');

        $data = User::where('role', 'trainer')
            ->withCount('assessments')
            ->orderBy('name')
            ->get()
            ->map(fn($u) => [
                'ID'          => $u->id,
                'Name'        => $u->name,
                'Email'       => $u->email,
                'Assessments' => $u->assessments_count,
                'Joined'      => $u->created_at->format('Y-m-d'),
            ]);

        return (new ReportExportService())->export(
            data: $data->toArray(),
            filename: 'trainers-report',
            format: $format,
            title: 'Trainers Report',
            plan: $plan
        );
    }
}