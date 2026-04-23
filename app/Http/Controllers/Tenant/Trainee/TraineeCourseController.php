<?php

namespace App\Http\Controllers\Tenant\Trainee;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Enrollment;
use Illuminate\Http\Request;

class TraineeCourseController extends Controller
{
    /**
     * Browse all active courses available for enrollment.
     */
    public function index(Request $request)
    {
        $query = Course::where('status', 'active')->withCount('enrollments');

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

        $courses = $query->latest()->paginate(12)->withQueryString();

        // Track which courses this trainee is already enrolled in
        $enrolledCourseIds = Enrollment::where('trainee_id', auth()->id())
            ->pluck('course_id')
            ->toArray();

        return view('tenants.trainee.courses.index', compact('courses', 'enrolledCourseIds'));
    }

    /**
     * View details of a single course.
     */
    public function show(Course $course)
    {
        // withCount so the blade can display $course->enrollments_count
        $course->loadCount('enrollments');

        $course->load(['schedules' => function ($q) {
            $q->whereIn('status', ['upcoming', 'ongoing'])
              ->with('trainer')
              ->orderBy('start_date');
        }]);

        // Check if trainee is already enrolled in this course
        $existingEnrollment = Enrollment::where('trainee_id', auth()->id())
            ->where('course_id', $course->id)
            ->first();

        return view('tenants.trainee.courses.show', compact('course', 'existingEnrollment'));
    }

    /**
     * Submit an enrollment request for a course.
     */
    public function enroll(Request $request, Course $course)
    {
        $traineeId = auth()->id();

        // Course must be active
        if ($course->status !== 'active') {
            return back()->withErrors(['course' => 'This course is not available for enrollment.']);
        }

        // Prevent duplicate enrollment
        $exists = Enrollment::where('trainee_id', $traineeId)
            ->where('course_id', $course->id)
            ->exists();

        if ($exists) {
            return back()->withErrors(['course' => 'You are already enrolled in this course.']);
        }

        Enrollment::create([
            'trainee_id'  => $traineeId,
            'course_id'   => $course->id,
            'status'      => 'pending',
            'enrolled_at' => now(),
        ]);

        return redirect()->route('trainee.enrollments.index')
            ->with('success', 'Enrollment request submitted. Please wait for admin approval.');
    }
}