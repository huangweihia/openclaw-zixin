<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Support\AdminUniqueCode;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));
        $published = $request->query('published');

        $articles = Article::query()
            ->with(['category:id,name,slug', 'author:id,name,email'])
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($query) use ($q) {
                    $query->where('title', 'like', '%'.$q.'%')
                        ->orWhere('slug', 'like', '%'.$q.'%')
                        ->orWhere('summary', 'like', '%'.$q.'%');
                });
            })
            ->when($published === '0' || $published === '1', function ($query) use ($published) {
                $query->where('is_published', $published === '1');
            })
            ->when($request->filled('category_id'), function ($query) use ($request) {
                $query->where('category_id', (int) $request->query('category_id'));
            })
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return response()->json($articles);
    }

    public function show(int $articleId): JsonResponse
    {
        $article = Article::query()
            ->with(['category:id,name,slug', 'author:id,name,email'])
            ->findOrFail($articleId);

        return response()->json(['article' => $article]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validated($request);
        $data['slug'] = AdminUniqueCode::slug($data['title'], Article::class, 'slug', null, 'post');

        if (($data['is_published'] ?? false) && empty($data['published_at'] ?? null)) {
            $data['published_at'] = now();
        }

        $article = Article::query()->create($data);

        return response()->json([
            'message' => '文章已创建',
            'article' => $article->fresh()->load(['category:id,name,slug', 'author:id,name,email']),
        ], 201);
    }

    public function update(Request $request, int $articleId): JsonResponse
    {
        $article = Article::query()->findOrFail($articleId);
        $data = $this->validated($request);
        unset($data['slug']);

        $willPublish = array_key_exists('is_published', $data)
            ? (bool) $data['is_published']
            : $article->is_published;
        if ($willPublish && empty($data['published_at'] ?? null) && $article->published_at === null) {
            $data['published_at'] = now();
        }

        $article->fill($data)->save();

        return response()->json([
            'message' => '文章已更新',
            'article' => $article->fresh()->load(['category:id,name,slug', 'author:id,name,email']),
        ]);
    }

    public function destroy(int $articleId): JsonResponse
    {
        $article = Article::query()->findOrFail($articleId);
        $article->delete();

        return response()->json(['message' => '文章已删除']);
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'title' => ['required', 'string', 'max:255'],
            'summary' => ['nullable', 'string', 'max:500'],
            'content' => ['nullable', 'string'],
            'cover_image' => ['nullable', 'string', 'max:255'],
            'author_id' => ['nullable', 'integer', 'exists:users,id'],
            'view_count' => ['sometimes', 'integer', 'min:0'],
            'like_count' => ['sometimes', 'integer', 'min:0'],
            'is_vip' => ['sometimes', 'boolean'],
            'is_published' => ['sometimes', 'boolean'],
            'published_at' => ['nullable', 'date'],
            'source_url' => ['nullable', 'string', 'max:255'],
            'meta_keywords' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
        ]);
    }
}
