<?php

namespace App\Models;

use App\Enums\ProjectStatus;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Project extends Model
{
    use HasFactory;
    protected $fillable = [
        'client_id',
        'freelancer_id',
        'category_id',
        'title',
        'description',
        'budget_type',
        'budget',
        'required_experience',
        'deadline',
        'status',
    ];

    protected function casts():array{
        return [
            'deadline'=> 'datetime',
            'status' => ProjectStatus::class,
        ];
    }

    protected function isExpired():Attribute{
        return Attribute::make(
            get: fn()=>$this->status === ProjectStatus::OPEN
            && $this->deadline !== null
            && $this ->deadline->isPast(),
        );
    }

    protected function budgetDisplay():Attribute{
        return Attribute::make(
            get: function(mixed $value , array $attributes){
                $amount = $this->budget;
                return $attributes['budget_type']==='fixed'?
                "{$amount} USD":"$ {$amount}/hr" ;
            }
        );
    }



    protected function budget():Attribute{
        return Attribute::make(
            get:fn(int $value)=> $value/100,
            set:fn(mixed $value)=>(int)(floatval($value)*100),
        );
    }

    protected function deadlineDisplay():Attribute{
        return Attribute::make(
            get: function(){

                if (!$this->deadline) {
                    return 'No deadline set';
                }

                if($this->deadline->isPast()){
                    return 'Expired';
                }
                $daysLeft=now()->diffInDays($this->deadline);
                return ($daysLeft)===0? "Expired today": "{$daysLeft} days left";
            }
        );
    }

    ////////  scopes  //////////

    public function scopeOpen(Builder $query):Builder{
        return $query->where('status' , ProjectStatus::OPEN);
    }

    public function scopeActive(Builder $query):Builder{
        return $query->where('status',ProjectStatus::OPEN)
                        ->where('deadline','>',now());
    }

    public function scopeMinBudget(Builder $query ,mixed $amount):Builder{
        if(!$amount){return $query;}
        $amountInCent = (int)(floatval($amount) * 100);
        return $query->where('budget', '>=', $amountInCent);
    }

    public function scopeThisMonth(Builder $query):Builder{
        return $query->where('created_at','>=',now()->startOfMonth());
    }

    public function scopeExpired(Builder $query):Builder{
        return $query->where('status', ProjectStatus::OPEN)
            ->where('deadline','<',now());
    }


    public function scopeByBudgetType(Builder $query ,string $type):Builder{
        return $query->where('budget_type',$type);
    }

    public function scopeForCard(Builder $query):Builder{
        return $query->with(['client','tags']);
    }
    public function client():BelongsTo{
        return $this->belongsTo(User::class,'client_id');
    }

    public function proposals():HasMany{
        return $this->hasMany(Proposal::class);
    }

    public function tags():BelongsToMany{
        return $this->belongsToMany(Tag::class,'project_tags')
                    ->withTimestamps();
    }

    public function reviews():HasMany{
        return $this->hasMany(Review::class);
    }

    public function attachments():MorphMany{
        return $this->morphMany(Attachment::class,'attachable');
    }

    public function category():BelongsTo{
        return $this->belongsTo(Category::class);
    }
}
