<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return[
            'id'              => $this->id,
            'name'            => $this->name,
            'email'           => $this->email,
            'role'            => $this->role,
            'title'           => $this->title,
            'bio'             => $this->bio,
            'hourly_rate'     => $this->hourly_rate,
            'phone_number'    => $this->phone_number,
            'availability'    => (bool) $this->availability,
            'is_verified'     =>(bool)$this->is_verified,
            'profile_image' => $this->profile_image,
            'portfolio_links' =>$this->portfolio_links?? [],
            'created_at'      =>$this->created_at?->toIso8601String(),
        ];
    }
}
