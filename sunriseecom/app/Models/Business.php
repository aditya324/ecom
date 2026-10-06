<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'legal_name',
    'gstin',
    'address',
    'email',
    'instagram',
])]
class Business extends Model
{
    public static function current(): self
    {
        return static::query()->first() ?? static::query()->create([
            'legal_name' => 'Sunrise Digital',
        ]);
    }
}
