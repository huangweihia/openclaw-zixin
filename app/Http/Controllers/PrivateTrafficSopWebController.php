<?php

namespace App\Http\Controllers;

use App\Models\CommentLike;
use App\Models\PrivateTrafficSop;
use App\Support\CommentThreadBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PrivateTrafficSopWebController extends Controller
{
    public function index(Request $request): View
    {
        $query = PrivateTrafficSop::query()->orderByDesc('id');

        $user = $request->user();
        $canVip = $user && in_array($user->role, ['vip', 'svip', 'admin'], true);

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
            || ($request->user() && in_array($request->user()->role, ['vip', 'svip', 'admin'], true));

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
            'bodyHtml' => $bodyHtml,
            'canSeeContact' => $canSeeContact,
            'canComment' => $canComment,
            'vipGate' => $vipGate,
            'comments' => $comments,
            'likedCommentIds' => $likedCommentIds,
        ]);
    }
}
