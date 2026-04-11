<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Comment;
use App\Models\EmailSubscription;
use App\Models\Project;
use App\Models\User;
use App\Models\UserAction;
use App\Models\UserPost;
use App\Models\ViewHistory;
use App\Support\VipActivityFeed;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        /** @var User $u */
        $u = auth()->user();

        VipActivityFeed::seedDemoIfNeeded(50);

        $postsCount = UserPost::query()
            ->where('user_id', $u->id)
            ->where('status', 'approved')
            ->count()
            + Article::query()
                ->where('author_id', $u->id)
                ->where('is_published', true)
                ->count();

        $articlesViewSum = Article::query()->where('author_id', $u->id)->where('is_published', true)->sum('view_count');
        $postsViewSum = UserPost::query()->where('user_id', $u->id)->where('status', 'approved')->sum('view_count');
        $viewsCount = (int) $articlesViewSum + (int) $postsViewSum;

        $favoritesCount = UserAction::query()
            ->where('user_id', $u->id)
            ->where('type', 'favorite')
            ->count();

        $commentsCount = Comment::query()
            ->where('user_id', $u->id)
            ->where('is_hidden', false)
            ->count();

        $footprintCount = ViewHistory::query()->where('user_id', $u->id)->count();

        $handle = '@'.Str::before($u->email, '@');

        $vipDays = null;
        $vipSecondsLeft = null;
        $vipIsUrgent = false;
        $vipExpiresAt = $u->subscription_ends_at;
        if ($u->isVip() && $u->subscription_ends_at && $u->subscription_ends_at->isFuture()) {
            $vipDays = (int) now()->startOfDay()->diffInDays($u->subscription_ends_at->copy()->startOfDay());
            $vipSecondsLeft = now()->diffInSeconds($u->subscription_ends_at, false);
            $vipIsUrgent = $vipDays <= 3;
        }
        $emailSubscription = EmailSubscription::query()
            ->where('email', $u->email)
            ->first();

        $roleLabel = match ($u->role) {
            'svip' => 'SVIP',
            'vip' => 'VIP 会员',
            'admin' => '管理员',
            default => '免费用户',
        };

        $timeline = $this->buildTimeline($u);
        $vipActivities = VipActivityFeed::recent(50);

        return view('dashboard', compact(
            'postsCount',
            'viewsCount',
            'favoritesCount',
            'commentsCount',
            'footprintCount',
            'handle',
            'vipDays',
            'vipSecondsLeft',
            'vipIsUrgent',
            'vipExpiresAt',
            'emailSubscription',
            'roleLabel',
            'timeline',
            'vipActivities',
        ));
    }

    public function edit(): View
    {
        return view('dashboard-edit');
    }

    public function comments(): View
    {
        $items = Comment::query()
            ->where('user_id', auth()->id())
            ->where('is_hidden', false)
            ->with('commentable')
            ->latest()
            ->paginate(20);

        return view('dashboard-comments', compact('items'));
    }

    private function buildTimeline(User $u): Collection
    {
        $items = collect();

        $posts = UserPost::query()
            ->where('user_id', $u->id)
            ->where('status', 'approved')
            ->latest('updated_at')
            ->limit(5)
            ->get();
        foreach ($posts as $p) {
            $items->push([
                'at' => $p->updated_at ?? $p->created_at,
                'text' => '发布了《'.$p->title.'》',
            ]);
        }

        $favs = UserAction::query()
            ->where('user_id', $u->id)
            ->where('type', 'favorite')
            ->with('actionable')
            ->latest()
            ->limit(5)
            ->get();
        foreach ($favs as $f) {
            $items->push([
                'at' => $f->created_at,
                'text' => '收藏了《'.$this->actionableLabel($f->actionable).'》',
            ]);
        }

        $comments = Comment::query()
            ->where('user_id', $u->id)
            ->where('is_hidden', false)
            ->with('commentable')
            ->latest()
            ->limit(5)
            ->get();
        foreach ($comments as $c) {
            $items->push([
                'at' => $c->created_at,
                'text' => '评论了《'.$this->actionableLabel($c->commentable).'》',
            ]);
        }

        $subs = DB::table('subscriptions')
            ->where('user_id', $u->id)
            ->orderByDesc('id')
            ->limit(4)
            ->get();
        foreach ($subs as $s) {
            $planText = match ($s->plan ?? '') {
                'monthly' => '月度会员',
                'yearly' => '年度会员',
                'lifetime' => '终身会员',
                default => '会员',
            };
            $items->push([
                'at' => \Carbon\Carbon::parse($s->created_at),
                'text' => '开通 / 续费了 '.$planText,
            ]);
        }

        return $items
            ->filter(fn (array $row) => $row['at'] !== null)
            ->sortByDesc(fn (array $row) => $row['at'])
            ->take(12)
            ->values();
    }

    private function actionableLabel(?object $m): string
    {
        if (! $m) {
            return '已删除内容';
        }
        if ($m instanceof Article) {
            return $m->title;
        }
        if ($m instanceof Project) {
            return $m->name;
        }
        if ($m instanceof UserPost) {
            return $m->title;
        }

        return '内容';
    }
}
