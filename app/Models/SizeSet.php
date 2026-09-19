<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SizeSet extends Model
{
    protected $fillable = [
        'tipo',
        'valor',
        'orden',
        'estado',
        'business_id',
    ];

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class, 'size_id');
    }

    public function locations(): BelongsToMany
    {
        return $this->belongsToMany(BusinessLocation::class, 'business_location_size_set')
            ->withPivot('estado')
            ->withTimestamps();
    }
}
