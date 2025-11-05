<?php

namespace Modules\School\Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\School\Models\Assignment;
use Modules\School\Models\Submission;

class SubmissionFactory extends Factory
{
    protected $model = Submission::class;

    public function definition(): array
    {
        return [
            'assignment_id' => Assignment::factory(),
            'student_id' => User::factory(),
            'content' => fake()->paragraphs(5, true),
            'grade' => null,
            'feedback' => null,
            'submitted_at' => fake()->dateTimeBetween('-1 week', 'now'),
        ];
    }

    public function graded(): static
    {
        return $this->state(fn (array $attributes) => [
            'grade' => fake()->numberBetween(50, 100),
            'feedback' => fake()->paragraph(),
        ]);
    }

    public function notSubmitted(): static
    {
        return $this->state(fn (array $attributes) => [
            'content' => null,
            'submitted_at' => null,
        ]);
    }
}

