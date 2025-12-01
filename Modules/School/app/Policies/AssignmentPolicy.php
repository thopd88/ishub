<?php

namespace Modules\School\Policies;

use App\Models\User;
use Modules\School\Models\Assignment;

class AssignmentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('school.view_assignments');
    }

    public function view(User $user, Assignment $assignment): bool
    {
        return $user->can('school.view_assignments');
    }

    public function create(User $user): bool
    {
        return $user->can('school.create_assignments');
    }

    public function update(User $user, Assignment $assignment): bool
    {
        return $user->can('school.edit_assignments') && $assignment->teacher_id === $user->id;
    }

    public function delete(User $user, Assignment $assignment): bool
    {
        return $user->can('school.delete_assignments') && $assignment->teacher_id === $user->id;
    }
}
