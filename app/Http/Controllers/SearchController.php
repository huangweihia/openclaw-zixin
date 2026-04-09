<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Project;
use App\Models\UserPost;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SearchController extends Controller
{
    public function __invoke(Request $request): View
    {
        $q = $request->string('q')->trim()->toString();

        $articles = collect();
        $projects = collect();
        $userPosts = collect();

        if ($q !== '') {
            $articles = Article::query()
                ->where('is_published', true)
                ->where(function ($builder) use ($q) {
                    $builder
                        ->where('title', 'like', '%'.$q.'%')
                        ->orWhere('summary', 'like', '%'.$q.'%')
                        ->orWhere('content', 'like', '%'.$q.'%');
                })
                ->with(['category'])
                ->orderByDesc('published_at')
                ->limit(20)
                ->get();

            $projects = Project::query()
                ->where(function ($builder) use ($q) {
                    $builder
                        ->where('name', 'like', '%'.$q.'%')
                        ->orWhere('description', 'like', '%'.$q.'%')
                        ->orWhere('full_name', 'like', '%'.$q.'%');
                })
                ->with(['category'])
                ->orderByDesc('stars')
                ->limit(20)
                ->get();

            $userPosts = UserPost::query()
                ->publicFeed()
                ->where(function ($builder) use ($q) {
                    $builder
                        ->where('title', 'like', '%'.$q.'%')
                        ->orWhere('content', 'like', '%'.$q.'%');
                })
                ->orderByDesc('audited_at')
                ->limit(20)
                ->get();
        }

        return view('search', [
            'q' => $q,
            'articles' => $articles,
            'projects' => $projects,
            'userPosts' => $userPosts,
        ]);
    }
}
