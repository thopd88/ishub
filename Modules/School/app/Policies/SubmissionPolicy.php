<?php

namespace Modules\School\Policies;

use App\Models\User;
use Modules\School\Models\Submission;

class SubmissionPolicy
{
    public function view(User $user, Submission $submission): bool
    {
        if ($user->hasRole('teacher') && $submission->assignment->teacher_id === $user->id) {
            return true;
        }

        if ($user->hasRole('student') && $submission->student_id === $user->id) {
            return true;
        }

        if ($user->hasRole('parent')) {
            return $user->children()->where('id', $submission->student_id)->exists();
        }

        return false;
    }

    public function grade(User $user, Submission $submission): bool
    {
        return $user->hasRole('teacher') && $submission->assignment->teacher_id === $user->id;
    }
}
