<?php

namespace App\Http\Controllers;

use App\Models\PointPackage;
use App\Support\PointsRuleConfig;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class DashboardPointsController extends Controller
{
    public function index(): View
    {
        $packages = Schema::hasTable('point_packages')
            ? PointPackage::query()
                ->where('is_active', true)
                ->where(function ($q) {
                    $q->whereNull('active_from')->orWhere('active_from', '<=', now());
                })
                ->where(function ($q) {
                    $q->whereNull('active_until')->orWhere('active_until', '>=', now());
                })
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get()
            : collect();

        return view('dashboard-points', [
            'packages' => $packages,
            'boostCost' => \App\Services\UserPostBoostService::pointsCost(),
            'pointRules' => [
                'daily_login' => PointsRuleConfig::loginDaily(),
                'post_approved' => PointsRuleConfig::postApproved(),
                'post_liked_author' => PointsRuleConfig::postLikedAuthor(),
                'post_favorited_author' => PointsRuleConfig::postFavoritedAuthor(),
                'post_commented_author' => PointsRuleConfig::postCommentedAuthor(),
                'boost_cost' => PointsRuleConfig::boostCost(),
                'boost_window_hours' => PointsRuleConfig::boostWindowHours(),
                'boost_random_notify_users' => PointsRuleConfig::boostRandomNotifyUsers(),
            ],
        ]);
    }
}
