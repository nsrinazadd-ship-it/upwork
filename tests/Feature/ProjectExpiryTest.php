<?php

namespace Tests\Unit;

use App\Models\Project;
use Tests\TestCase;

class ProjectExpiryTest extends TestCase
{
    public function test_open_project_with_a_past_deadline_is_expired(): void
    {
        $project = new Project(['status' => 'open', 'deadline' => now()->subDay()]);

        $this->assertTrue($project->is_expired);
    }

    public function test_open_project_with_a_future_deadline_is_not_expired(): void
    {
        $project = new Project(['status' => 'open', 'deadline' => now()->addDay()]);

        $this->assertFalse($project->is_expired);
    }


}
