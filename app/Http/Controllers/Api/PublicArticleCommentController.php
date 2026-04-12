<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Comment;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * 文章评论列表；VIP 专享文与正文一致：无会员权限时不返回评论（防抓取与端上对齐）
 */
class PublicArticleCommentController extends Controller
{
    public function index(Request $request, string $slug): JsonResponse
    {
        $article = Article::query()
            ->where('slug', $slug)
            ->where('is_published', true)
            ->firstOrFail();

        /** @var User|null $user */
        $user = auth('sanctum')->user();
        if (! $article->userCanReadFull($user instanceof User ? $user : null)) {
            return response()->json([
                'success' => true,
                'data' => [],
            ]);
        }

        $rows = Comment::query()
            ->where('commentable_type', $article->getMorphClass())
            ->where('commentable_id', $article->id)
            ->where('is_hidden', false)
            ->with(['user:id,name,avatar'])
            ->orderBy('created_at')
            ->limit(200)
            ->get();

        $data = $rows->map(function (Comment $c) {
            return [
                'id' => $c->id,
                'parent_id' => $c->parent_id,
                'content' => $c->content,
                'like_count' => (int) $c->like_count,
                'created_at' => $c->created_at?->toIso8601String(),
                'user' => $c->user ? [
                    'id' => $c->user->id,
                    'name' => $c->user->name,
                    'avatar' => $c->user->avatar,
                ] : null,
            ];
        })->values();

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }
}
