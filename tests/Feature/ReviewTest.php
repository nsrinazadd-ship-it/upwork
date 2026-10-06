<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Proposal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewTest extends TestCase
{
    use RefreshDatabase;

    private function completedProjectWithAcceptedProposal(): array
    {
        $client = User::factory()->create(['role' => 'client']);
        $freelancer = User::factory()->create(['role' => 'freelancer']);
        $project = Project::factory()->create(['client_id' => $client->id, 'status' => 'completed']);
        Proposal::factory()->create([
            'project_id' => $project->id,
            'freelancer_id' => $freelancer->id,
            'status' => 'accepted',
        ]);

        return [$client, $freelancer, $project];
    }

    public function test_client_can_review_freelancer_on_completed_project(): void
    {
        [$client, $freelancer, $project] = $this->completedProjectWithAcceptedProposal();

        $response = $this->actingAs($client)->postJson('/api/reviews', [
            'project_id' => $project->id,
            'rating' => 5,
            'comment' => 'تعامل ممتاز وتسليم بالوقت المحدد.',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('reviews', [
            'project_id' => $project->id,
            'reviewer_id' => $client->id,
            'reviewee_id' => $freelancer->id,
        ]);
    }

    public function test_cannot_review_a_project_that_is_not_completed(): void
    {
        $client = User::factory()->create(['role' => 'client']);
        $freelancer = User::factory()->create(['role' => 'freelancer']);
        $project = Project::factory()->create(['client_id' => $client->id, 'status' => 'in_progress']);
        Proposal::factory()->create([
            'project_id' => $project->id,
            'freelancer_id' => $freelancer->id,
            'status' => 'accepted',
        ]);

        $response = $this->actingAs($client)->postJson('/api/reviews', [
            'project_id' => $project->id,
            'rating' => 5,
            'comment' => 'تقييم مبكر.',
        ]);

        $response->assertStatus(403);
    }

    public function test_someone_unrelated_to_the_project_cannot_review_it(): void
    {
        [, , $project] = $this->completedProjectWithAcceptedProposal();
        $stranger = User::factory()->create(['role' => 'client']);

        $response = $this->actingAs($stranger)->postJson('/api/reviews', [
            'project_id' => $project->id,
            'rating' => 5,
            'comment' => 'مش طرف بهالمشروع.',
        ]);

        $response->assertStatus(403);
    }

    public function test_cannot_submit_review_twice_for_same_project(): void
    {
        [$client, , $project] = $this->completedProjectWithAcceptedProposal();

        $this->actingAs($client)->postJson('/api/reviews', [
            'project_id' => $project->id,
            'rating' => 5,
            'comment' => 'أول تقييم.',
        ]);

        $response = $this->actingAs($client)->postJson('/api/reviews', [
            'project_id' => $project->id,
            'rating' => 4,
            'comment' => 'محاولة تاني تقييم.',
        ]);

        $response->assertStatus(422);
    }
}
