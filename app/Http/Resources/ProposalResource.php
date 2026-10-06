<?php

namespace App\Http\Resources;

use App\Models\FreelancerProfile;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProposalResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'=> $this->id,
            'bid_amount'=>"$ ".$this->bid_amount,
            'estimated_days' => "{$this->estimated_days} days",
            'cover_letter'   => $this->cover_letter,
            'status'        => $this->status,
            'created_at'    => $this->created_at?->diffForHumans(),
            'freelancer'=>FreelancerResource::make($this->freelancer?->freelancerProfile),
        ];
    }
}
