<?php

namespace App\Models;

use App\Enums\ProposalSatuts;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Override;

class Proposal extends Model
{
    protected $fillable = [
        'project_id',
        'freelancer_id',
        'cover_letter',
        'bid_amount',
        'estimated_days',
        'status',
    ];


    #[Override]
    protected function casts()
    {
        return [
            'status'=>ProposalSatuts::class,
        ];
    }


    protected function bidAmount():Attribute{
        return Attribute::make(
            get:fn(int $value)=> $value/100,
            set:fn(mixed $value)=>(int)(floatval($value)*100),
        );
    }

    public function project():BelongsTo{
        return $this->belongsTo(Project::class);
    }

    public function freelancer():BelongsTo{
        return $this->belongsTo(User::class,'freelancer_id');
    }
    public function attachments():MorphMany{
        return $this->morphMany(Attachment::class,'attachable');
    }
}
