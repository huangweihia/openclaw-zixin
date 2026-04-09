<?php

namespace App\Http\Controllers;

use App\Models\UserAction;
use App\Models\UserPost;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserPostEngagementController extends Controller
{
    public function toggleLike(Request $request, UserPost $userPost): RedirectResponse|JsonResponse
    {
        abort_unless($userPost->status === 'approved', 404);
        $this->abortIfNotPublicFeed($userPost);

        $user = $request->user();

        return DB::transaction(function () use ($user, $userPost, $request) {
            $row = UserAction::query()
                ->where('user_id', $user->id)
                ->where('actionable_type', $userPost->getMorphClass())
                ->where('actionable_id', $userPost->id)
                ->where('type', 'like')
                ->lockForUpdate()
                ->first();

            if ($row) {
                $row->delete();
                UserPost::query()->whereKey($userPost->id)->decrement('like_count');
                $message = '已取消点赞';
            } else {
                UserAction::query()->create([
                    'user_id' => $user->id,
                    'actionable_type' => $userPost->getMorphClass(),
                    'actionable_id' => $userPost->id,
                    'type' => 'like',
                ]);
                UserPost::query()->whereKey($userPost->id)->increment('like_count');
                $message = '点赞成功';
            }

            $liked = ! $row;
            $count = (int) (UserPost::query()->whereKey($userPost->id)->value('like_count') ?? 0);
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['ok' => true, 'liked' => $liked, 'count' => $count, 'message' => $message]);
            }

            return back()->with('success', $message);
        });
    }

    public function toggleFavorite(Request $request, UserPost $userPost): RedirectResponse|JsonResponse
    {
        abort_unless($userPost->status === 'approved', 404);
        $this->abortIfNotPublicFeed($userPost);

        $user = $request->user();

        return DB::transaction(function () use ($user, $userPost, $request) {
            $row = UserAction::query()
                ->where('user_id', $user->id)
                ->where('actionable_type', $userPost->getMorphClass())
                ->where('actionable_id', $userPost->id)
                ->where('type', 'favorite')
                ->lockForUpdate()
                ->first();

            if ($row) {
                $row->delete();
                UserPost::query()->whereKey($userPost->id)->decrement('favorite_count');
                $message = '已取消收藏';
            } else {
                UserAction::query()->create([
                    'user_id' => $user->id,
                    'actionable_type' => $userPost->getMorphClass(),
                    'actionable_id' => $userPost->id,
                    'type' => 'favorite',
                ]);
                UserPost::query()->whereKey($userPost->id)->increment('favorite_count');
                $message = '已加入收藏';
            }

            $favorited = ! $row;
            $count = (int) (UserPost::query()->whereKey($userPost->id)->value('favorite_count') ?? 0);
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['ok' => true, 'favorited' => $favorited, 'count' => $count, 'message' => $message]);
            }

            return back()->with('success', $message);
        });
    }

    private function abortIfNotPublicFeed(UserPost $userPost): void
    {
        abort_unless(
            $userPost->status === 'approved' && in_array($userPost->visibility, ['public', 'vip'], true),
            404
        );
    }
}
