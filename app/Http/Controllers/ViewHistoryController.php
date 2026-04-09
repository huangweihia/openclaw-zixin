<?php

namespace App\Http\Controllers;

use App\Models\AiToolMonetization;
use App\Models\Article;
use App\Models\Project;
use App\Models\SideHustleCase;
use App\Models\UserPost;
use App\Models\ViewHistory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ViewHistoryController extends Controller
{
    public function index(Request $request): View
    {
        $query = ViewHistory::query()
            ->where('user_id', $request->user()->id);

        $type = $request->string('type')->toString();
        $map = [
            'article' => (new Article)->getMorphClass(),
            'project' => (new Project)->getMorphClass(),
            'case' => (new SideHustleCase)->getMorphClass(),
            'tool' => (new AiToolMonetization)->getMorphClass(),
            'post' => (new UserPost)->getMorphClass(),
        ];
        if ($type !== '' && isset($map[$type])) {
            $query->where('viewable_type', $map[$type]);
        }

        $date = $request->string('date')->toString();
        match ($date) {
            'today' => $query->whereDate('viewed_at', today()),
            'yesterday' => $query->whereDate('viewed_at', today()->subDay()),
            '7_days' => $query->where('viewed_at', '>=', now()->subDays(7)),
            '30_days' => $query->where('viewed_at', '>=', now()->subDays(30)),
            default => null,
        };

        $histories = $query
            ->orderByDesc('viewed_at')
            ->paginate(20)
            ->appends($request->query());

        $histories->getCollection()->loadMorph('viewable', [
            Article::class => ['category'],
            Project::class => ['category'],
            UserPost::class => ['author'],
            SideHustleCase::class => [],
            AiToolMonetization::class => [],
        ]);

        return view('history.index', [
            'histories' => $histories,
            'filterType' => $type,
            'filterDate' => $date,
        ]);
    }

    public function destroy(Request $request, ViewHistory $viewHistory): RedirectResponse
    {
        abort_unless((int) $viewHistory->user_id === (int) $request->user()->id, 403);
        $viewHistory->delete();

        return back()->with('success', '已删除该条浏览记录');
    }

    public function clear(Request $request): RedirectResponse
    {
        ViewHistory::query()->where('user_id', $request->user()->id)->delete();

        return redirect()->route('history.index')->with('success', '已清空浏览历史');
    }
}
