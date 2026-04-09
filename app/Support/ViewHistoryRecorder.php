<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ViewHistoryRecorder
{
    public static function record(?\App\Models\User $user, Model $viewable): void
    {
        if (! $user || $user->privacy_mode) {
            return;
        }

        DB::table('view_histories')->updateOrInsert(
            [
                'user_id' => $user->id,
                'viewable_type' => $viewable->getMorphClass(),
                'viewable_id' => $viewable->getKey(),
            ],
            ['viewed_at' => now()]
        );
    }
}
