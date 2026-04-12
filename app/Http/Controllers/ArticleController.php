<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Category;
use App\Models\CommentLike;
use App\Models\UserAction;
use App\Support\CommentThreadBuilder;
use App\Support\ViewHistoryRecorder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ArticleController extends Controller
{
    public function index(Request $request): View
    {
        $categories = Category::query()->orderByDesc('sort')->orderBy('name')->get();

        $query = Article::query()
            ->where('is_published', true)
            ->with(['category', 'author']);

        $slug = $request->string('category')->trim()->toString();
        if ($slug !== '') {
            $cat = Category::query()->where('slug', $slug)->first();
            if ($cat) {
                $query->where('category_id', $cat->id);
            }
        }

        $sort = $request->string('sort', 'latest')->toString();
        match ($sort) {
            'hot' => $query->orderByDesc('view_count')->orderByDesc('published_at'),
            'vip' => $query->where('is_vip', true)->orderByDesc('published_at'),
            default => $query->orderByDesc('published_at'),
        };

        $q = $request->string('q')->trim()->toString();
        if ($q !== '') {
            $query->where(function ($qBuilder) use ($q) {
                $qBuilder
                    ->where('title', 'like', '%'.$q.'%')
                    ->orWhere('summary', 'like', '%'.$q.'%')
                    ->orWhere('content', 'like', '%'.$q.'%');
            });
        } else {
            // 列表页不加载 longText content，减轻 IO（有搜索关键词时再查正文）
            $query->select([
                'id',
                'category_id',
                'title',
                'slug',
                'summary',
                'cover_image',
                'author_id',
                'view_count',
                'like_count',
                'is_vip',
                'is_published',
                'published_at',
                'created_at',
                'updated_at',
            ]);
        }

        $articles = $query->paginate(12)->appends($request->query());

        $canAccessVip = $request->user()
            && in_array($request->user()->role, ['vip', 'svip', 'admin'], true);

        return view('articles.index', [
            'categories' => $categories,
            'articles' => $articles,
            'currentCategory' => $slug,
            'currentSort' => $sort,
            'searchQ' => $q,
            'canAccessVip' => $canAccessVip,
        ]);
    }

    public function show(Request $request, Article $article): View
    {
        if (! $article->is_published) {
            abort(404);
        }

        $canReadFull = ! $article->is_vip
            || ($request->user() && in_array($request->user()->role, ['vip', 'svip', 'admin'], true));

        if ($article->is_vip && ! $canReadFull) {
            if (! $request->user()) {
                return redirect()->guest(route('login'));
            }

            return redirect()
                ->route('pricing')
                ->with('warning', '该文章为 VIP 专属内容，请先开通会员后阅读。');
        }

        $article->increment('view_count');
        $article->refresh();

        ViewHistoryRecorder::record($request->user(), $article);

        $userLiked = false;
        $userFavorited = false;
        if ($request->user()) {
            $types = UserAction::query()
                ->where('user_id', $request->user()->id)
                ->where('actionable_type', $article->getMorphClass())
                ->where('actionable_id', $article->id)
                ->pluck('type');
            $userLiked = $types->contains('like');
            $userFavorited = $types->contains('favorite');
        }

        $comments = $article->comments()
            ->whereNull('parent_id')
            ->where('is_hidden', false)
            ->latest()
            ->with(['user'])
            ->paginate(20)
            ->withQueryString();

        CommentThreadBuilder::attachNestedReplies($comments, $article);

        $likedCommentIds = [];
        if ($request->user()) {
            $ids = CommentThreadBuilder::collectTreeCommentIds($comments);
            $likedCommentIds = CommentLike::query()
                ->where('user_id', $request->user()->id)
                ->whereIn('comment_id', $ids)
                ->pluck('comment_id')
                ->all();
        }

        $related = Article::query()
            ->where('is_published', true)
            ->where('id', '!=', $article->id)
            ->when($article->category_id, fn ($q) => $q->where('category_id', $article->category_id))
            ->orderByDesc('view_count')
            ->limit(5)
            ->get();

        return view('articles.show', [
            'article' => $article->load(['category', 'author']),
            'canReadFull' => $canReadFull,
            'related' => $related,
            'userLiked' => $userLiked,
            'userFavorited' => $userFavorited,
            'comments' => $comments,
            'likedCommentIds' => $likedCommentIds,
        ]);
    }
}
