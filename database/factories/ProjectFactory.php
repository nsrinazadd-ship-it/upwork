<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProjectFactory extends Factory
{
    public function definition(): array
    {
        return [
            'client_id' => User::factory()->state(['role' => 'client']),
            'category_id' => Category::factory(),
            'title' => fake()->sentence(4),
            'description' => fake()->paragraph(),
            'budget_type' => 'fixed',
            'budget' => fake()->numberBetween(10000, 500000), // بالسنتات
            'required_experience' => 'intermediate',
            'deadline' => now()->addDays(30),
            'status' => 'open',
        ];
    }
}
