<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Dsha extends Model
{
    protected $table = 'dshas';

    protected $fillable = [
        'code',
        'name',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function drillRecords(): BelongsToMany
    {
        return $this->belongsToMany(DrillRecord::class, 'drill_record_dsha');
    }

    public function label(): string
    {
        return "{$this->code} — {$this->name}";
    }
}
