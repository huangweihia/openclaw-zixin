<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Category;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class PublicArticleController extends Controller
{
    /**
     * 分类列表（小程序筛选用）
     */
    public function categories(): JsonResponse
    {
        $rows = Category::query()
            ->orderByDesc('sort')
            ->orderBy('name')
            ->get(['id', 'name', 'slug', 'description']);

        return response()->json([
            'success' => true,
            'data' => $rows,
        ]);
    }

    /**
     * 已发布文章分页（与 Web 列表筛选逻辑对齐；游客不可读 VIP 正文，仅列表展示 is_vip）
     */
    public function index(Request $request): JsonResponse
    {
        $query = Article::query()
            ->where('is_published', true)
            ->with(['category:id,name,slug', 'author:id,name']);

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
            'vip' => $query->where(function ($q) {
                $q->where('is_vip', true)->orWhere('is_vip_only', true);
            })->orderByDesc('published_at'),
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
                'is_vip_only',
                'is_published',
                'published_at',
                'created_at',
                'updated_at',
            ]);
        }

        $perPage = min(max((int) $request->input('per_page', 15), 1), 50);
        /** @var LengthAwarePaginator $paginator */
        $paginator = $query->paginate($perPage);

        $data = collect($paginator->items())->map(function (Article $a) {
            return [
                'id' => $a->id,
                'title' => $a->title,
                'slug' => $a->slug,
                'summary' => $a->summary,
                'cover_image' => $this->absoluteMediaUrl($a->cover_image),
                'view_count' => $a->view_count,
                'like_count' => $a->like_count,
                'is_vip' => $a->is_vip,
                'is_vip_only' => $a->is_vip_only,
                'published_at' => $a->published_at?->toIso8601String(),
                'category' => $a->category ? [
                    'name' => $a->category->name,
                    'slug' => $a->category->slug,
                ] : null,
                'author' => $a->author ? ['name' => $a->author->name] : null,
            ];
        })->values();

        return response()->json([
            'success' => true,
            'data' => $data,
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }

    /**
     * 文章详情（游客：VIP 文不返回正文，与 Web 未登录一致）
     */
    public function show(Request $request, string $slug): JsonResponse
    {
        $article = Article::query()
            ->where('slug', $slug)
            ->where('is_published', true)
            ->with(['category:id,name,slug', 'author:id,name'])
            ->firstOrFail();

        $article->increment('view_count');
        $article->refresh();

        /** @var User|null $user */
        $user = auth('sanctum')->user();
        $canReadFull = $article->userCanReadFull($user instanceof User ? $user : null);

        $data = [
            'id' => $article->id,
            'title' => $article->title,
            'slug' => $article->slug,
            'summary' => $article->summary,
            'cover_image' => $this->absoluteMediaUrl($article->cover_image),
            'view_count' => $article->view_count,
            'like_count' => $article->like_count,
            'is_vip' => $article->is_vip,
            'is_vip_only' => $article->is_vip_only,
            'published_at' => $article->published_at?->toIso8601String(),
            'category' => $article->category ? [
                'name' => $article->category->name,
                'slug' => $article->category->slug,
            ] : null,
            'author' => $article->author ? [
                'id' => $article->author->id,
                'name' => $article->author->name,
            ] : null,
            'can_read_full' => $canReadFull,
            'content' => $canReadFull ? $article->content : null,
            'vip_hint' => $canReadFull
                ? null
                : ($user
                    ? ($article->is_vip_only
                        ? '本文为 SVIP 会员专享，请升级后阅读全文。'
                        : '本文为 VIP 会员专享，请开通会员后阅读全文。')
                    : ($article->is_vip_only
                        ? '本文为 SVIP 会员专享，请登录并升级后阅读全文。'
                        : '本文为 VIP 会员专享，请登录并开通会员后阅读全文。')),
        ];

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    private function absoluteMediaUrl(?string $path): ?string
    {
        if ($path === null || $path === '') {
            return null;
        }
        if (preg_match('#^https?://#i', $path)) {
            return $path;
        }

        return url($path);
    }
}
