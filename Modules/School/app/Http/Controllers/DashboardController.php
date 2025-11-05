<?php

namespace Modules\School\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Modules\School\Models\Assignment;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        if ($user->hasRole('teacher')) {
            return $this->teacherDashboard($user);
        }

        if ($user->hasRole('student')) {
            return $this->studentDashboard($user);
        }

        if ($user->hasRole('parent')) {
            return $this->parentDashboard($user);
        }

        abort(403, 'You do not have access to the school module.');
    }

    protected function teacherDashboard($user): Response
    {
        $assignments = Assignment::query()
            ->where('teacher_id', $user->id)
            ->withCount('submissions')
            ->latest()
            ->get();

        $totalAssignments = $assignments->count();
        $totalSubmissions = $assignments->sum('submissions_count');

        return Inertia::render('School/Teacher/Dashboard', [
            'assignments' => $assignments,
            'stats' => [
                'total_assignments' => $totalAssignments,
                'total_submissions' => $totalSubmissions,
            ],
        ]);
    }

    protected function studentDashboard($user): Response
    {
        $assignments = Assignment::query()
            ->with(['submissions' => function ($query) use ($user) {
                $query->where('student_id', $user->id);
            }])
            ->latest()
            ->get();

        $pendingAssignments = $assignments->filter(function ($assignment) {
            return $assignment->submissions->isEmpty();
        })->count();

        $completedAssignments = $assignments->filter(function ($assignment) {
            return $assignment->submissions->isNotEmpty();
        })->count();

        return Inertia::render('School/Student/Dashboard', [
            'assignments' => $assignments,
            'stats' => [
                'pending_assignments' => $pendingAssignments,
                'completed_assignments' => $completedAssignments,
            ],
        ]);
    }

    protected function parentDashboard($user): Response
    {
        $children = $user->children()->with([
            'submissions' => function ($query) {
                $query->with('assignment')->latest()->limit(10);
            },
        ])->get();

        return Inertia::render('School/Parent/Dashboard', [
            'children' => $children,
        ]);
    }
}
