<?php

namespace App\Http\Controllers;

use App\Models\CommentLike;
use App\Models\PrivateTrafficSop;
use App\Support\CommentThreadBuilder;
use App\Support\SiteGateMask;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PrivateTrafficSopWebController extends Controller
{
    public function index(Request $request): View
    {
        $query = PrivateTrafficSop::query()->orderByDesc('id');

        $user = $request->user();
        $canVip = (bool) $user?->canAccessVipExclusiveContent();

        $query->where(function ($q) use ($canVip) {
            $q->where('visibility', 'public');
            if ($canVip) {
                $q->orWhere('visibility', 'vip');
            }
        });

        $sops = $query->paginate(12)->appends($request->query());

        return view('sops.index', [
            'sops' => $sops,
        ]);
    }

    public function show(Request $request, PrivateTrafficSop $privateTrafficSop): View
    {
        $canRead = $privateTrafficSop->visibility === 'public'
            || (bool) $request->user()?->canAccessVipExclusiveContent();

        if (! $canRead && $privateTrafficSop->visibility === 'vip') {
            $privateTrafficSop->increment('view_count');
            $privateTrafficSop->refresh();
            $teaserPlain = $privateTrafficSop->summary
                ?: Str::limit(strip_tags((string) $privateTrafficSop->content), 480);
            $teaserHtml = Str::markdown($teaserPlain);

            return view('sops.show', [
                'sop' => $privateTrafficSop,
                'canReadFull' => false,
                'bodyHtml' => '',
                'teaserHtml' => $teaserHtml,
                'gateMask' => SiteGateMask::forVipExclusive($request->user(), $request->fullUrl()),
                'vipGate' => (bool) $privateTrafficSop->vip_gate_engagement,
                'canSeeContact' => false,
                'canComment' => false,
                'comments' => new LengthAwarePaginator(collect(), 0, 20, 1, [
                    'path' => $request->url(),
                    'query' => $request->query(),
                ]),
                'likedCommentIds' => [],
            ]);
        }

        abort_unless($canRead, 403);

        $privateTrafficSop->increment('view_count');
        $privateTrafficSop->refresh();

        $bodyHtml = Str::markdown((string) $privateTrafficSop->content);

        $member = $request->user() && $request->user()->hasMemberMenuPrivileges();
        $vipGate = (bool) $privateTrafficSop->vip_gate_engagement;
        $canSeeContact = ! $vipGate || $member;
        $canComment = ! $vipGate || $member;

        $comments = $privateTrafficSop->comments()
            ->whereNull('parent_id')
            ->where('is_hidden', false)
            ->latest()
            ->with(['user'])
            ->paginate(20)
            ->withQueryString();

        CommentThreadBuilder::attachNestedReplies($comments, $privateTrafficSop);

        $likedCommentIds = [];
        if ($request->user()) {
            $ids = CommentThreadBuilder::collectTreeCommentIds($comments);
            $likedCommentIds = CommentLike::query()
                ->where('user_id', $request->user()->id)
                ->whereIn('comment_id', $ids)
                ->pluck('comment_id')
                ->all();
        }

        return view('sops.show', [
            'sop' => $privateTrafficSop,
            'canReadFull' => true,
            'bodyHtml' => $bodyHtml,
            'canSeeContact' => $canSeeContact,
            'canComment' => $canComment,
            'vipGate' => $vipGate,
            'comments' => $comments,
            'likedCommentIds' => $likedCommentIds,
        ]);
    }
}
