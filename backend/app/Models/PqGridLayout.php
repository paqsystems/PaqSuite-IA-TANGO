<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modelo para layouts persistentes de grillas DevExtreme.
 *
 * @see docs/04-tareas/000-Generalidades/TR-001-layouts-grilla.md
 */
class PqGridLayout extends Model
{
    protected $table = 'pq_grid_layouts';

    protected $fillable = [
        'user_id',
        'proceso',
        'grid_id',
        'layout_name',
        'layout_data',
        'is_default',
    ];

    protected $casts = [
        'layout_data' => 'array',
        'is_default' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function scopeForProcesoGrid($query, string $proceso, string $gridId = 'default')
    {
        return $query->where('proceso', $proceso)->where('grid_id', $gridId);
    }

    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }
}
