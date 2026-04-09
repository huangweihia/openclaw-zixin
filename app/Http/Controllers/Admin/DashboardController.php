<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\UserPost;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $pendingPostsCount = UserPost::query()->where('status', 'pending')->count();
        $pendingOrdersCount = Order::query()->where('status', 'pending')->count();
        $recentPendingPosts = UserPost::query()
            ->where('status', 'pending')
            ->latest()
            ->with('author')
            ->limit(5)
            ->get();

        return view('admin.dashboard', [
            'pendingPostsCount' => $pendingPostsCount,
            'pendingOrdersCount' => $pendingOrdersCount,
            'recentPendingPosts' => $recentPendingPosts,
        ]);
    }
}
