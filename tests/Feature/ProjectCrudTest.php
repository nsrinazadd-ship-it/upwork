<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Project;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ProjectCrudTest extends TestCase
{
    use RefreshDatabase;

    private function validProjectPayload(array $overrides = []): array
    {
        $category = Category::factory()->create();
        $tags = Tag::factory()->count(2)->create();

        return array_merge([
            'title' => 'مشروع تطوير موقع تجارة إلكترونية متكامل',
            'description' => str_repeat('وصف تفصيلي للمشروع. ', 10),
            'category_id' => $category->id,
            'budget_type' => 'fixed',
            'budget' => 500,
            'deadline' => now()->addDays(30)->toDateString(),
            'tags' => $tags->pluck('id')->toArray(),
        ], $overrides);
    }

    public function test_client_can_create_project(): void
    {
        $client = User::factory()->create(['role' => 'client']);

        $response = $this->actingAs($client)->postJson('/api/projects', $this->validProjectPayload());

        $response->assertStatus(201);
        $this->assertDatabaseHas('projects', [
            'client_id' => $client->id,
            'status' => 'open',
        ]);
    }

    public function test_freelancer_cannot_create_project(): void
    {
        $freelancer = User::factory()->create(['role' => 'freelancer']);

        $response = $this->actingAs($freelancer)->postJson('/api/projects', $this->validProjectPayload());

        $response->assertStatus(403);
    }

    public function test_client_can_update_their_own_open_project(): void
    {
        $client = User::factory()->create(['role' => 'client']);
        $project = Project::factory()->create(['client_id' => $client->id, 'status' => 'open']);
        $tags = Tag::factory()->count(2)->create();

        $response = $this->actingAs($client)->putJson("/api/projects/{$project->id}", [
            'title' => 'عنوان محدّث للمشروع بشكل كامل',
            'description' => str_repeat('وصف محدّث للمشروع. ', 5),
            'budget' => 700,
            'deadline' => now()->addDays(20)->toDateString(),
            'tags' => $tags->pluck('id')->toArray(),
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('projects', ['id' => $project->id, 'budget' => 70000]);
    }

    public function test_client_cannot_update_someone_elses_project(): void
    {
        $owner = User::factory()->create(['role' => 'client']);
        $otherClient = User::factory()->create(['role' => 'client']);
        $project = Project::factory()->create(['client_id' => $owner->id, 'status' => 'open']);
        $tags = Tag::factory()->count(2)->create();

        $response = $this->actingAs($otherClient)->putJson("/api/projects/{$project->id}", [
            'title' => 'محاولة تعديل مشروع مش ملكي',
            'description' => str_repeat('نص. ', 10),
            'budget' => 700,
            'deadline' => now()->addDays(20)->toDateString(),
            'tags' => $tags->pluck('id')->toArray(),
        ]);

        $response->assertStatus(403);
    }

    public function test_client_cannot_update_project_that_is_already_in_progress(): void
    {
        $client = User::factory()->create(['role' => 'client']);
        $project = Project::factory()->create(['client_id' => $client->id, 'status' => 'in_progress']);
        $tags = Tag::factory()->count(2)->create();

        $response = $this->actingAs($client)->putJson("/api/projects/{$project->id}", [
            'title' => 'محاولة تعديل مشروع شغال حالياً',
            'description' => str_repeat('نص. ', 10),
            'budget' => 700,
            'deadline' => now()->addDays(20)->toDateString(),
            'tags' => $tags->pluck('id')->toArray(),
        ]);

        $response->assertStatus(403);
    }

    public function test_client_can_delete_their_own_open_project(): void
    {
        $client = User::factory()->create(['role' => 'client']);
        $project = Project::factory()->create(['client_id' => $client->id, 'status' => 'open']);

        $response = $this->actingAs($client)->deleteJson("/api/projects/{$project->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('projects', ['id' => $project->id]);
    }

    public function test_client_cannot_delete_someone_elses_project(): void
    {
        $owner = User::factory()->create(['role' => 'client']);
        $otherClient = User::factory()->create(['role' => 'client']);
        $project = Project::factory()->create(['client_id' => $owner->id, 'status' => 'open']);

        $response = $this->actingAs($otherClient)->deleteJson("/api/projects/{$project->id}");

        $response->assertStatus(403);
        $this->assertDatabaseHas('projects', ['id' => $project->id]);
    }

    public function test_anyone_can_view_a_single_open_project(): void
    {
        $project = Project::factory()->create(['status' => 'open']);

        $response = $this->getJson("/api/projects/{$project->id}");

        $response->assertStatus(200);
    }


    public function test_creating_a_project_still_works_when_the_moderation_api_is_down(): void
{
    Http::fake([
        'api.openai.com/*' => fn () => throw new ConnectionException('timeout'),
    ]);

    $client = User::factory()->create(['role' => 'client']);

    $this->actingAs($client)
        ->postJson('/api/projects', $this->validProjectPayload())
        ->assertStatus(201);
}


}
