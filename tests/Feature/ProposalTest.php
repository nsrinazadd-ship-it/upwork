<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Project;
use App\Models\Proposal;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ProposalTest extends TestCase
{
    use RefreshDatabase;

    private function openProject(User $client): Project
    {
        return Project::factory()->create([
            'client_id' => $client->id,
            'status' => 'open',
        ]);
    }


    public function test_freelancer_can_submit_proposal_on_open_project(): void
    {
        $client = User::factory()->create(['role' => 'client']);
        $freelancer = User::factory()->create(['role' => 'freelancer']);
        $project = $this->openProject($client);

        $response = $this->actingAs($freelancer)->postJson('/api/proposals', [
            'project_id' => $project->id,
            'cover_letter' => str_repeat('خبرة واسعة بهالمجال. ', 10),
            'bid_amount' => 500,
            'estimated_days' => 7,
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('proposals', [
            'project_id' => $project->id,
            'freelancer_id' => $freelancer->id,
        ]);
    }

    public function test_client_cannot_submit_proposal(): void
    {
        $client = User::factory()->create(['role' => 'client']);
        $project = $this->openProject($client);

        $response = $this->actingAs($client)->postJson('/api/proposals', [
            'project_id' => $project->id,
            'cover_letter' => str_repeat('نص تجريبي. ', 10),
            'bid_amount' => 500,
            'estimated_days' => 7,
        ]);

        $response->assertStatus(403);
    }

    public function test_freelancer_cannot_submit_proposal_twice_on_same_project(): void
    {
        $client = User::factory()->create(['role' => 'client']);
        $freelancer = User::factory()->create(['role' => 'freelancer']);
        $project = $this->openProject($client);

        Proposal::factory()->create([
            'project_id' => $project->id,
            'freelancer_id' => $freelancer->id,
        ]);

        $response = $this->actingAs($freelancer)->postJson('/api/proposals', [
            'project_id' => $project->id,
            'cover_letter' => str_repeat('نص تجريبي. ', 10),
            'bid_amount' => 500,
            'estimated_days' => 7,
        ]);

        $response->assertStatus(403);
    }

    public function test_client_sees_all_proposals_on_their_project(): void
    {
        $client = User::factory()->create(['role' => 'client']);
        $project = $this->openProject($client);

        Proposal::factory()->count(3)->create(['project_id' => $project->id]);

        $response = $this->actingAs($client)->getJson('/api/proposals?project_id='.$project->id);

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data.data');
    }

    public function test_freelancer_only_sees_their_own_proposal_not_competitors(): void
    {
        $client = User::factory()->create(['role' => 'client']);
        $project = $this->openProject($client);

        $me = User::factory()->create(['role' => 'freelancer']);
        $competitor = User::factory()->create(['role' => 'freelancer']);

        Proposal::factory()->create(['project_id' => $project->id, 'freelancer_id' => $me->id]);
        Proposal::factory()->create(['project_id' => $project->id, 'freelancer_id' => $competitor->id]);

        $response = $this->actingAs($me)->getJson('/api/proposals?project_id='.$project->id);

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data.data');
    }


}
