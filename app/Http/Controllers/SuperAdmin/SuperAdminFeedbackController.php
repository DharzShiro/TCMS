<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\SystemFeedback;

class SuperAdminFeedbackController extends Controller
{
    public function index()
    {
        $feedback = SystemFeedback::latest()->paginate(20);

        $avgRating = SystemFeedback::avg('rating');
        $total     = SystemFeedback::count();
        $byRating  = SystemFeedback::selectRaw('rating, count(*) as total')
            ->groupBy('rating')
            ->pluck('total', 'rating')
            ->toArray();

        return view('superadmin.feedback.index', compact('feedback', 'avgRating', 'total', 'byRating'));
    }
}
