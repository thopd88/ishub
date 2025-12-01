<?php

namespace Modules\School\Policies;

use App\Models\User;
use Modules\School\Models\Submission;

class SubmissionPolicy
{
    public function view(User $user, Submission $submission): bool
    {
        if (! $user->can('school.view_grades')) {
            return false;
        }

        // Teacher can view submissions for their own assignments
        if ($submission->assignment->teacher_id === $user->id) {
            return true;
        }

        // Student can view their own submissions
        if ($submission->student_id === $user->id) {
            return true;
        }

        // Parent can view their children's submissions
        return $user->children()->where('id', $submission->student_id)->exists();
    }

    public function grade(User $user, Submission $submission): bool
    {
        return $user->can('school.grade_submissions') && $submission->assignment->teacher_id === $user->id;
    }
}
