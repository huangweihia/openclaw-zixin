<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\CommentLike;
use App\Models\Project;
use App\Models\UserAction;
use App\Support\CommentThreadBuilder;
use Illuminate\Http\Request;
use App\Support\ViewHistoryRecorder;
use Illuminate\View\View;

class ProjectController extends Controller
{
    public function index(Request $request): View
    {
        $categories = Category::query()->orderByDesc('sort')->orderBy('name')->get();

        $query = Project::query()->with('category');

        $slug = $request->string('category')->trim()->toString();
        if ($slug !== '') {
            $cat = Category::query()->where('slug', $slug)->first();
            if ($cat) {
                $query->where('category_id', $cat->id);
            }
        }

        $sort = $request->string('sort', 'stars')->toString();
        if ($sort === 'latest') {
            $query->orderByDesc('created_at');
        } else {
            $query->orderByDesc('stars')->orderByDesc('created_at');
        }

        $q = $request->string('q')->trim()->toString();
        if ($q !== '') {
            $query->where(function ($qBuilder) use ($q) {
                $qBuilder
                    ->where('name', 'like', '%'.$q.'%')
                    ->orWhere('description', 'like', '%'.$q.'%')
                    ->orWhere('full_name', 'like', '%'.$q.'%');
            });
        }

        $lang = $request->string('language')->trim()->toString();
        if ($lang !== '') {
            $query->where('language', $lang);
        }

        $projects = $query->paginate(12)->appends($request->query());

        return view('projects.index', [
            'categories' => $categories,
            'projects' => $projects,
            'currentCategory' => $slug,
            'currentSort' => $sort,
            'searchQ' => $q,
            'currentLanguage' => $lang,
        ]);
    }

    public function show(Request $request, Project $project): View
    {
        ViewHistoryRecorder::record($request->user(), $project);

        $userFavorited = false;
        if ($request->user()) {
            $userFavorited = UserAction::query()
                ->where('user_id', $request->user()->id)
                ->where('actionable_type', $project->getMorphClass())
                ->where('actionable_id', $project->id)
                ->where('type', 'favorite')
                ->exists();
        }

        $comments = $project->comments()
            ->whereNull('parent_id')
            ->where('is_hidden', false)
            ->latest()
            ->with(['user'])
            ->paginate(20)
            ->withQueryString();

        CommentThreadBuilder::attachNestedReplies($comments, $project);

        $likedCommentIds = [];
        if ($request->user()) {
            $ids = CommentThreadBuilder::collectTreeCommentIds($comments);
            $likedCommentIds = CommentLike::query()
                ->where('user_id', $request->user()->id)
                ->whereIn('comment_id', $ids)
                ->pluck('comment_id')
                ->all();
        }

        $related = Project::query()
            ->where('id', '!=', $project->id)
            ->when($project->category_id, fn ($q) => $q->where('category_id', $project->category_id))
            ->orderByDesc('stars')
            ->limit(5)
            ->get();

        return view('projects.show', [
            'project' => $project->load('category'),
            'related' => $related,
            'userFavorited' => $userFavorited,
            'comments' => $comments,
            'likedCommentIds' => $likedCommentIds,
        ]);
    }
}
