<?php

namespace App\Http\Controllers\Tenant\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\Request;

class AdminCourseController extends Controller
{
    public function index(Request $request)
    {
        $query = Course::withCount('enrollments');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('level')) {
            $query->where('level', $request->level);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $courses = $query->latest()->paginate(10)->withQueryString();

        return view('tenants.admin.courses.index', compact('courses'));
    }

    public function create()
    {
        return view('tenants.admin.courses.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code'           => ['required', 'string', 'max:255', 'unique:courses,code'],
            'name'           => ['required', 'string', 'max:255'],
            'description'    => ['nullable', 'string'],
            'duration_hours' => ['required', 'integer', 'min:1'],
            'level'          => ['nullable', 'in:NC I,NC II,NC III,NC IV,COC'],
            'status'         => ['required', 'in:active,inactive'],
        ]);

        Course::create($validated);

        return redirect()->route('admin.courses.index')
                         ->with('success', 'Course created successfully.');
    }

    public function show(Course $course)
    {
        $course->load(['enrollments.trainee', 'schedules.trainer']);

        return view('tenants.admin.courses.show', compact('course'));
    }

    public function edit(Course $course)
    {
        return view('tenants.admin.courses.edit', compact('course'));
    }

    public function update(Request $request, Course $course)
    {
        $validated = $request->validate([
            'code'           => ['required', 'string', 'max:255', 'unique:courses,code,' . $course->id],
            'name'           => ['required', 'string', 'max:255'],
            'description'    => ['nullable', 'string'],
            'duration_hours' => ['required', 'integer', 'min:1'],
            'level'          => ['nullable', 'in:NC I,NC II,NC III,NC IV,COC'],
            'status'         => ['required', 'in:active,inactive'],
        ]);

        $course->update($validated);

        return redirect()->route('admin.courses.index')
                         ->with('success', 'Course updated successfully.');
    }

    public function destroy(Course $course)
    {
        $course->delete();

        return redirect()->route('admin.courses.index')
                         ->with('success', 'Course deleted successfully.');
    }
}