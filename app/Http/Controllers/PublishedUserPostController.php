<?php

namespace App\Http\Controllers;

use App\Models\CommentLike;
use App\Models\UserAction;
use App\Models\UserPost;
use App\Support\CommentThreadBuilder;
use App\Support\ViewHistoryRecorder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PublishedUserPostController extends Controller
{
    private const TYPES = ['case', 'tool', 'experience', 'resource', 'question'];

    /** @var array<string, string> */
    private const TYPE_LABELS = [
        'case' => '案例',
        'tool' => '工具',
        'experience' => '经验心得',
        'resource' => '学习资源',
        'question' => '提问讨论',
    ];

    public function index(Request $request): View
    {
        $type = $request->query('type');
        if ($type !== null && $type !== '' && ! in_array($type, self::TYPES, true)) {
            $type = null;
        }
        if ($type === '') {
            $type = null;
        }

        $posts = UserPost::query()
            ->publicFeed()
            ->with('author')
            ->when($type, fn (Builder $q) => $q->where('type', $type))
            ->orderByDesc('audited_at')
            ->orderByDesc('id')
            ->paginate(12)
            ->appends($request->query());

        return view('user-posts.public-index', [
            'posts' => $posts,
            'currentType' => $type,
            'typeLabels' => self::TYPE_LABELS,
        ]);
    }

    public function show(Request $request, UserPost $userPost): View
    {
        abort_unless($userPost->status === 'approved', 404);

        if ($userPost->visibility === 'private') {
            abort_unless(
                $request->user() && (int) $request->user()->id === (int) $userPost->user_id,
                404
            );
        }

        $canReadFull = $userPost->visibility !== 'vip'
            || (bool) $request->user()?->canAccessVipExclusiveContent();

        $userPost->load('author');
        $userPost->increment('view_count');
        ViewHistoryRecorder::record($request->user(), $userPost);

        $bodyHtml = $canReadFull ? $userPost->content : null;
        $teaser = ! $canReadFull ? Str::limit(strip_tags($userPost->content), 360) : null;

        $userLiked = false;
        $userFavorited = false;
        if ($request->user()) {
            $types = UserAction::query()
                ->where('user_id', $request->user()->id)
                ->where('actionable_type', $userPost->getMorphClass())
                ->where('actionable_id', $userPost->id)
                ->pluck('type');
            $userLiked = $types->contains('like');
            $userFavorited = $types->contains('favorite');
        }

        $comments = $userPost->comments()
            ->whereNull('parent_id')
            ->where('is_hidden', false)
            ->latest()
            ->with(['user'])
            ->paginate(20)
            ->withQueryString();

        CommentThreadBuilder::attachNestedReplies($comments, $userPost);

        $likedCommentIds = [];
        if ($request->user()) {
            $ids = CommentThreadBuilder::collectTreeCommentIds($comments);
            $likedCommentIds = CommentLike::query()
                ->where('user_id', $request->user()->id)
                ->whereIn('comment_id', $ids)
                ->pluck('comment_id')
                ->all();
        }

        return view('user-posts.public-show', [
            'post' => $userPost,
            'canReadFull' => $canReadFull,
            'bodyHtml' => $bodyHtml,
            'teaser' => $teaser,
            'typeLabel' => self::TYPE_LABELS[$userPost->type] ?? $userPost->type,
            'userLiked' => $userLiked,
            'userFavorited' => $userFavorited,
            'comments' => $comments,
            'likedCommentIds' => $likedCommentIds,
        ]);
    }
}
