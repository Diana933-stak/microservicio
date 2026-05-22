<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RetroItem extends Model
{
    public const CATEGORIAS = ['accion', 'logro', 'impedimento', 'comentario', 'otro'];

    protected $table = 'retro_items';

    protected $fillable = [
        'sprint_id',
        'categoria',
        'descripcion',
        'cumplida',
        'fecha_revision',
    ];

    protected $casts = [
        'cumplida' => 'boolean',
    ];

    public function sprint(): BelongsTo
    {
        return $this->belongsTo(Sprint::class, 'sprint_id');
    }
}
