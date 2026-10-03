<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class FreelancerProfile extends Model
{
    protected $fillable=[
        'user_id',
        'title',
        'bio',
        'hourly_rate',
        'phone_number',
        'profile_image',
        'availability',
        'is_verified',
        'portfolio_links',
    ];

    protected function casts():array{
        return[
            'portfolio_links'=>'array',
            'is_verified'=> 'boolean',
            ];
    }
    protected function hourlyRate():Attribute{
        return Attribute::make(
            get:fn(int $value)=>$value/100,
            set:fn(mixed $value)=>(int)(floatval($value)*100),
        );
    }

    protected function phoneNumber():Attribute{
        return Attribute::make(
            set:fn(string $value)=>preg_replace('/[^0-9]/','',$value)
        );
    }

    protected function profileImage():Attribute{
        return Attribute::make(
            get: function(?string $value){
                if($value){
                    return asset('storage/'.$value);
                }
                $name = $this->user ? urlencode($this->user->full_name) : 'User';
            return "https://ui-avatars.com/api/?name={$name}&background=6366f1&color=fff&bold=true";

            }
        );
    }

        public function scopeVerifiedActive(Builder $query):Builder{
            return $query->where('is_verified', true)
            ->whereHas('user', function ($q) {
                    $q->whereNotNull('email_verified_at');});

    }


    public function scopeAvailable(Builder $query):Builder{
        return $query->where('availability','available');
    }

    public function scopeHighestRated(Builder $query):Builder{
        return $query->withAvg('user.reviewsReceived','rating')
        ->orderBy('user_reviews_received_avg_rating','desc');
    }

    public function user():BelongsTo{
        return $this->belongsTo(User::class);
    }

    public function skills():BelongsToMany{
        return $this->belongsToMany(Skill::class,'Freelancer_skills')
                        ->withPivot('years_of_experience')
                        ->withTimestamps();
    }

    public function proposals():\Illuminate\Database\Eloquent\Relations\HasManyThrough{
        return $this->hasManyThrough(
            Proposal::class,
            User::class,
            'id',
            'freelancer_id',
            'user_id',
            'id'
        );
    }
}
