<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Proposal;
use App\Models\Review;
use App\Models\User;
use Faker\Factory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProjectShowTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_does_not_see_the_proposals_of_a_project(): void
    {
        $client  = User::factory()->create(['role' => 'client']);
        $project = Project::factory()->create(['client_id' => $client->id, 'status' => 'open']);
        Proposal::factory()->count(2)->create(['project_id' => $project->id]);

        $response = $this->getJson("/api/projects/{$project->id}");

        $response->assertOk();
        $response->assertJsonMissingPath('data.proposals');
    }

    public function test_project_owner_sees_the_proposal_of_their_project():void{
        $client=User::factory()->create(['role'=>'client']);
        $project=Project::factory()->create(['client_id'=>$client->id,'status'=>'open']);
        $proposal=Proposal::factory()->count(3)->create(['project_id'=>$project->id]);
        Sanctum::actingAs($client);
        $response=$this->getJson("/api/projects/{$project->id}");

        $response->assertOk();
        $response->assertJsonCount(3,'data.proposals');
    }
    public function test_proposal_resource_returns_the_real_values(): void
{
    $client  = User::factory()->create(['role' => 'client']);
    $project = Project::factory()->create(['client_id' => $client->id, 'status' => 'open']);
    $proposal = Proposal::factory()->create([
        'project_id'     => $project->id,
        'estimated_days' => 9,
    ]);

    Sanctum::actingAs($client);
    $response = $this->getJson("/api/projects/{$project->id}");

    $response->assertJsonPath('data.proposals.0.estimated_days', '9 days');
    $response->assertJsonPath('data.proposals.0.bid_amount', '$ ' . $proposal->bid_amount);
    $response->assertJsonPath('data.proposals.0.cover_letter', $proposal->cover_letter);
}
public function test_client_email_is_not_exposed_publicly(): void
{
    $client  = User::factory()->create(['role' => 'client', 'email' => 'secret.client@example.com']);
    $project = Project::factory()->create(['client_id' => $client->id, 'status' => 'open']);

    $response = $this->getJson("/api/projects/{$project->id}");

    $response->assertOk();
    $this->assertStringNotContainsString('secret.client@example.com', $response->getContent());
    $response->assertJsonPath('data.client.name', $client->full_name);
}
public function test_client_rating_is_shown_instead_of_the_email(): void
{
    $client  = User::factory()->create(['role' => 'client']);
    $project = Project::factory()->create(['client_id' => $client->id, 'status' => 'open']);

    foreach ([4, 5] as $rating) {
        Review::create([
            'project_id'  => $project->id,
            'reviewer_id' => User::factory()->create(['role' => 'freelancer'])->id,
            'reviewee_id' => $client->id,
            'rating'      => $rating,
            'comment'     => 'Good',
        ]);
    }

    $response = $this->getJson("/api/projects/{$project->id}");

    $response->assertOk();
    $response->assertJsonPath('data.client.rating', '4.5 ⭐');
    $response->assertJsonMissingPath('data.client.email');
}
}
