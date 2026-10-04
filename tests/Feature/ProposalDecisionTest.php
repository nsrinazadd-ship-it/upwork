<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Proposal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProposalDecisionTest extends TestCase
{
    use RefreshDatabase;

    public function test_client_can_accept_pending_proposal_on_their_project(): void
    {
        $client = User::factory()->create(['role' => 'client']);
        $freelancer = User::factory()->create(['role' => 'freelancer']);
        $project = Project::factory()->create(['client_id' => $client->id, 'status' => 'open']);
        $proposal = Proposal::factory()->create([
            'project_id' => $project->id,
            'freelancer_id' => $freelancer->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($client)->patchJson("/api/proposals/{$proposal->id}/accept");

        $response->assertStatus(200);
        $this->assertDatabaseHas('proposals', ['id' => $proposal->id, 'status' => 'accepted']);
        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
            'status' => 'in_progress',
            'freelancer_id' => $freelancer->id,
        ]);
    }

    public function test_accepting_a_proposal_auto_rejects_other_pending_proposals(): void
    {
        $client = User::factory()->create(['role' => 'client']);
        $project = Project::factory()->create(['client_id' => $client->id, 'status' => 'open']);

        $winner = Proposal::factory()->create(['project_id' => $project->id, 'status' => 'pending']);
        $loser = Proposal::factory()->create(['project_id' => $project->id, 'status' => 'pending']);

        $this->actingAs($client)->patchJson("/api/proposals/{$winner->id}/accept");

        $this->assertDatabaseHas('proposals', ['id' => $loser->id, 'status' => 'rejected']);
    }

    public function test_someone_else_cannot_accept_proposal_on_a_project_that_is_not_theirs(): void
    {
        $client = User::factory()->create(['role' => 'client']);
        $otherClient = User::factory()->create(['role' => 'client']);
        $project = Project::factory()->create(['client_id' => $client->id, 'status' => 'open']);
        $proposal = Proposal::factory()->create(['project_id' => $project->id, 'status' => 'pending']);

        $response = $this->actingAs($otherClient)->patchJson("/api/proposals/{$proposal->id}/accept");

        $response->assertStatus(403);
    }

    public function test_client_can_reject_pending_proposal(): void
    {
        $client = User::factory()->create(['role' => 'client']);
        $project = Project::factory()->create(['client_id' => $client->id, 'status' => 'open']);
        $proposal = Proposal::factory()->create(['project_id' => $project->id, 'status' => 'pending']);

        $response = $this->actingAs($client)->patchJson("/api/proposals/{$proposal->id}/reject");

        $response->assertStatus(200);
        $this->assertDatabaseHas('proposals', ['id' => $proposal->id, 'status' => 'rejected']);
    }

    public function test_someone_else_cannot_reject_proposal_on_a_project_that_is_not_theirs(): void
    {
        $client = User::factory()->create(['role' => 'client']);
        $otherClient = User::factory()->create(['role' => 'client']);
        $project = Project::factory()->create(['client_id' => $client->id, 'status' => 'open']);
        $proposal = Proposal::factory()->create(['project_id' => $project->id, 'status' => 'pending']);

        $response = $this->actingAs($otherClient)->patchJson("/api/proposals/{$proposal->id}/reject");

        $response->assertStatus(403);
    }

    public function test_cannot_reject_a_proposal_that_was_already_accepted(): void
    {
        $client = User::factory()->create(['role' => 'client']);
        $project = Project::factory()->create(['client_id' => $client->id, 'status' => 'in_progress']);
        $proposal = Proposal::factory()->create(['project_id' => $project->id, 'status' => 'accepted']);

        $response = $this->actingAs($client)->patchJson("/api/proposals/{$proposal->id}/reject");

        $response->assertStatus(422);
    }
}
