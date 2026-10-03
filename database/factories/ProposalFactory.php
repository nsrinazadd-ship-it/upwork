<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProposalFactory extends Factory
{
    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'freelancer_id' => User::factory()->state(['role' => 'freelancer']),
            'cover_letter' => fake()->paragraphs(2, true),
            'bid_amount' => fake()->numberBetween(5000, 100000), // بالسنتات
            'estimated_days' => fake()->numberBetween(3, 30),
            'status' => 'pending',
        ];
    }
}
