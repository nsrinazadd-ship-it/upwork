<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\Project\StoreProjectRequest;
use App\Http\Requests\Client\Project\UpdateProjectRequest;
use App\Models\Project;
use App\Http\Resources\ProjectResource;
use App\Http\Resources\ProjectDetailResource;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class ProjectController extends Controller
{
    use ApiResponse;
    public function index():JsonResponse
    {
        $projects=Project::open()
        ->with(['client','tags'])
        ->latest()
        ->paginate(15);
        return $this->success(
            ProjectResource::collection($projects)->response()->getData(true),
            'Open projects retrieved successfully.'
        );
    }


    public function store(StoreProjectRequest $request):JsonResponse
    {
        $validated=$request->validated();

        $validated['client_id']=Auth::id();
        $validated['status']='open';

        $project=DB::transaction(function()use ($validated){
            $newProject=Project::create($validated);
            $newProject->tags()->attach($validated['tags']);
            return $newProject;
        });

        return $this->success(
            new ProjectResource($project->load('tags')),
            'Project created successfully.'
        );

    }
    public function show(Project $project):JsonResponse
    {
        $project->load(
            'client',
            'tags',
            'attachments',
            'reviews.reviewer',
            'proposals.freelancer.skills'
        )->loadAvg('reviews', 'rating');

        return $this->success(
            new ProjectDetailResource($project),
            'Project details retrieved successfully.'
        );
    }

    public function update(UpdateProjectRequest $request, Project $project):JsonResponse
    {
        Gate::authorize('update',$project);

        $validated=$request->validated();

        DB::transaction(function() use ($validated,$project){
            $project->update($validated);
            $project->tags()->sync($validated['tags']);
        });
        return $this->success(
            new ProjectResource($project->load('tags')),
            'Project updated successfully.'
        );

    }

    public function destroy(Project $project)
    {
        Gate::authorize('delete',$project);
        $project->delete();

        return $this->success(null,'Project deleted successfully.');
    }
}
