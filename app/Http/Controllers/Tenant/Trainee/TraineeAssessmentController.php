<?php

namespace App\Http\Controllers\Tenant\Trainee;

use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Models\Enrollment;
use Illuminate\Http\Request;

class TraineeAssessmentController extends Controller
{
    /**
     * Get enrollment IDs belonging to this trainee.
     */
    private function traineeEnrollmentIds(): \Illuminate\Support\Collection
    {
        return Enrollment::where('trainee_id', auth()->id())->pluck('id');
    }

    /**
     * List all assessment results for the authenticated trainee.
     */
    public function index(Request $request)
    {
        $enrollmentIds = $this->traineeEnrollmentIds();

        $query = Assessment::with(['enrollment.course', 'trainer'])
            ->whereIn('enrollment_id', $enrollmentIds);

        if ($request->filled('result')) {
            $query->where('result', $request->result);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('enrollment.course', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $assessments = $query->latest('assessed_at')->paginate(10)->withQueryString();

        // Summary counts
        $competentCount       = Assessment::whereIn('enrollment_id', $enrollmentIds)->where('result', 'competent')->count();
        $notYetCompetentCount = Assessment::whereIn('enrollment_id', $enrollmentIds)->where('result', 'not_yet_competent')->count();

        return view('tenants.trainee.assessments.index', compact(
            'assessments',
            'competentCount',
            'notYetCompetentCount'
        ));
    }

    /**
     * View a specific assessment result.
     */
    public function show(Assessment $assessment)
    {
        $enrollmentIds = $this->traineeEnrollmentIds();

        // Ensure this assessment belongs to the authenticated trainee
        abort_if(! $enrollmentIds->contains($assessment->enrollment_id), 403);

        $assessment->load(['enrollment.course', 'trainer']);

        return view('tenants.trainee.assessments.show', compact('assessment'));
    }
}