<?php

namespace App\Http\Requests\Freelancer\Proposal;

use App\Models\Project;
use App\Models\Proposal;
use App\Rules\ProhibitOffensiveContent;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreProposalRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user=$this->user();
        $projectId=$this->input('project_id');

        if($user->role !=='freelancer'){
            abort(403,'عذراً، يحق للمستقلين فقط تقديم العروض على المشاريع.');
        }

        $project=Project::find($projectId);
        if(!$project){
            abort(404,'المشروع غير موجود.');
        }

        if($project->status->value !== 'open' ){
            abort(403,'عذراً، هذا المشروع مغلق ولم يعد يستقبل عروضاً جديدة.');
        }

        if($user->id === $project->client_id){
            abort(403,'لا يمكنك تقديم عرض على مشروع قمت بنشره بنفسك.');
        }

        $alreadyAplied=Proposal::where('project_id',$projectId)
        ->where('freelancer_id',$user->id)
        ->exists();

        if($alreadyAplied){
            abort(403,'لقد قمت بتقديم عرض على هذا المشروع مسبقاً، لا يمكنك التقديم مرتين.');
        }
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'project_id' => [
                'required',
                'exists:projects,id'
            ],

            'cover_letter' => [
                'required',
                'string',
                'min:100',
                new ProhibitOffensiveContent()
            ],

            'bid_amount' => [
                'required',
                'numeric',
                'min:1'
            ],
        ];
    }
}
