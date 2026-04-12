<?php

namespace App\Http\Controllers;

use App\Models\SideHustleCase;
use App\Models\UserAction;
use App\Services\ContentEngagementNotifier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SideHustleCaseEngagementController extends Controller
{
    public function toggleLike(Request $request, SideHustleCase $sideHustleCase): RedirectResponse|JsonResponse
    {
        abort_unless($sideHustleCase->status === 'approved', 404);

        $user = $request->user();

        return DB::transaction(function () use ($user, $sideHustleCase, $request) {
            $row = UserAction::query()
                ->where('user_id', $user->id)
                ->where('actionable_type', $sideHustleCase->getMorphClass())
                ->where('actionable_id', $sideHustleCase->id)
                ->where('type', 'like')
                ->lockForUpdate()
                ->first();

            if ($row) {
                $row->delete();
                SideHustleCase::query()
                    ->whereKey($sideHustleCase->id)
                    ->where('like_count', '>', 0)
                    ->decrement('like_count');
                $message = '已取消点赞';
            } else {
                UserAction::query()->create([
                    'user_id' => $user->id,
                    'actionable_type' => $sideHustleCase->getMorphClass(),
                    'actionable_id' => $sideHustleCase->id,
                    'type' => 'like',
                ]);
                SideHustleCase::query()->whereKey($sideHustleCase->id)->increment('like_count');
                $message = '点赞成功';
                app(ContentEngagementNotifier::class)->notifyLiked($user, $sideHustleCase);
            }

            $liked = ! $row;
            $count = (int) (SideHustleCase::query()->whereKey($sideHustleCase->id)->value('like_count') ?? 0);
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['ok' => true, 'liked' => $liked, 'count' => $count, 'message' => $message]);
            }

            return back()->with('success', $message);
        });
    }

    public function toggleFavorite(Request $request, SideHustleCase $sideHustleCase): RedirectResponse|JsonResponse
    {
        abort_unless($sideHustleCase->status === 'approved', 404);

        $user = $request->user();

        return DB::transaction(function () use ($user, $sideHustleCase, $request) {
            $row = UserAction::query()
                ->where('user_id', $user->id)
                ->where('actionable_type', $sideHustleCase->getMorphClass())
                ->where('actionable_id', $sideHustleCase->id)
                ->where('type', 'favorite')
                ->lockForUpdate()
                ->first();

            if ($row) {
                $row->delete();
                SideHustleCase::query()
                    ->whereKey($sideHustleCase->id)
                    ->where('favorite_count', '>', 0)
                    ->decrement('favorite_count');
                $message = '已取消收藏';
            } else {
                UserAction::query()->create([
                    'user_id' => $user->id,
                    'actionable_type' => $sideHustleCase->getMorphClass(),
                    'actionable_id' => $sideHustleCase->id,
                    'type' => 'favorite',
                ]);
                SideHustleCase::query()->whereKey($sideHustleCase->id)->increment('favorite_count');
                $message = '已加入收藏';
                app(ContentEngagementNotifier::class)->notifyFavorited($user, $sideHustleCase);
            }

            $favorited = ! $row;
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['ok' => true, 'favorited' => $favorited, 'message' => $message]);
            }

            return back()->with('success', $message);
        });
    }
}
