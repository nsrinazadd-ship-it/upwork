<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Freelancer\Proposal\StoreProposalRequest;
use App\Models\Proposal;
use App\Models\Project;
use App\Http\Resources\ProposalResource;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class ProposalController extends Controller
{
    use ApiResponse;

    public function index(Request $request):JsonResponse
    {
        $request->validate(['project_id' => 'required|exists:projects,id']);
        $project=Project::findOrFail($request->project_id);
        $query=Proposal::where('project_id',$project->id)
        ->with(['freelancer.freelancerProfile.skills'])
        ->latest();

        if(Auth::id() !== $project->client_id){
            $query->where('freelancer_id',Auth::id());
        }

        $proposals=$query->paginate(10);

        return $this->success(
            ProposalResource::collection($proposals)->response()->getData(true),
            'Proposals retrieved successfully.'
        );

    }


    public function store(StoreProposalRequest $request):JsonResponse
    {
        $validate=$request->validated();
        $project=Project::findOrFail($validate['project_id']);
        if($project->status->value !== 'open'){
            return $this->error("You cannot submit a proposal to a {$project->status->value} project.", 422);
        }

        if(Proposal::where('project_id', $validate['project_id'])->where('freelancer_id', Auth::id())->exists()){
            return $this->error('You have already submitted a proposal for this project.', 422);
        }

        $validate['freelancer_id']=Auth::id();
        $proposal=Proposal::create($validate);

        return $this->success(
            new ProposalResource($proposal),
            'Your proposal has been submitted successfully!'
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(Proposal $proposal)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Proposal $proposal)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Proposal $proposal)
    {
        //
    }

    public function accept(Proposal $proposal):JsonResponse{
        Gate::authorize('manage',$proposal);
        if($proposal->project->status->value !=='open'){
            return $this->error('This project is no longer open for accepting proposals.', 422);}

            DB::transaction(function() use ($proposal){
                $proposal->update(['status'=>'accepted']);
                $proposal->project->update([
                    'status'=>'in_progress',
                    'freelancer_id'=>$proposal->freelancer_id
                ]);
            });

            $proposal->project->proposals()
            ->where('id','!=',$proposal->id)
            ->where('status','pending')
            ->update(['status' => 'rejected']);

            return $this->success(
                new ProposalResource($proposal->load('project')),
                'Proposal accepted successfully. Project is now in progress.'
            );
    }

    public function reject(Proposal $proposal):JsonResponse{
        Gate::authorize('manage',$proposal);
        if($proposal->status->value !== 'pending'){
            return $this->error('Only pending proposals can be rejected.', 422);
        }

        $proposal->update(['status' => 'rejected']);

        return $this->success(new ProposalResource($proposal)
        ,'Proposal has been rejected successfully.');
    }
}
