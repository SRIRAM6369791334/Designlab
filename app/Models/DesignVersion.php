<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DesignVersion extends Model
{
    use HasFactory;

    protected $fillable = [
        'design_id',
        'version_number',
        'canvas_json',
        'snapshot_path',
        'created_by',
        'change_note',
    ];

    protected function casts(): array
    {
        return [
            'canvas_json' => 'array',
        ];
    }

    public function design(): BelongsTo
    {
        return $this->belongsTo(Design::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
