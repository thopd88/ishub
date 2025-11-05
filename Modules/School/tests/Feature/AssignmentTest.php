<?php

use App\Models\User;
use Modules\School\Models\Assignment;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;

beforeEach(function () {
    Role::firstOrCreate(['name' => 'teacher', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'student', 'guard_name' => 'web']);
});

it('allows teachers to create assignments', function () {
    $teacher = User::factory()->create();
    $teacher->assignRole('teacher');

    $response = actingAs($teacher)->post(route('school.assignments.store'), [
        'title' => 'Test Assignment',
        'description' => 'This is a test assignment description',
        'due_date' => now()->addDays(7)->toDateTimeString(),
        'max_points' => 100,
    ]);

    $response->assertRedirect();
    assertDatabaseHas('assignments', [
        'teacher_id' => $teacher->id,
        'title' => 'Test Assignment',
    ]);
});

it('prevents non-teachers from creating assignments', function () {
    $student = User::factory()->create();
    $student->assignRole('student');

    $response = actingAs($student)->post(route('school.assignments.store'), [
        'title' => 'Test Assignment',
        'description' => 'This is a test assignment description',
        'due_date' => now()->addDays(7)->toDateTimeString(),
        'max_points' => 100,
    ]);

    $response->assertForbidden();
});

it('allows teachers to update their own assignments', function () {
    $teacher = User::factory()->create();
    $teacher->assignRole('teacher');

    $assignment = Assignment::factory()->create(['teacher_id' => $teacher->id]);

    $response = actingAs($teacher)->put(route('school.assignments.update', $assignment), [
        'title' => 'Updated Assignment',
        'description' => 'Updated description',
        'due_date' => now()->addDays(10)->toDateTimeString(),
        'max_points' => 150,
    ]);

    $response->assertRedirect();
    assertDatabaseHas('assignments', [
        'id' => $assignment->id,
        'title' => 'Updated Assignment',
    ]);
});

it('prevents teachers from updating other teachers assignments', function () {
    $teacher1 = User::factory()->create();
    $teacher1->assignRole('teacher');

    $teacher2 = User::factory()->create();
    $teacher2->assignRole('teacher');

    $assignment = Assignment::factory()->create(['teacher_id' => $teacher1->id]);

    $response = actingAs($teacher2)->put(route('school.assignments.update', $assignment), [
        'title' => 'Hacked Assignment',
        'description' => 'Hacked description',
        'due_date' => now()->addDays(10)->toDateTimeString(),
        'max_points' => 150,
    ]);

    $response->assertForbidden();
});

it('allows teachers to delete their own assignments', function () {
    $teacher = User::factory()->create();
    $teacher->assignRole('teacher');

    $assignment = Assignment::factory()->create(['teacher_id' => $teacher->id]);

    $response = actingAs($teacher)->delete(route('school.assignments.destroy', $assignment));

    $response->assertRedirect();
    expect(Assignment::find($assignment->id))->toBeNull();
});

it('validates assignment creation data', function () {
    $teacher = User::factory()->create();
    $teacher->assignRole('teacher');

    $response = actingAs($teacher)->post(route('school.assignments.store'), [
        'title' => '',
        'description' => '',
        'due_date' => 'invalid-date',
        'max_points' => -5,
    ]);

    $response->assertSessionHasErrors(['title', 'description', 'due_date', 'max_points']);
});
