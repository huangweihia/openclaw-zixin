<?php

namespace App\Observers;

use App\Models\AdSlot;
use Illuminate\Support\Facades\DB;

/**
 * 保证全局仅有一个 is_active=true 的广告位（与业务及 Vue 版「单启用」一致）。
 */
class AdSlotObserver
{
    public function saved(AdSlot $adSlot): void
    {
        if (! $adSlot->is_active) {
            return;
        }

        DB::table('ad_slots')
            ->where('id', '!=', $adSlot->id)
            ->update([
                'is_active' => false,
                'updated_at' => now(),
            ]);
    }
}
