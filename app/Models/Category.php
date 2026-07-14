<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Category extends Model
{
    protected $fillable=['name', 'slug',];

    protected function name():Attribute{
        return Attribute::make(
            set:fn(string $value)=>[
                'name'=>$value,
                'slug'=>Str::slug($value),
            ]
        );
    }

    public function projects():HasMany{
        return $this->hasMany(Project::class);
    }
}
