<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArchivedDrillRecord extends Model
{
    protected $fillable = [
        'original_drill_id',
        'reference_no',
        'rig_name',
        'status_name',
        'drill_date',
        'snapshot',
        'deleted_by_user_id',
        'deleted_by_name',
        'archived_at',
    ];

    protected function casts(): array
    {
        return [
            'snapshot' => 'array',
            'drill_date' => 'date',
            'archived_at' => 'datetime',
        ];
    }
}
