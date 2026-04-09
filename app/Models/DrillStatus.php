<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DrillStatus extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'sort_order',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function drillRecords(): HasMany
    {
        return $this->hasMany(DrillRecord::class, 'status_id');
    }
}
