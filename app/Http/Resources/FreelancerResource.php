<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FreelancerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                 => $this->id,
            'name'               => $this->user?->full_name,
            'profile_image'      =>$this->profile_image,
            'title'              => $this->title,
            'bio'                => $this->bio,
            'hourly_rate'        => $this->hourly_rate ? "$ {$this->hourly_rate}/hr" : 'Negotiable',
            'is_verified'        =>(bool)$this->is_verified,
            'location'           =>[
                'city'=>$this->city?->name,
                'country'=>$this->country?->name,
            ],
            'skills'=>SkillResource::collection($this->whenLoaded('skills')),
            'reviews'=>ReviewResource::collection($this->whenLoaded('reviews')),
        ];
    }
}
