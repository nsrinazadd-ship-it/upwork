<?php

namespace App\Http\Requests\Api;

use App\Models\Project;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class ReviewRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $project=Project::with(['proposal'=>function($query){
            $query->where('status','accepted');
        }])->find($this->project_id);
        if(!$project) return false;

        $userId=Auth::id();
        $acceptedProposal=$project->proposals->first();
        $freelancerId=$acceptedProposal?$acceptedProposal->freelancer_id:null;

        $isPartofProject=($project->client_id === $userId || $freelancerId === $userId);

        return $isPartofProject && $project->status->value ==='completed';

    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'project_id' => ['required', 'exists:projects,id'],
            'rating'     => ['required', 'integer', 'min:1', 'max:5'],
            'comment'    => ['required', 'string', 'min:5', 'max:1000'],
        ];
    }
}
