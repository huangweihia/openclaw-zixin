<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\UserAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ArticleEngagementController extends Controller
{
    public function toggleLike(Request $request, Article $article): RedirectResponse|JsonResponse
    {
        if (! $article->is_published) {
            abort(404);
        }

        $user = $request->user();

        return DB::transaction(function () use ($user, $article, $request) {
            $row = UserAction::query()
                ->where('user_id', $user->id)
                ->where('actionable_type', $article->getMorphClass())
                ->where('actionable_id', $article->id)
                ->where('type', 'like')
                ->lockForUpdate()
                ->first();

            if ($row) {
                $row->delete();
                Article::query()->whereKey($article->id)->decrement('like_count');
                $message = '已取消点赞';
            } else {
                UserAction::query()->create([
                    'user_id' => $user->id,
                    'actionable_type' => $article->getMorphClass(),
                    'actionable_id' => $article->id,
                    'type' => 'like',
                ]);
                Article::query()->whereKey($article->id)->increment('like_count');
                $message = '点赞成功';
            }

            $liked = ! $row;
            $count = (int) (Article::query()->whereKey($article->id)->value('like_count') ?? 0);
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['ok' => true, 'liked' => $liked, 'count' => $count, 'message' => $message]);
            }

            return back()->with('success', $message);
        });
    }

    public function toggleFavorite(Request $request, Article $article): RedirectResponse|JsonResponse
    {
        if (! $article->is_published) {
            abort(404);
        }

        $user = $request->user();

        return DB::transaction(function () use ($user, $article, $request) {
            $row = UserAction::query()
                ->where('user_id', $user->id)
                ->where('actionable_type', $article->getMorphClass())
                ->where('actionable_id', $article->id)
                ->where('type', 'favorite')
                ->lockForUpdate()
                ->first();

            if ($row) {
                $row->delete();
                $message = '已取消收藏';
            } else {
                UserAction::query()->create([
                    'user_id' => $user->id,
                    'actionable_type' => $article->getMorphClass(),
                    'actionable_id' => $article->id,
                    'type' => 'favorite',
                ]);
                $message = '已加入收藏';
            }

            $favorited = ! $row;
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['ok' => true, 'favorited' => $favorited, 'message' => $message]);
            }

            return back()->with('success', $message);
        });
    }
}
