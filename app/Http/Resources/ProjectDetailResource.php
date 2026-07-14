<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'=>$this->id,
            'title'=>$this->title,
            'description'=>$this->description,
            'budget_type'=>$this->budget_type,
            'budget'=>$this->budget_display,
            'required_experience'=>$this->required_experience,
            'deadline'=>$this->deadline_display,
            'status'=>$this->status->value,
            'created_at'=>$this->created_at?->toIso8601String(),
            'client'=>UserResource::make($this->whenLoaded('client')),
            'tags' => TagResource::collection($this->whenLoaded('tags')),
            'attachments' => AttachmentResource::collection($this->whenLoaded('attachments')),
            'reviews' => ReviewResource::collection($this->whenLoaded('reviews')),
            'proposals' => ProposalResource::collection($this->whenLoaded('proposals')),
            'stats' => [
                'proposals_count' => $this->whenCounted('proposals'),
                'reviews_avg_rating' => $this->whenNotNull($this->reviews_avg_rating),
            ],

        ];
    }
}
