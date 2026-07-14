<?php

namespace App\Models;

//use Illuminate\Container\Attributes\Storage;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Storage;

class Attachment extends Model
{
    protected $fillable = [
        'file_name',
        'file_path',
        'file_type',
    ];

    protected function filePath():Attribute{
        return Attribute::make(
            get:fn(?string $value)=>$value?
                asset('storage/' . $value)
            :null,
        );
    }

    public function attachable():MorphTo{
        return $this->morphTo();
    }
}
