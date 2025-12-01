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

        // Check permissions to determine which dashboard to show
        // Teacher dashboard if they can create or grade
        if ($user->can('school.create_assignments') || $user->can('school.grade_submissions')) {
            return $this->teacherDashboard($user);
        }

        // Student dashboard if they can submit assignments
        if ($user->can('school.submit_assignments')) {
            return $this->studentDashboard($user);
        }

        // Parent dashboard if they can view grades
        if ($user->can('school.view_grades')) {
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
