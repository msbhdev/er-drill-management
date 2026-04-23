<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccountAppAccess extends Model
{
    protected $connection = 'auth';

    protected $table = 'account_app_access';

    protected $fillable = [
        'account_id',
        'app_code',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(User::class, 'account_id');
    }
}
