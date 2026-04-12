<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Comment;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * 小程序文章评论（需 Sanctum 登录）
 */
class WeChatMiniArticleCommentController extends Controller
{
    public function store(Request $request, string $slug): JsonResponse
    {
        $article = Article::query()
            ->where('slug', $slug)
            ->where('is_published', true)
            ->firstOrFail();

        /** @var User $user */
        $user = $request->user();
        $canReadFull = $article->userCanReadFull($user);
        if (! $canReadFull) {
            return response()->json([
                'message' => '本文为会员专享，阅读全文后方可评论',
            ], 403);
        }

        $data = $request->validate([
            'content' => ['required', 'string', 'min:1', 'max:20000'],
            'parent_id' => ['nullable', 'integer', 'exists:comments,id'],
        ]);

        $parentId = null;
        if (! empty($data['parent_id'])) {
            $parent = Comment::query()->findOrFail($data['parent_id']);
            if (
                $parent->commentable_type !== $article->getMorphClass()
                || (int) $parent->commentable_id !== (int) $article->id
            ) {
                return response()->json(['message' => '回复目标无效'], 422);
            }
            $parentId = $parent->threadRoot()->id;
        }

        $comment = $article->comments()->create([
            'user_id' => $request->user()->id,
            'parent_id' => $parentId,
            'content' => $data['content'],
        ]);
        $comment->load('user:id,name,avatar');

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $comment->id,
                'parent_id' => $comment->parent_id,
                'content' => $comment->content,
                'created_at' => $comment->created_at?->toIso8601String(),
                'user' => $comment->user ? [
                    'id' => $comment->user->id,
                    'name' => $comment->user->name,
                    'avatar' => $comment->user->avatar,
                ] : null,
            ],
        ], 201);
    }
}
