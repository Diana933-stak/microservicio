<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sprint extends Model
{
    protected $table = 'sprints';

    protected $fillable = [
        'nombre',
        'fecha_inicio',
        'fecha_fin',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(RetroItem::class, 'sprint_id');
    }
}
