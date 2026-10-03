<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Skill extends Model
{
    protected $fillable=['name','slug'];

    public function freelancerProfiles():BelongsToMany{
        return $this->belongsToMany(FreelancerProfile::class,'freelancer_skills')
                    ->withPivot('years_of_experience')
                    ->withTimestamps();
    }
}
