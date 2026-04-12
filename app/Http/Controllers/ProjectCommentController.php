<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\CommentLike;
use App\Models\CommentReport;
use App\Models\Project;
use App\Services\CommentReplyNotifier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProjectCommentController extends Controller
{
    public function store(Request $request, Project $project): RedirectResponse|JsonResponse
    {
        $data = $request->validate([
            'content' => ['required', 'string', 'min:1', 'max:20000'],
            'parent_id' => ['nullable', 'integer', 'exists:comments,id'],
            'ajax' => ['sometimes', 'boolean'],
        ]);

        $parentId = null;
        $parentForNotify = null;
        if (! empty($data['parent_id'])) {
            $parent = Comment::query()->findOrFail($data['parent_id']);
            if (
                $parent->commentable_type !== $project->getMorphClass()
                || (int) $parent->commentable_id !== (int) $project->id
            ) {
                if ($this->wantsCommentJson($request)) {
                    return response()->json(['error' => '回复目标无效'], 422);
                }

                return back()->with('error', '回复目标无效')->withInput();
            }
            $parentId = $parent->threadRoot()->id;
            $parentForNotify = $parent;
        }

        $comment = $project->comments()->create([
            'user_id' => $request->user()->id,
            'parent_id' => $parentId,
            'content' => $data['content'],
        ]);
        $comment->load('user');

        if ($parentForNotify !== null) {
            app(CommentReplyNotifier::class)->notify($comment, $parentForNotify);
        } else {
            app(CommentReplyNotifier::class)->notifyNewRootComment($comment);
        }

        if ($this->wantsCommentJson($request)) {
            $comment->setRelation('replies', collect());

            return response()->json([
                'ok' => true,
                'isReply' => $parentId !== null,
                'rootId' => $parentId ?? $comment->id,
                'html' => $parentId !== null
                    ? view('partials.comment-node', [
                        'c' => $comment,
                        'likedIds' => [],
                        'isProject' => true,
                        'commentContext' => 'project',
                        'isReply' => true,
                    ])->render()
                    : view('partials.comment-thread', [
                        'root' => $comment,
                        'likedIds' => [],
                        'isProject' => true,
                        'commentContext' => 'project',
                    ])->render(),
            ]);
        }

        return back()->with('success', '评论已发布');
    }

    public function reply(Request $request, Comment $comment): RedirectResponse|JsonResponse
    {
        $data = $request->validate([
            'content' => ['required', 'string', 'min:1', 'max:20000'],
            'ajax' => ['sometimes', 'boolean'],
        ]);

        $project = $comment->commentable;
        if (! $project instanceof Project) {
            abort(404);
        }

        $root = $comment->threadRoot();
        $reply = $project->comments()->create([
            'user_id' => $request->user()->id,
            'parent_id' => $root->id,
            'content' => $data['content'],
        ]);
        $reply->load('user');

        app(CommentReplyNotifier::class)->notify($reply, $comment);

        if ($this->wantsCommentJson($request)) {
            return response()->json([
                'ok' => true,
                'isReply' => true,
                'rootId' => $root->id,
                'html' => view('partials.comment-node', [
                    'c' => $reply,
                    'likedIds' => [],
                    'isProject' => true,
                    'commentContext' => 'project',
                    'isReply' => true,
                ])->render(),
            ]);
        }

        return back()->with('success', '回复已发布');
    }

    public function like(Request $request, Comment $comment): JsonResponse
    {
        $user = $request->user();
        if (! $user) {
            return response()->json(['error' => '请先登录'], 401);
        }
        if (! $comment->commentable instanceof Project) {
            abort(404);
        }

        $existing = CommentLike::where('user_id', $user->id)->where('comment_id', $comment->id)->first();
        if ($existing) {
            $existing->delete();
            $comment->decrement('like_count');
            $comment->refresh();

            return response()->json(['success' => '已取消点赞', 'liked' => false, 'count' => (int) $comment->like_count]);
        }

        CommentLike::create(['user_id' => $user->id, 'comment_id' => $comment->id]);
        $comment->increment('like_count');
        $comment->refresh();

        return response()->json(['success' => '已点赞', 'liked' => true, 'count' => (int) $comment->like_count]);
    }

    public function report(Request $request, Comment $comment): JsonResponse
    {
        $user = $request->user();
        if (! $user) {
            return response()->json(['error' => '请先登录'], 401);
        }
        if (! $comment->commentable instanceof Project) {
            abort(404);
        }

        $data = $request->validate([
            'reason' => ['required', 'in:spam,abuse,harassment,other'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        $existing = CommentReport::where('user_id', $user->id)->where('comment_id', $comment->id)->first();
        if ($existing) {
            return response()->json(['error' => '你已经举报过该评论'], 422);
        }

        CommentReport::create([
            'user_id' => $user->id,
            'comment_id' => $comment->id,
            'reason' => $data['reason'],
            'description' => $data['description'] ?? null,
        ]);

        return response()->json(['success' => '举报成功，感谢反馈']);
    }

    private function wantsCommentJson(Request $request): bool
    {
        return $request->wantsJson()
            || $request->ajax()
            || $request->boolean('ajax');
    }
}
