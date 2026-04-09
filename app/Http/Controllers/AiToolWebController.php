<?php

namespace App\Http\Controllers;

use App\Models\AiToolMonetization;
use App\Models\UserPost;
use App\Support\ViewHistoryRecorder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AiToolWebController extends Controller
{
    public function index(Request $request): View
    {
        $query = AiToolMonetization::query()->orderByDesc('id');

        $user = $request->user();
        $canVip = $user && in_array($user->role, ['vip', 'svip', 'admin'], true);

        $query->where(function ($q) use ($canVip) {
            $q->where('visibility', 'public');
            if ($canVip) {
                $q->orWhere('visibility', 'vip');
            }
        });

        $cat = $request->string('category')->trim()->toString();
        if ($cat !== '' && in_array($cat, ['image', 'text', 'video', 'audio', 'code'], true)) {
            $query->where('category', $cat);
        }

        $tools = $query->paginate(12)->appends($request->query());

        $userToolPosts = UserPost::query()
            ->publicFeed()
            ->where('type', 'tool')
            ->with('author')
            ->orderByDesc('audited_at')
            ->orderByDesc('id')
            ->limit(12)
            ->get();

        return view('tools.index', [
            'tools' => $tools,
            'currentCategory' => $cat,
            'userToolPosts' => $userToolPosts,
        ]);
    }

    public function show(Request $request, AiToolMonetization $aiToolMonetization): View
    {
        $canRead = $aiToolMonetization->visibility === 'public'
            || ($request->user() && in_array($request->user()->role, ['vip', 'svip', 'admin'], true));

        abort_unless($canRead, 403);

        $aiToolMonetization->increment('view_count');
        $aiToolMonetization->refresh();
        ViewHistoryRecorder::record($request->user(), $aiToolMonetization);

        return view('tools.show', [
            'tool' => $aiToolMonetization,
        ]);
    }
}
