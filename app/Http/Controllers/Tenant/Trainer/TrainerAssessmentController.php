<?php

namespace App\Http\Controllers\Tenant\Trainer;

use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Models\Enrollment;
use Illuminate\Http\Request;

class TrainerAssessmentController extends Controller
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
        $trainerId = auth()->id();

        $query = Assessment::with(['enrollment.trainee', 'enrollment.course', 'trainer'])
            ->where('trainer_id', $trainerId);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('enrollment.trainee', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            })->orWhereHas('enrollment.course', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('result')) {
            $query->where('result', $request->result);
        }

        $assessments = $query->latest('assessed_at')->paginate(10)->withQueryString();

        return view('tenants.trainer.assessments.index', compact('assessments'));
    }

    public function create()
    {
        $enrollmentIds = $this->trainerEnrollmentIds();

        // Only approved or completed enrollments under trainer's courses
        $enrollments = Enrollment::with(['trainee', 'course'])
            ->whereIn('id', $enrollmentIds)
            ->whereIn('status', ['approved', 'completed'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('tenants.trainer.assessments.create', compact('enrollments'));
    }

    public function store(Request $request)
    {
        $trainerId     = auth()->id();
        $enrollmentIds = $this->trainerEnrollmentIds();

        $validated = $request->validate([
            'enrollment_id' => ['required', 'exists:enrollments,id'],
            'score'         => ['nullable', 'numeric', 'min:0', 'max:100'],
            'remarks'       => ['nullable', 'string'],
            'result'        => ['required', 'in:competent,not_yet_competent'],
            'assessed_at'   => ['required', 'date'],
        ]);

        // Ensure enrollment belongs to this trainer
        if (! $enrollmentIds->contains($validated['enrollment_id'])) {
            abort(403, 'You are not authorized to assess this enrollment.');
        }

        // Prevent duplicate assessment for same enrollment
        $exists = Assessment::where('enrollment_id', $validated['enrollment_id'])
            ->where('trainer_id', $trainerId)
            ->exists();

        if ($exists) {
            return back()->withInput()
                ->withErrors(['enrollment_id' => 'You have already submitted an assessment for this enrollment.']);
        }

        Assessment::create(array_merge($validated, ['trainer_id' => $trainerId]));

        return redirect()->route('trainer.assessments.index')
            ->with('success', 'Assessment recorded successfully.');
    }

    public function show(Assessment $assessment)
    {
        abort_if($assessment->trainer_id !== auth()->id(), 403);

        $assessment->load(['enrollment.trainee', 'enrollment.course', 'trainer']);

        return view('tenants.trainer.assessments.show', compact('assessment'));
    }

    public function edit(Assessment $assessment)
    {
        abort_if($assessment->trainer_id !== auth()->id(), 403);

        $enrollmentIds = $this->trainerEnrollmentIds();

        $enrollments = Enrollment::with(['trainee', 'course'])
            ->whereIn('id', $enrollmentIds)
            ->whereIn('status', ['approved', 'completed'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('tenants.trainer.assessments.edit', compact('assessment', 'enrollments'));
    }

    public function update(Request $request, Assessment $assessment)
    {
        abort_if($assessment->trainer_id !== auth()->id(), 403);

        $enrollmentIds = $this->trainerEnrollmentIds();

        $validated = $request->validate([
            'enrollment_id' => ['required', 'exists:enrollments,id'],
            'score'         => ['nullable', 'numeric', 'min:0', 'max:100'],
            'remarks'       => ['nullable', 'string'],
            'result'        => ['required', 'in:competent,not_yet_competent'],
            'assessed_at'   => ['required', 'date'],
        ]);

        // Ensure enrollment belongs to this trainer
        if (! $enrollmentIds->contains($validated['enrollment_id'])) {
            abort(403, 'You are not authorized to assess this enrollment.');
        }

        // Prevent duplicate but exclude current record
        $exists = Assessment::where('enrollment_id', $validated['enrollment_id'])
            ->where('trainer_id', auth()->id())
            ->where('id', '!=', $assessment->id)
            ->exists();

        if ($exists) {
            return back()->withInput()
                ->withErrors(['enrollment_id' => 'An assessment for this enrollment already exists.']);
        }

        $assessment->update($validated);

        return redirect()->route('trainer.assessments.index')
            ->with('success', 'Assessment updated successfully.');
    }

    public function destroy(Assessment $assessment)
    {
        abort_if($assessment->trainer_id !== auth()->id(), 403);

        $assessment->delete();

        return redirect()->route('trainer.assessments.index')
            ->with('success', 'Assessment deleted successfully.');
    }
}