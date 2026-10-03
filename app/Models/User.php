<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'city_id',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    protected function fullName():Attribute{
        return Attribute::make(
            get: fn(mixed $value , array $attributes)=>"{$attributes['first_name']} {$attributes['last_name']}",
        );
    }

    protected function firstName():Attribute{
        return Attribute::make(
            set : fn(string $value)=> ucfirst(strtolower($value)),
        );
    }
    protected function lastName():Attribute{
        return Attribute::make(
            set: fn(string $value)=>ucfirst(strtolower($value)),
        );
    }

    protected function memberSince():Attribute{
        return Attribute::make(
            get : fn(mixed $value , array $attributes)=>'Member Since '.$this->created_at->format('F Y'),
        );
    }

    protected function ratingDisplay():Attribute{
        return Attribute::make(
            get: function(){
                $avarage=$this->reviewsReceived()->avg('rating');
                if(!$avarage){
                    return "لا توجد تقييمات بعد";
                }
                return number_format($avarage,1).' ⭐';
            }
        );
    }
    public function city():BelongsTo{
        return $this->belongsTo(City::class);
    }

    public function freelancerProfile():HasOne{
        return $this->hasOne(FreelancerProfile::class);
    }

    public function projects():HasMany{
        return $this->hasMany(Project::class ,'client_id');
    }

    public function proposals():HasMany{
        return $this->hasMany(Proposal::class,'freelancer_id');
    }

    public function reviewsWritten():HasMany{
        return $this->hasMany(Review::class,'reviewer_id');
    }

    public function reviewsReceived():HasMany{
        return $this->hasMany(Review::class,'reviewee_id');
    }

    public function apiRequestLogs():HasMany{
        return $this->hasMany(ApiRequestLog::class);
    }
}
