<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ReviewRequest;
use App\Models\Project;
use App\Models\Proposal;
use App\Models\Review;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    use ApiResponse;
    public function store(ReviewRequest $request):JsonResponse{
        $validated=$request->validated();
        $authId=Auth::id();

        $project=Project::findOrFail($validated['project_id']);
        $acceptedProposal=$project->proposals()->where('status','accepted')->first();

        if(!$acceptedProposal){
            return $this->error('No accepted freelancer found for this project.', 422);
        }

        $freelancerId=$acceptedProposal->freelancer_id;

        $alreadyReviewe=Review::where('project_id',$project->id)
        ->where('reviewer_id',$authId)
        ->exists();

        if($alreadyReviewe){
            return $this->error('You have already submitted a review for this project.', 422);
            }

            $revieweeId=($project->client_id === $authId)?$freelancerId:$project->client_id;

            $review=Review::create([
                'project_id'  => $project->id,
                'reviewer_id' => $authId,
                'reviewee_id' => $revieweeId,
                'rating'      => $validated['rating'],
                'comment'     => $validated['comment'],
                ]);

        return $this->created($review,'Review submitted successfully.');
    }
}
