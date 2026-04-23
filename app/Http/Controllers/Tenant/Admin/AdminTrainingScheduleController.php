<?php

namespace App\Http\Controllers\Tenant\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\TrainingSchedule;
use App\Models\User;
use Illuminate\Http\Request;

class AdminTrainingScheduleController extends Controller
{
    public function index(Request $request)
    {
        $query = TrainingSchedule::with(['course', 'trainer']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('location', 'like', "%{$search}%")
                  ->orWhereHas('course', function ($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%");
                  })
                  ->orWhereHas('trainer', function ($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $schedules = $query->latest()->paginate(10)->withQueryString();

        return view('tenants.admin.training-schedules.index', compact('schedules'));
    }

    public function create()
    {
        $courses  = Course::where('status', 'active')->orderBy('name')->get();
        $trainers = User::where('role', 'trainer')->orderBy('name')->get();

        return view('tenants.admin.training-schedules.create', compact('courses', 'trainers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'course_id'  => 'required|exists:courses,id',
            'trainer_id' => 'required|exists:users,id',
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
            'time_start' => 'required',
            'time_end'   => 'required',
            'location'   => 'nullable|string|max:255',
            'status'     => 'required|in:upcoming,ongoing,completed,cancelled',
        ]);

        if ($request->time_end <= $request->time_start) {
            return back()
                ->withErrors(['time_end' => 'The end time must be after the start time.'])
                ->withInput();
        }

        TrainingSchedule::create($request->only([
            'course_id', 'trainer_id', 'start_date', 'end_date',
            'time_start', 'time_end', 'location', 'status',
        ]));

        return redirect()->route('admin.training-schedules.index')
                        ->with('success', 'Training schedule created successfully.');
    }

    public function show(TrainingSchedule $trainingSchedule)
    {
        $trainingSchedule->load(['course', 'trainer']);

        return view('tenants.admin.training-schedules.show', compact('trainingSchedule'));
    }

    public function edit(TrainingSchedule $trainingSchedule)
    {
        $courses  = Course::where('status', 'active')->orderBy('name')->get();
        $trainers = User::where('role', 'trainer')->orderBy('name')->get();

        return view('tenants.admin.training-schedules.edit', compact('trainingSchedule', 'courses', 'trainers'));
    }

    public function update(Request $request, TrainingSchedule $trainingSchedule)
    {
        $request->validate([
            'course_id'  => 'required|exists:courses,id',
            'trainer_id' => 'required|exists:users,id',
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
            'time_start' => 'required',
            'time_end'   => 'required',
            'location'   => 'nullable|string|max:255',
            'status'     => 'required|in:upcoming,ongoing,completed,cancelled',
        ]);

        if ($request->time_end <= $request->time_start) {
            return back()
                ->withErrors(['time_end' => 'The end time must be after the start time.'])
                ->withInput();
        }

        $trainingSchedule->update($request->only([
            'course_id', 'trainer_id', 'start_date', 'end_date',
            'time_start', 'time_end', 'location', 'status',
        ]));

        return redirect()->route('admin.training-schedules.index')
                        ->with('success', 'Training schedule updated successfully.');
    }

    public function destroy(TrainingSchedule $trainingSchedule)
    {
        $trainingSchedule->delete();

        return redirect()->route('admin.training-schedules.index')
                         ->with('success', 'Training schedule deleted successfully.');
    }
}