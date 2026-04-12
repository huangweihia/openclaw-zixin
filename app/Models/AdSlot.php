<?php

namespace App\Models;

use App\Support\AdminUniqueCode;
use Illuminate\Database\Eloquent\Model;

class AdSlot extends Model
{
    protected $fillable = [
        'name',
        'code',
        'position',
        'type',
        'width',
        'height',
        'is_active',
        'sort',
        'default_title',
        'default_image_url',
        'default_link_url',
        'default_content',
        'show_default_when_empty',
        'audience',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'show_default_when_empty' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (AdSlot $slot): void {
            if (! filled($slot->code)) {
                $slot->code = AdminUniqueCode::code(
                    filled($slot->name) ? (string) $slot->name : 'slot',
                    self::class,
                    'code'
                );
            }
        });
    }

}
