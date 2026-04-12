<?php

namespace App\Http\Controllers;

use App\Models\SideHustleCase;
use App\Models\UserPost;
use App\Models\CommentLike;
use App\Support\CommentThreadBuilder;
use App\Support\SiteGateMask;
use App\Support\ViewHistoryRecorder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
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
        $canVip = (bool) $user?->canAccessVipExclusiveContent();

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
            || (bool) $request->user()?->canAccessVipExclusiveContent();

        if (! $canRead && $sideHustleCase->visibility === 'vip') {
            $sideHustleCase->increment('view_count');
            $sideHustleCase->refresh();
            ViewHistoryRecorder::record($request->user(), $sideHustleCase);
            $teaserPlain = $sideHustleCase->summary
                ?: Str::limit(strip_tags((string) $sideHustleCase->content), 480);
            $teaserHtml = Str::markdown($teaserPlain);
            $mask = SiteGateMask::forVipExclusive($request->user(), $request->fullUrl());

            return view('cases.show', [
                'case' => $sideHustleCase,
                'canReadFull' => false,
                'teaserHtml' => $teaserHtml,
                'gateMask' => $mask,
                'bodyHtml' => '',
                'stepsHtml' => null,
                'recommendCases' => SideHustleCase::query()
                    ->where('status', 'approved')
                    ->whereKeyNot($sideHustleCase->id)
                    ->where('visibility', 'public')
                    ->orderByDesc('like_count')
                    ->orderByDesc('view_count')
                    ->limit(6)
                    ->get(['id', 'title', 'slug', 'like_count', 'view_count']),
                'comments' => new LengthAwarePaginator(collect(), 0, 20, 1, [
                    'path' => $request->url(),
                    'query' => $request->query(),
                ]),
                'likedCommentIds' => [],
            ]);
        }

        abort_unless($canRead, 403);

        $sideHustleCase->increment('view_count');
        $sideHustleCase->refresh();
        ViewHistoryRecorder::record($request->user(), $sideHustleCase);

        $bodyHtml = Str::markdown((string) $sideHustleCase->content);
        $stepsHtml = $sideHustleCase->steps ? Str::markdown((string) $sideHustleCase->steps) : null;
        $recommendCases = SideHustleCase::query()
            ->where('status', 'approved')
            ->whereKeyNot($sideHustleCase->id)
            ->where('visibility', 'public')
            ->orderByDesc('like_count')
            ->orderByDesc('view_count')
            ->limit(6)
            ->get(['id', 'title', 'slug', 'like_count', 'view_count']);

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
            'canReadFull' => true,
            'bodyHtml' => $bodyHtml,
            'stepsHtml' => $stepsHtml,
            'recommendCases' => $recommendCases,
            'comments' => $comments,
            'likedCommentIds' => $likedCommentIds,
        ]);
    }
}
