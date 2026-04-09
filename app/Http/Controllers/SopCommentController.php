<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\CommentLike;
use App\Models\CommentReport;
use App\Models\PrivateTrafficSop;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SopCommentController extends Controller
{
    private function assertCanComment(Request $request, PrivateTrafficSop $sop): ?JsonResponse
    {
        if (! $sop->vip_gate_engagement) {
            return null;
        }
        $u = $request->user();
        if (! $u || ! $u->hasMemberMenuPrivileges()) {
            if ($request->wantsJson() || $request->ajax() || $request->boolean('ajax')) {
                return response()->json(['error' => '本 SOP 已开启会员互动，请开通 VIP 后评论'], 403);
            }
            abort(403, '本 SOP 已开启会员互动，请开通 VIP 后评论');
        }

        return null;
    }

    public function store(Request $request, PrivateTrafficSop $privateTrafficSop): RedirectResponse|JsonResponse
    {
        $gate = $this->assertCanComment($request, $privateTrafficSop);
        if ($gate) {
            return $gate;
        }

        $data = $request->validate([
            'content' => ['required', 'string', 'min:1', 'max:20000'],
            'parent_id' => ['nullable', 'integer', 'exists:comments,id'],
            'ajax' => ['sometimes', 'boolean'],
        ]);

        $parentId = null;
        if (! empty($data['parent_id'])) {
            $parent = Comment::query()->findOrFail($data['parent_id']);
            if (
                $parent->commentable_type !== $privateTrafficSop->getMorphClass()
                || (int) $parent->commentable_id !== (int) $privateTrafficSop->id
            ) {
                if ($this->wantsCommentJson($request)) {
                    return response()->json(['error' => '回复目标无效'], 422);
                }

                return back()->with('error', '回复目标无效')->withInput();
            }
            $parentId = $parent->threadRoot()->id;
        }

        $comment = $privateTrafficSop->comments()->create([
            'user_id' => $request->user()->id,
            'parent_id' => $parentId,
            'content' => $data['content'],
        ]);
        $comment->load('user');

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
                        'commentContext' => 'sop',
                        'isReply' => true,
                    ])->render()
                    : view('partials.comment-thread', [
                        'root' => $comment,
                        'likedIds' => [],
                        'commentContext' => 'sop',
                    ])->render(),
            ]);
        }

        return back()->with('success', '评论已发布');
    }

    public function reply(Request $request, Comment $comment): RedirectResponse|JsonResponse
    {
        $sop = $comment->commentable;
        if (! $sop instanceof PrivateTrafficSop) {
            abort(404);
        }
        $gate = $this->assertCanComment($request, $sop);
        if ($gate) {
            return $gate;
        }

        $data = $request->validate([
            'content' => ['required', 'string', 'min:1', 'max:20000'],
            'ajax' => ['sometimes', 'boolean'],
        ]);

        $root = $comment->threadRoot();
        $reply = $sop->comments()->create([
            'user_id' => $request->user()->id,
            'parent_id' => $root->id,
            'content' => $data['content'],
        ]);
        $reply->load('user');

        if ($this->wantsCommentJson($request)) {
            return response()->json([
                'ok' => true,
                'isReply' => true,
                'rootId' => $root->id,
                'html' => view('partials.comment-node', [
                    'c' => $reply,
                    'likedIds' => [],
                    'commentContext' => 'sop',
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
        if (! $comment->commentable instanceof PrivateTrafficSop) {
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
        if (! $comment->commentable instanceof PrivateTrafficSop) {
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
