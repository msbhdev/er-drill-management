<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Rig extends Model
{
    use HasFactory;

    public function getConnectionName()
    {
        return config('database.default');
    }

    protected $fillable = [
        'name',
        'code',
        'location',
        'timezone',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function drillRecords(): HasMany
    {
        return $this->hasMany(DrillRecord::class);
    }

    public function timezoneName(): string
    {
        return $this->timezone ?: config('er_drill.default_timezone', 'Asia/Kuala_Lumpur');
    }

    public function formatDateTime(?CarbonInterface $value, string $format = 'd M Y H:i'): ?string
    {
        return $value?->copy()->timezone($this->timezoneName())->format($format);
    }
}
