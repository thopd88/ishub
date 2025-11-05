<?php

use App\Models\User;
use Modules\School\Models\Assignment;
use Modules\School\Models\Submission;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;

beforeEach(function () {
    Role::firstOrCreate(['name' => 'teacher', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'student', 'guard_name' => 'web']);
});

it('allows students to submit assignments', function () {
    $student = User::factory()->create();
    $student->assignRole('student');

    $teacher = User::factory()->create();
    $teacher->assignRole('teacher');

    $assignment = Assignment::factory()->create(['teacher_id' => $teacher->id]);

    $response = actingAs($student)->post(route('school.submissions.store', $assignment), [
        'content' => 'This is my submission content for the assignment.',
    ]);

    $response->assertRedirect();
    assertDatabaseHas('submissions', [
        'assignment_id' => $assignment->id,
        'student_id' => $student->id,
        'content' => 'This is my submission content for the assignment.',
    ]);
});

it('prevents non-students from submitting assignments', function () {
    $teacher = User::factory()->create();
    $teacher->assignRole('teacher');

    $assignment = Assignment::factory()->create(['teacher_id' => $teacher->id]);

    $response = actingAs($teacher)->post(route('school.submissions.store', $assignment), [
        'content' => 'This is my submission content.',
    ]);

    $response->assertForbidden();
});

it('allows teachers to grade submissions', function () {
    $teacher = User::factory()->create();
    $teacher->assignRole('teacher');

    $student = User::factory()->create();
    $student->assignRole('student');

    $assignment = Assignment::factory()->create([
        'teacher_id' => $teacher->id,
        'max_points' => 100,
    ]);

    $submission = Submission::factory()->create([
        'assignment_id' => $assignment->id,
        'student_id' => $student->id,
        'grade' => null,
        'feedback' => null,
    ]);

    $response = actingAs($teacher)->post(route('school.submissions.grade', $submission), [
        'grade' => 85,
        'feedback' => 'Good work!',
    ]);

    $response->assertRedirect();
    assertDatabaseHas('submissions', [
        'id' => $submission->id,
        'grade' => 85,
        'feedback' => 'Good work!',
    ]);
});

it('prevents teachers from grading other teachers submissions', function () {
    $teacher1 = User::factory()->create();
    $teacher1->assignRole('teacher');

    $teacher2 = User::factory()->create();
    $teacher2->assignRole('teacher');

    $student = User::factory()->create();
    $student->assignRole('student');

    $assignment = Assignment::factory()->create(['teacher_id' => $teacher1->id]);

    $submission = Submission::factory()->create([
        'assignment_id' => $assignment->id,
        'student_id' => $student->id,
    ]);

    $response = actingAs($teacher2)->post(route('school.submissions.grade', $submission), [
        'grade' => 85,
        'feedback' => 'Good work!',
    ]);

    $response->assertForbidden();
});

it('validates submission content', function () {
    $student = User::factory()->create();
    $student->assignRole('student');

    $teacher = User::factory()->create();
    $teacher->assignRole('teacher');

    $assignment = Assignment::factory()->create(['teacher_id' => $teacher->id]);

    $response = actingAs($student)->post(route('school.submissions.store', $assignment), [
        'content' => 'short',
    ]);

    $response->assertSessionHasErrors(['content']);
});

it('validates grade is within assignment max points', function () {
    $teacher = User::factory()->create();
    $teacher->assignRole('teacher');

    $student = User::factory()->create();
    $student->assignRole('student');

    $assignment = Assignment::factory()->create([
        'teacher_id' => $teacher->id,
        'max_points' => 100,
    ]);

    $submission = Submission::factory()->create([
        'assignment_id' => $assignment->id,
        'student_id' => $student->id,
    ]);

    $response = actingAs($teacher)->post(route('school.submissions.grade', $submission), [
        'grade' => 150,
        'feedback' => 'Excellent!',
    ]);

    $response->assertSessionHasErrors(['grade']);
});

it('allows students to update their submissions', function () {
    $student = User::factory()->create();
    $student->assignRole('student');

    $teacher = User::factory()->create();
    $teacher->assignRole('teacher');

    $assignment = Assignment::factory()->create(['teacher_id' => $teacher->id]);

    $submission = Submission::factory()->create([
        'assignment_id' => $assignment->id,
        'student_id' => $student->id,
        'content' => 'Original content',
    ]);

    $response = actingAs($student)->post(route('school.submissions.store', $assignment), [
        'content' => 'Updated submission content with more details.',
    ]);

    $response->assertRedirect();
    assertDatabaseHas('submissions', [
        'id' => $submission->id,
        'content' => 'Updated submission content with more details.',
    ]);
});
