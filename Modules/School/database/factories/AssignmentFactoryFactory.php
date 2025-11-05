<?php

namespace Modules\School\Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\School\Models\Assignment;

class AssignmentFactory extends Factory
{
    protected $model = Assignment::class;

    public function definition(): array
    {
        return [
            'teacher_id' => User::factory(),
            'title' => fake()->sentence(),
            'description' => fake()->paragraphs(3, true),
            'due_date' => fake()->dateTimeBetween('now', '+1 month'),
            'max_points' => fake()->randomElement([50, 75, 100, 150]),
        ];
    }
}

