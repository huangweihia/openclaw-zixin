<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Comment;
use Illuminate\Http\JsonResponse;

/**
 * 文章评论列表（公开，无需登录）
 */
class PublicArticleCommentController extends Controller
{
    public function index(string $slug): JsonResponse
    {
        $article = Article::query()
            ->where('slug', $slug)
            ->where('is_published', true)
            ->firstOrFail();

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
