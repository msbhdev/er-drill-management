<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DrillEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'drill_record_id',
        'event_time',
        'event_description',
        'created_by_user_id',
    ];

    public function drillRecord(): BelongsTo
    {
        return $this->belongsTo(DrillRecord::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }
}
