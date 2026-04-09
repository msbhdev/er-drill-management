<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoleHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'rig_id',
        'person_name',
        'role',
        'effective_from',
        'effective_to',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'effective_from' => 'date',
            'effective_to' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function rig(): BelongsTo
    {
        return $this->belongsTo(Rig::class);
    }
}
