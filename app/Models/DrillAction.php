<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DrillAction extends Model
{
    use HasFactory;

    protected $fillable = [
        'drill_record_id',
        'action_description',
        'action_owner',
        'action_status_id',
        'due_date',
        'created_by_user_id',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'completed_at' => 'datetime',
        ];
    }

    public function drillRecord(): BelongsTo
    {
        return $this->belongsTo(DrillRecord::class);
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(ActionStatus::class, 'action_status_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }
}
