<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReviewResource extends JsonResource
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
            'rating'=>(float)$this->rating,
            'comment'=>$this->comment,
            'created_at'=>$this->created_at?->diffForHumans(),
            'reviewer'=>UserResource::make($this->whenLoaded('reviewer')),
        ];
    }
}
