<?php

namespace Modules\School\Policies;

use App\Models\User;
use Modules\School\Models\Assignment;

class AssignmentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['teacher', 'student', 'parent']);
    }

    public function view(User $user, Assignment $assignment): bool
    {
        return $user->hasAnyRole(['teacher', 'student', 'parent']);
    }

    public function create(User $user): bool
    {
        return $user->hasRole('teacher');
    }

    public function update(User $user, Assignment $assignment): bool
    {
        return $user->hasRole('teacher') && $assignment->teacher_id === $user->id;
    }

    public function delete(User $user, Assignment $assignment): bool
    {
        return $user->hasRole('teacher') && $assignment->teacher_id === $user->id;
    }
}
