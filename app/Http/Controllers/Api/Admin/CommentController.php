<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Comment;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $hidden = $request->query('hidden');

        $paginator = Comment::query()
            ->with([
                'user:id,name,email',
                'commentable',
            ])
            ->when($hidden === '1' || $hidden === '0', function ($query) use ($hidden) {
                $query->where('is_hidden', $hidden === '1');
            })
            ->orderByDesc('id')
            ->paginate(30)
            ->withQueryString();

        return response()->json(
            $paginator->through(function (Comment $c) {
                $target = null;
                if ($c->commentable) {
                    $target = match (true) {
                        $c->commentable instanceof Article => $c->commentable->title,
                        $c->commentable instanceof Project => $c->commentable->name,
                        default => '#'.$c->commentable_id,
                    };
                }

                return [
                    'id' => $c->id,
                    'content' => $c->content,
                    'is_hidden' => $c->is_hidden,
                    'like_count' => $c->like_count,
                    'parent_id' => $c->parent_id,
                    'created_at' => $c->created_at?->toIso8601String(),
                    'user' => $c->user,
                    'commentable_type' => $c->commentable_type,
                    'commentable_id' => $c->commentable_id,
                    'target_title' => $target,
                ];
            })
        );
    }

    public function update(Request $request, int $commentId): JsonResponse
    {
        $comment = Comment::query()->findOrFail($commentId);
        $data = $request->validate([
            'is_hidden' => ['required', 'boolean'],
        ]);
        $comment->forceFill($data)->save();

        return response()->json([
            'message' => '评论已更新',
            'comment' => [
                'id' => $comment->id,
                'is_hidden' => $comment->is_hidden,
            ],
        ]);
    }

    public function destroy(int $commentId): JsonResponse
    {
        $comment = Comment::query()->findOrFail($commentId);
        $comment->delete();

        return response()->json(['message' => '评论已删除']);
    }
}
