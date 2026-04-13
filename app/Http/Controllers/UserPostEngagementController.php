<?php

namespace App\Http\Controllers;

use App\Models\UserAction;
use App\Models\UserPost;
use App\Services\ContentEngagementNotifier;
use App\Services\PointsService;
use App\Services\UserPostHeatService;
use App\Support\PointsRuleConfig;
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
            UserPost::query()->whereKey($userPost->id)->lockForUpdate()->first();

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
                app(ContentEngagementNotifier::class)->notifyLiked($user, $userPost);
                UserPostHeatService::increment($userPost, (int) config('heat.like', 5), 'like');
                $author = $userPost->author;
                if ($author && (int) $author->id !== (int) $user->id) {
                    $p = PointsRuleConfig::postLikedAuthor();
                    if ($p > 0) {
                        PointsService::earn($author, $p, 'post_liked', '投稿被点赞', $userPost->getMorphClass(), (int) $userPost->id);
                    }
                }
            }

            $liked = ! $row;
            $count = (int) (UserPost::query()->whereKey($userPost->id)->value('like_count') ?? 0);
            if ($request->expectsJson()) {
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
            UserPost::query()->whereKey($userPost->id)->lockForUpdate()->first();

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
                app(ContentEngagementNotifier::class)->notifyFavorited($user, $userPost);
                UserPostHeatService::increment($userPost, (int) config('heat.favorite', 8), 'favorite');
                $author = $userPost->author;
                if ($author && (int) $author->id !== (int) $user->id) {
                    $p = PointsRuleConfig::postFavoritedAuthor();
                    if ($p > 0) {
                        PointsService::earn($author, $p, 'post_favorited', '投稿被收藏', $userPost->getMorphClass(), (int) $userPost->id);
                    }
                }
            }

            $favorited = ! $row;
            $count = (int) (UserPost::query()->whereKey($userPost->id)->value('favorite_count') ?? 0);
            if ($request->expectsJson()) {
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
