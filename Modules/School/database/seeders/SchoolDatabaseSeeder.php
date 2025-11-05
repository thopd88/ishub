<?php

namespace Modules\School\Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Modules\School\Models\Assignment;
use Modules\School\Models\Submission;
use Spatie\Permission\Models\Role;

class SchoolDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->createRoles();
        $users = $this->createUsers();
        $this->createStudentParentRelationships($users);
        $this->createAssignmentsAndSubmissions($users);
    }

    protected function createRoles(): void
    {
        $roles = ['teacher', 'student', 'parent'];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }
    }

    protected function createUsers(): array
    {
        $teacher = User::firstOrCreate(
            ['email' => 'teacher@example.com'],
            [
                'name' => 'John Teacher',
                'password' => Hash::make('password'),
            ]
        );
        $teacher->assignRole('teacher');

        $student1 = User::firstOrCreate(
            ['email' => 'student1@example.com'],
            [
                'name' => 'Alice Student',
                'password' => Hash::make('password'),
            ]
        );
        $student1->assignRole('student');

        $student2 = User::firstOrCreate(
            ['email' => 'student2@example.com'],
            [
                'name' => 'Bob Student',
                'password' => Hash::make('password'),
            ]
        );
        $student2->assignRole('student');

        $parent1 = User::firstOrCreate(
            ['email' => 'parent1@example.com'],
            [
                'name' => 'Mary Parent',
                'password' => Hash::make('password'),
            ]
        );
        $parent1->assignRole('parent');

        $parent2 = User::firstOrCreate(
            ['email' => 'parent2@example.com'],
            [
                'name' => 'David Parent',
                'password' => Hash::make('password'),
            ]
        );
        $parent2->assignRole('parent');

        return [
            'teacher' => $teacher,
            'students' => [$student1, $student2],
            'parents' => [$parent1, $parent2],
        ];
    }

    protected function createStudentParentRelationships(array $users): void
    {
        DB::table('student_parent_relationships')->insertOrIgnore([
            [
                'student_id' => $users['students'][0]->id,
                'parent_id' => $users['parents'][0]->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'student_id' => $users['students'][1]->id,
                'parent_id' => $users['parents'][1]->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    protected function createAssignmentsAndSubmissions(array $users): void
    {
        $assignment1 = Assignment::firstOrCreate(
            [
                'teacher_id' => $users['teacher']->id,
                'title' => 'Introduction to Laravel',
            ],
            [
                'description' => 'Create a simple Laravel application with CRUD operations.',
                'due_date' => now()->addDays(7),
                'max_points' => 100,
            ]
        );

        $assignment2 = Assignment::firstOrCreate(
            [
                'teacher_id' => $users['teacher']->id,
                'title' => 'Database Design Project',
            ],
            [
                'description' => 'Design a normalized database schema for an e-commerce system.',
                'due_date' => now()->addDays(14),
                'max_points' => 150,
            ]
        );

        Submission::firstOrCreate(
            [
                'assignment_id' => $assignment1->id,
                'student_id' => $users['students'][0]->id,
            ],
            [
                'content' => 'Here is my completed Laravel CRUD application. I implemented all features as requested.',
                'submitted_at' => now()->subDays(1),
                'grade' => 95,
                'feedback' => 'Excellent work! Very clean code and good practices.',
            ]
        );

        Submission::firstOrCreate(
            [
                'assignment_id' => $assignment1->id,
                'student_id' => $users['students'][1]->id,
            ],
            [
                'content' => 'My Laravel CRUD application submission with all required functionality.',
                'submitted_at' => now()->subHours(12),
                'grade' => null,
                'feedback' => null,
            ]
        );

        // Note: Not creating an empty submission for assignment2, as students need to submit content
        // This demonstrates a pending assignment with no submission yet
    }
}
