<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ApiRequestLog extends Model
{
    const UPDATED_AT = null;

    protected $fillable = [
        'user_id',
        'url',
        'method',
        'status_code',
        'duration',
        'ip_address',
        'user_agent',
        'payload',
    ];

    public function user():BelongsTo{
        return $this->belongsTo(User::class)
        ->withDefault([
        'name' => 'Guest / Visitor'
        ]);
    }


}
