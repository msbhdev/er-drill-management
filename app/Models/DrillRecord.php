<?php

namespace App\Models;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DrillRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference_no',
        'rig_id',
        'drill_type_id',
        'event_type_id',
        'status_id',
        'drill_date',
        'drill_time',
        'on_duty_crews',
        'event_location',
        'scenario',
        'applicable_dsha',
        'performance_standard',
        'performance_standards_met',
        'objectives',
        'debrief_attendees',
        'positive_observations',
        'improvement_opportunities',
        'other_comments',
        'created_by_user_id',
        'sto_user_id',
        'be_user_id',
        'oim_user_id',
        'sto_name',
        'be_name',
        'oim_name',
        'submitted_at',
        'verified_at',
        'approved_at',
        'closed_at',
    ];

    protected function casts(): array
    {
        return [
            'drill_date' => 'date',
            'drill_time' => 'datetime:H:i',
            'submitted_at' => 'datetime',
            'verified_at' => 'datetime',
            'approved_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->isManagement() || $user->isAdministrator()) {
            return $query;
        }

        return $query->where('rig_id', $user->rig_id);
    }

    public function rig(): BelongsTo
    {
        return $this->belongsTo(Rig::class);
    }

    public function drillType(): BelongsTo
    {
        return $this->belongsTo(DrillType::class);
    }

    public function eventType(): BelongsTo
    {
        return $this->belongsTo(EventType::class);
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(DrillStatus::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function stoUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sto_user_id');
    }

    public function beUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'be_user_id');
    }

    public function oimUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'oim_user_id');
    }

    public function events(): HasMany
    {
        return $this->hasMany(DrillEvent::class)->orderBy('id');
    }

    public function actions(): HasMany
    {
        return $this->hasMany(DrillAction::class)->orderBy('id');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(DrillAttachment::class)->latest();
    }

    public function workflowHistory(): HasMany
    {
        return $this->hasMany(DrillWorkflowHistory::class)->latest();
    }

    public function isEditableBy(User $user): bool
    {
        if ($user->isAdministrator()) {
            return true;
        }

        return $user->role === UserRole::STO->value
            && $user->rig_id === $this->rig_id
            && in_array($this->status?->code, ['draft', 'returned_by_be', 'returned_by_oim'], true);
    }
}
