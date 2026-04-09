<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Project;
use App\Models\SideHustleCase;
use App\Models\UserAction;
use App\Models\UserPost;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FavoriteController extends Controller
{
    public function index(Request $request): View
    {
        $type = $request->string('type')->trim()->toString();

        $articleMorph = (new Article)->getMorphClass();
        $projectMorph = (new Project)->getMorphClass();
        $caseMorph = (new SideHustleCase)->getMorphClass();
        $postMorph = (new UserPost)->getMorphClass();

        $query = UserAction::query()
            ->where('user_id', $request->user()->id)
            ->where('type', 'favorite')
            ->whereIn('actionable_type', [$articleMorph, $projectMorph, $caseMorph, $postMorph]);

        if ($type === 'article') {
            $query->where('actionable_type', $articleMorph);
        } elseif ($type === 'project') {
            $query->where('actionable_type', $projectMorph);
        } elseif ($type === 'case') {
            $query->where('actionable_type', $caseMorph);
        } elseif ($type === 'post') {
            $query->where('actionable_type', $postMorph);
        }

        $actions = $query
            ->orderByDesc('created_at')
            ->paginate(12)
            ->withQueryString();

        $actions->getCollection()->loadMorph('actionable', [
            Article::class => ['category'],
            Project::class => ['category'],
            SideHustleCase::class => [],
            UserPost::class => ['author'],
        ]);

        return view('favorites.index', [
            'actions' => $actions,
            'currentType' => $type,
        ]);
    }

    public function destroy(Request $request, UserAction $userAction): RedirectResponse
    {
        abort_unless(
            $userAction->user_id === $request->user()->id && $userAction->type === 'favorite',
            403
        );

        $userAction->delete();

        return back()->with('success', '已移除收藏');
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer'],
        ]);

        $deleted = UserAction::query()
            ->where('user_id', $request->user()->id)
            ->where('type', 'favorite')
            ->whereIn('id', $data['ids'])
            ->delete();

        return back()->with('success', '已删除 '.$deleted.' 条收藏');
    }
}
