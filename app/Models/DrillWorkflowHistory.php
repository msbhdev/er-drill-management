<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DrillWorkflowHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'drill_record_id',
        'actor_user_id',
        'actor_account_name',
        'actor_person_name',
        'actor_role_code',
        'actor_rig_code',
        'from_status_id',
        'to_status_id',
        'action',
        'comments',
    ];

    public function drillRecord(): BelongsTo
    {
        return $this->belongsTo(DrillRecord::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }

    public function fromStatus(): BelongsTo
    {
        return $this->belongsTo(DrillStatus::class, 'from_status_id');
    }

    public function toStatus(): BelongsTo
    {
        return $this->belongsTo(DrillStatus::class, 'to_status_id');
    }
}
