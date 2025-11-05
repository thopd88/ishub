<?php

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Modules\School\Models\Assignment;
use Modules\School\Models\Submission;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    Role::firstOrCreate(['name' => 'teacher', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'student', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'parent', 'guard_name' => 'web']);
});

it('shows teacher dashboard to teachers', function () {
    $teacher = User::factory()->create();
    $teacher->assignRole('teacher');

    Assignment::factory()->count(3)->create(['teacher_id' => $teacher->id]);

    $response = actingAs($teacher)->get(route('school.dashboard'));

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page
        ->component('School/Teacher/Dashboard')
        ->has('assignments', 3)
    );
});

it('shows student dashboard to students', function () {
    $student = User::factory()->create();
    $student->assignRole('student');

    $teacher = User::factory()->create();
    $teacher->assignRole('teacher');

    $assignment = Assignment::factory()->create(['teacher_id' => $teacher->id]);

    Submission::factory()->create([
        'assignment_id' => $assignment->id,
        'student_id' => $student->id,
    ]);

    $response = actingAs($student)->get(route('school.dashboard'));

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page
        ->component('School/Student/Dashboard')
        ->has('assignments')
        ->has('stats')
    );
});

it('shows parent dashboard to parents', function () {
    $parent = User::factory()->create();
    $parent->assignRole('parent');

    $student = User::factory()->create();
    $student->assignRole('student');

    DB::table('student_parent_relationships')->insert([
        'student_id' => $student->id,
        'parent_id' => $parent->id,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $response = actingAs($parent)->get(route('school.dashboard'));

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page
        ->component('School/Parent/Dashboard')
        ->has('children', 1)
    );
});

it('prevents access to users without school roles', function () {
    $user = User::factory()->create();

    $response = actingAs($user)->get(route('school.dashboard'));

    $response->assertForbidden();
});

it('calculates teacher statistics correctly', function () {
    $teacher = User::factory()->create();
    $teacher->assignRole('teacher');

    $student = User::factory()->create();
    $student->assignRole('student');

    $assignment1 = Assignment::factory()->create(['teacher_id' => $teacher->id]);
    $assignment2 = Assignment::factory()->create(['teacher_id' => $teacher->id]);

    Submission::factory()->create([
        'assignment_id' => $assignment1->id,
        'student_id' => $student->id,
    ]);

    Submission::factory()->create([
        'assignment_id' => $assignment1->id,
        'student_id' => $student->id,
    ]);

    $response = actingAs($teacher)->get(route('school.dashboard'));

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page
        ->where('stats.total_assignments', 2)
        ->where('stats.total_submissions', 2)
    );
});

it('shows pending and completed assignments for students', function () {
    $student = User::factory()->create();
    $student->assignRole('student');

    $teacher = User::factory()->create();
    $teacher->assignRole('teacher');

    $assignment1 = Assignment::factory()->create(['teacher_id' => $teacher->id]);
    $assignment2 = Assignment::factory()->create(['teacher_id' => $teacher->id]);

    Submission::factory()->create([
        'assignment_id' => $assignment1->id,
        'student_id' => $student->id,
    ]);

    $response = actingAs($student)->get(route('school.dashboard'));

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page
        ->where('stats.pending_assignments', 1)
        ->where('stats.completed_assignments', 1)
    );
});
