<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'order_no',
        'product_type',
        'product_id',
        'amount',
        'status',
        'payment_id',
        'payment_method',
        'paid_at',
        'refund_requested_at',
        'remark',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'refund_requested_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function planKeyFromProduct(): ?string
    {
        return match ((int) $this->product_id) {
            1 => 'vip',
            2 => 'svip',
            default => null,
        };
    }
}
