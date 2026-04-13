<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class DashboardFollowController extends Controller
{
    public function followers(Request $request): View
    {
        $users = Schema::hasTable('user_follows')
            ? $request->user()
                ->followers()
                ->orderByPivot('created_at', 'desc')
                ->paginate(30)
                ->withQueryString()
            : new LengthAwarePaginator([], 0, 30, 1, ['path' => $request->url(), 'pageName' => 'page']);

        return view('dashboard-follow-list', [
            'mode' => 'followers',
            'title' => '我的粉丝',
            'emptyText' => '还没有人关注你。',
            'users' => $users,
        ]);
    }

    public function following(Request $request): View
    {
        $users = Schema::hasTable('user_follows')
            ? $request->user()
                ->following()
                ->orderByPivot('created_at', 'desc')
                ->paginate(30)
                ->withQueryString()
            : new LengthAwarePaginator([], 0, 30, 1, ['path' => $request->url(), 'pageName' => 'page']);

        return view('dashboard-follow-list', [
            'mode' => 'following',
            'title' => '我的关注',
            'emptyText' => '你还没有关注任何人。',
            'users' => $users,
        ]);
    }
}
