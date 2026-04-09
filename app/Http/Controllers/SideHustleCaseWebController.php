<?php

namespace App\Http\Controllers;

use App\Models\SideHustleCase;
use App\Models\UserPost;
use App\Models\CommentLike;
use App\Support\CommentThreadBuilder;
use App\Support\ViewHistoryRecorder;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SideHustleCaseWebController extends Controller
{
    public function index(Request $request): View
    {
        $query = SideHustleCase::query()
            ->where('status', 'approved')
            ->orderByDesc('audited_at')
            ->orderByDesc('id');

        $user = $request->user();
        $canVip = $user && in_array($user->role, ['vip', 'svip', 'admin'], true);

        $query->where(function ($q) use ($canVip) {
            $q->where('visibility', 'public');
            if ($canVip) {
                $q->orWhere('visibility', 'vip');
            }
        });

        $cat = $request->string('category')->trim()->toString();
        if ($cat !== '' && in_array($cat, ['online', 'offline', 'hybrid'], true)) {
            $query->where('category', $cat);
        }

        $cases = $query->paginate(12)->appends($request->query());

        $userCasePosts = UserPost::query()
            ->publicFeed()
            ->where('type', 'case')
            ->with('author')
            ->orderByDesc('audited_at')
            ->orderByDesc('id')
            ->limit(12)
            ->get();

        return view('cases.index', [
            'cases' => $cases,
            'currentCategory' => $cat,
            'userCasePosts' => $userCasePosts,
        ]);
    }

    public function show(Request $request, SideHustleCase $sideHustleCase): View
    {
        abort_unless($sideHustleCase->status === 'approved', 404);

        $canRead = $sideHustleCase->visibility === 'public'
            || ($request->user() && in_array($request->user()->role, ['vip', 'svip', 'admin'], true));

        abort_unless($canRead, 403);

        $sideHustleCase->increment('view_count');
        $sideHustleCase->refresh();
        ViewHistoryRecorder::record($request->user(), $sideHustleCase);

        $bodyHtml = Str::markdown((string) $sideHustleCase->content);
        $stepsHtml = $sideHustleCase->steps ? Str::markdown((string) $sideHustleCase->steps) : null;

        $comments = $sideHustleCase->comments()
            ->whereNull('parent_id')
            ->where('is_hidden', false)
            ->latest()
            ->with(['user'])
            ->paginate(20)
            ->withQueryString();
        CommentThreadBuilder::attachNestedReplies($comments, $sideHustleCase);

        $likedCommentIds = [];
        if ($request->user()) {
            $ids = CommentThreadBuilder::collectTreeCommentIds($comments);
            $likedCommentIds = CommentLike::query()
                ->where('user_id', $request->user()->id)
                ->whereIn('comment_id', $ids)
                ->pluck('comment_id')
                ->all();
        }

        return view('cases.show', [
            'case' => $sideHustleCase,
            'bodyHtml' => $bodyHtml,
            'stepsHtml' => $stepsHtml,
            'comments' => $comments,
            'likedCommentIds' => $likedCommentIds,
        ]);
    }
}
