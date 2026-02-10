<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Design extends Model
{
    use HasFactory;

    public const STATUS_DRAFT = 'draft';
    public const STATUS_PUBLISHED = 'published';
    public const STATUS_ARCHIVED = 'archived';

    protected $fillable = [
        'user_id',
        'name',
        'slug',
        'canvas_json',
        'meta',
        'status',
        'autosaved_at',
    ];

    protected function casts(): array
    {
        return [
            'canvas_json' => 'array',
            'meta' => 'array',
            'autosaved_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function versions(): HasMany
    {
        return $this->hasMany(DesignVersion::class);
    }

    public function assets(): HasMany
    {
        return $this->hasMany(Asset::class);
    }
}
