<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AiToolMonetization;
use App\Models\Category;
use App\Models\PrivateTrafficSop;
use App\Models\Project;
use App\Models\SideHustleCase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

/**
 * 前台浏览类内容 JSON（与 Web 列表/详情对齐；游客 = 仅 public 可见性，不含 VIP 专享正文）
 */
class PublicBrowseController extends Controller
{
    public function projectsIndex(Request $request): JsonResponse
    {
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

        $perPage = min(max((int) $request->input('per_page', 12), 1), 50);
        /** @var LengthAwarePaginator $paginator */
        $paginator = $query->paginate($perPage);

        $data = collect($paginator->items())->map(fn (Project $p) => [
            'id' => $p->id,
            'name' => $p->name,
            'full_name' => $p->full_name,
            'description' => $p->description,
            'url' => $p->url,
            'language' => $p->language,
            'stars' => $p->stars,
            'forks' => $p->forks,
            'is_vip' => (bool) $p->is_vip,
            'category' => $p->category ? ['name' => $p->category->name, 'slug' => $p->category->slug] : null,
        ])->values();

        return $this->paginatedJson($data, $paginator);
    }

    public function projectsShow(Project $project): JsonResponse
    {
        $project->load('category');

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $project->id,
                'name' => $project->name,
                'full_name' => $project->full_name,
                'description' => $project->description,
                'url' => $project->url,
                'language' => $project->language,
                'stars' => $project->stars,
                'forks' => $project->forks,
                'tags' => $project->tags,
                'monetization' => $project->monetization,
                'difficulty' => $project->difficulty,
                'is_vip' => (bool) $project->is_vip,
                'category' => $project->category ? [
                    'name' => $project->category->name,
                    'slug' => $project->category->slug,
                ] : null,
            ],
        ]);
    }

    public function casesIndex(Request $request): JsonResponse
    {
        $query = SideHustleCase::query()
            ->where('status', 'approved')
            ->where('visibility', 'public')
            ->orderByDesc('audited_at')
            ->orderByDesc('id');

        $cat = $request->string('category')->trim()->toString();
        if ($cat !== '' && in_array($cat, ['online', 'offline', 'hybrid'], true)) {
            $query->where('category', $cat);
        }

        $perPage = min(max((int) $request->input('per_page', 12), 1), 50);
        /** @var LengthAwarePaginator $paginator */
        $paginator = $query->paginate($perPage);

        $data = collect($paginator->items())->map(fn (SideHustleCase $c) => [
            'id' => $c->id,
            'title' => $c->title,
            'slug' => $c->slug,
            'summary' => $c->summary,
            'category' => $c->category,
            'view_count' => $c->view_count,
            'like_count' => $c->like_count,
        ])->values();

        return $this->paginatedJson($data, $paginator);
    }

    public function casesShow(string $slug): JsonResponse
    {
        $case = SideHustleCase::query()
            ->where('slug', $slug)
            ->where('status', 'approved')
            ->firstOrFail();

        $guestOk = $case->visibility === 'public';
        if (! $guestOk) {
            return response()->json([
                'success' => false,
                'message' => '该内容为会员可见，请在官网登录后阅读。',
                'data' => [
                    'title' => $case->title,
                    'slug' => $case->slug,
                    'summary' => $case->summary,
                    'visibility' => $case->visibility,
                    'can_read_full' => false,
                ],
            ], 403);
        }

        $case->increment('view_count');
        $case->refresh();

        $bodyHtml = Str::markdown((string) $case->content);
        $stepsHtml = $case->steps ? Str::markdown((string) $case->steps) : null;

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $case->id,
                'title' => $case->title,
                'slug' => $case->slug,
                'summary' => $case->summary,
                'category' => $case->category,
                'view_count' => $case->view_count,
                'like_count' => $case->like_count,
                'can_read_full' => true,
                'content_html' => $bodyHtml,
                'steps_html' => $stepsHtml,
            ],
        ]);
    }

    public function toolsIndex(Request $request): JsonResponse
    {
        $query = AiToolMonetization::query()
            ->where('visibility', 'public')
            ->orderByDesc('id');

        $cat = $request->string('category')->trim()->toString();
        if ($cat !== '' && in_array($cat, ['image', 'text', 'video', 'audio', 'code'], true)) {
            $query->where('category', $cat);
        }

        $perPage = min(max((int) $request->input('per_page', 12), 1), 50);
        /** @var LengthAwarePaginator $paginator */
        $paginator = $query->paginate($perPage);

        $data = collect($paginator->items())->map(fn (AiToolMonetization $t) => [
            'id' => $t->id,
            'tool_name' => $t->tool_name,
            'slug' => $t->slug,
            'category' => $t->category,
            'pricing_model' => $t->pricing_model,
            'view_count' => $t->view_count,
        ])->values();

        return $this->paginatedJson($data, $paginator);
    }

    public function toolsShow(string $slug): JsonResponse
    {
        $tool = AiToolMonetization::query()->where('slug', $slug)->firstOrFail();

        if ($tool->visibility !== 'public') {
            return response()->json([
                'success' => false,
                'message' => '该内容为会员可见，请在官网登录后阅读。',
                'data' => [
                    'tool_name' => $tool->tool_name,
                    'slug' => $tool->slug,
                    'can_read_full' => false,
                ],
            ], 403);
        }

        $tool->increment('view_count');
        $tool->refresh();

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $tool->id,
                'tool_name' => $tool->tool_name,
                'slug' => $tool->slug,
                'tool_url' => $tool->tool_url,
                'category' => $tool->category,
                'pricing_model' => $tool->pricing_model,
                'view_count' => $tool->view_count,
                'can_read_full' => true,
                'content_html' => Str::markdown((string) $tool->content),
            ],
        ]);
    }

    public function sopsIndex(Request $request): JsonResponse
    {
        $query = PrivateTrafficSop::query()
            ->where('visibility', 'public')
            ->orderByDesc('id');

        $perPage = min(max((int) $request->input('per_page', 12), 1), 50);
        /** @var LengthAwarePaginator $paginator */
        $paginator = $query->paginate($perPage);

        $data = collect($paginator->items())->map(fn (PrivateTrafficSop $s) => [
            'id' => $s->id,
            'title' => $s->title,
            'slug' => $s->slug,
            'summary' => $s->summary,
            'platform' => $s->platform,
            'view_count' => $s->view_count,
        ])->values();

        return $this->paginatedJson($data, $paginator);
    }

    public function sopsShow(string $slug): JsonResponse
    {
        $sop = PrivateTrafficSop::query()->where('slug', $slug)->firstOrFail();

        if ($sop->visibility !== 'public') {
            return response()->json([
                'success' => false,
                'message' => '该内容为会员可见，请在官网登录后阅读。',
                'data' => [
                    'title' => $sop->title,
                    'slug' => $sop->slug,
                    'summary' => $sop->summary,
                    'can_read_full' => false,
                ],
            ], 403);
        }

        $sop->increment('view_count');
        $sop->refresh();

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $sop->id,
                'title' => $sop->title,
                'slug' => $sop->slug,
                'summary' => $sop->summary,
                'view_count' => $sop->view_count,
                'can_read_full' => true,
                'content_html' => Str::markdown((string) $sop->content),
            ],
        ]);
    }

    /**
     * @param  \Illuminate\Support\Collection<int, mixed>  $data
     */
    private function paginatedJson($data, LengthAwarePaginator $paginator): JsonResponse
    {
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
}
