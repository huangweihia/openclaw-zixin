<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdminUser extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'display_name',
        'is_active',
        'is_super',
        'last_login_at',
        'last_login_ip',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_super' => 'boolean',
        'last_login_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

