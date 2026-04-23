<?php

namespace App\Http\Controllers\Tenant\Trainer;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Enrollment;
use Illuminate\Http\Request;
use App\Models\Course;

class TrainerAttendanceController extends Controller
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
        $enrollmentIds = $this->trainerEnrollmentIds();

        $query = Attendance::with(['enrollment.trainee', 'enrollment.course'])
            ->whereIn('enrollment_id', $enrollmentIds);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('enrollment.trainee', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            })->orWhereHas('enrollment.course', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date')) {
            $query->whereDate('date', $request->date);
        }

        $attendances = $query->latest('date')->paginate(10)->withQueryString();

        return view('tenants.trainer.attendances.index', compact('attendances'));
    }

    public function create()
    {
        $enrollmentIds = $this->trainerEnrollmentIds();

        // Only approved enrollments under trainer's courses
        $enrollments = Enrollment::with(['trainee', 'course'])
            ->whereIn('id', $enrollmentIds)
            ->where('status', 'approved')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('tenants.trainer.attendances.create', compact('enrollments'));
    }

    public function store(Request $request)
    {
        $enrollmentIds = $this->trainerEnrollmentIds();

        $validated = $request->validate([
            'enrollment_id' => ['required', 'exists:enrollments,id'],
            'date'          => ['required', 'date'],
            'status'        => ['required', 'in:present,absent,late'],
        ]);

        // Ensure the enrollment belongs to this trainer
        if (! $enrollmentIds->contains($validated['enrollment_id'])) {
            abort(403, 'You are not authorized to record attendance for this enrollment.');
        }

        // Prevent duplicate attendance for same enrollment on same date
        $exists = Attendance::where('enrollment_id', $validated['enrollment_id'])
            ->whereDate('date', $validated['date'])
            ->exists();

        if ($exists) {
            return back()->withInput()
                ->withErrors(['date' => 'Attendance for this trainee on this date already exists.']);
        }

        Attendance::create($validated);

        return redirect()->route('trainer.attendances.index')
            ->with('success', 'Attendance recorded successfully.');
    }

    public function show(Attendance $attendance)
    {
        $enrollmentIds = $this->trainerEnrollmentIds();

        // Ensure this attendance belongs to trainer's enrollments
        abort_if(! $enrollmentIds->contains($attendance->enrollment_id), 403);

        $attendance->load(['enrollment.trainee', 'enrollment.course']);

        return view('tenants.trainer.attendances.show', compact('attendance'));
    }

    public function edit(Attendance $attendance)
    {
        $enrollmentIds = $this->trainerEnrollmentIds();

        abort_if(! $enrollmentIds->contains($attendance->enrollment_id), 403);

        $enrollments = Enrollment::with(['trainee', 'course'])
            ->whereIn('id', $enrollmentIds)
            ->where('status', 'approved')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('tenants.trainer.attendances.edit', compact('attendance', 'enrollments'));
    }

    public function update(Request $request, Attendance $attendance)
    {
        $enrollmentIds = $this->trainerEnrollmentIds();

        abort_if(! $enrollmentIds->contains($attendance->enrollment_id), 403);

        $validated = $request->validate([
            'enrollment_id' => ['required', 'exists:enrollments,id'],
            'date'          => ['required', 'date'],
            'status'        => ['required', 'in:present,absent,late'],
        ]);

        // Ensure the new enrollment_id also belongs to this trainer
        if (! $enrollmentIds->contains($validated['enrollment_id'])) {
            abort(403, 'You are not authorized to record attendance for this enrollment.');
        }

        // Prevent duplicate but exclude current record
        $exists = Attendance::where('enrollment_id', $validated['enrollment_id'])
            ->whereDate('date', $validated['date'])
            ->where('id', '!=', $attendance->id)
            ->exists();

        if ($exists) {
            return back()->withInput()
                ->withErrors(['date' => 'Attendance for this trainee on this date already exists.']);
        }

        $attendance->update($validated);

        return redirect()->route('trainer.attendances.index')
            ->with('success', 'Attendance updated successfully.');
    }

    public function bulk(Request $request)
    {
        $trainerId = auth()->id();
    
        // Courses this trainer is assigned to
        $courses = \App\Models\Course::whereHas('schedules', function ($q) use ($trainerId) {
            $q->where('trainer_id', $trainerId);
        })->orderBy('name')->get();
    
        $enrollments = collect();
        $selectedCourse = null;
        $selectedDate = $request->input('date', today()->format('Y-m-d'));
        $existingAttendance = collect();
    
        if ($request->filled('course_id')) {
            $selectedCourse = \App\Models\Course::find($request->course_id);
    
            $enrollmentIds = $this->trainerEnrollmentIds();
    
            $enrollments = Enrollment::with('trainee')
                ->whereIn('id', $enrollmentIds)
                ->where('course_id', $request->course_id)
                ->where('status', 'approved')
                ->orderBy('created_at')
                ->get();
    
            // Load existing attendance for this date so we can pre-fill
            $existingAttendance = Attendance::whereIn('enrollment_id', $enrollments->pluck('id'))
                ->whereDate('date', $selectedDate)
                ->get()
                ->keyBy('enrollment_id');
        }
    
        return view('tenants.trainer.attendances.bulk', compact(
            'courses',
            'enrollments',
            'selectedCourse',
            'selectedDate',
            'existingAttendance'
        ));
    }
    
    public function bulkStore(Request $request)
    {
        $request->validate([
            'course_id'      => ['required', 'exists:courses,id'],
            'date'           => ['required', 'date'],
            'attendance'     => ['required', 'array'],
            'attendance.*'   => ['required', 'in:present,absent,late'],
        ]);
    
        $enrollmentIds = $this->trainerEnrollmentIds();
        $date          = $request->date;
        $saved         = 0;
        $skipped       = 0;
    
        foreach ($request->attendance as $enrollmentId => $status) {
            // Ensure enrollment belongs to this trainer
            if (! $enrollmentIds->contains((int) $enrollmentId)) {
                continue;
            }
    
            // Upsert — update if exists, create if not
            $existing = Attendance::where('enrollment_id', $enrollmentId)
                ->whereDate('date', $date)
                ->first();
    
            if ($existing) {
                $existing->update(['status' => $status]);
                $skipped++; // updated
            } else {
                Attendance::create([
                    'enrollment_id' => $enrollmentId,
                    'date'          => $date,
                    'status'        => $status,
                ]);
                $saved++;
            }
        }
    
        $message = "Attendance saved for {$saved} trainee(s).";
        if ($skipped > 0) {
            $message .= " {$skipped} existing record(s) updated.";
        }
    
        return redirect()->route('trainer.attendances.index')
            ->with('success', $message);
    }

    public function destroy(Attendance $attendance)
    {
        $enrollmentIds = $this->trainerEnrollmentIds();

        abort_if(! $enrollmentIds->contains($attendance->enrollment_id), 403);

        $attendance->delete();

        return redirect()->route('trainer.attendances.index')
            ->with('success', 'Attendance deleted successfully.');
    }
}