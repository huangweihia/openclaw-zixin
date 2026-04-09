<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProjectController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));

        $projects = Project::query()
            ->with('category:id,name,slug')
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($query) use ($q) {
                    $query->where('name', 'like', '%'.$q.'%')
                        ->orWhere('full_name', 'like', '%'.$q.'%')
                        ->orWhere('url', 'like', '%'.$q.'%');
                });
            })
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return response()->json($projects);
    }

    public function show(int $projectId): JsonResponse
    {
        $project = Project::query()
            ->with('category:id,name,slug')
            ->findOrFail($projectId);

        return response()->json(['project' => $project]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validated($request);
        $project = Project::query()->create($data);

        return response()->json([
            'message' => '项目已创建',
            'project' => $project->fresh()->load('category:id,name,slug'),
        ], 201);
    }

    public function update(Request $request, int $projectId): JsonResponse
    {
        $project = Project::query()->findOrFail($projectId);
        $data = $this->validated($request, $project->id);
        $project->fill($data)->save();

        return response()->json([
            'message' => '项目已更新',
            'project' => $project->fresh()->load('category:id,name,slug'),
        ]);
    }

    public function destroy(int $projectId): JsonResponse
    {
        $project = Project::query()->findOrFail($projectId);
        $project->delete();

        return response()->json(['message' => '项目已删除']);
    }

    private function validated(Request $request, ?int $ignoreProjectId = null): array
    {
        $urlRule = Rule::unique('projects', 'url');
        if ($ignoreProjectId !== null) {
            $urlRule = $urlRule->ignore($ignoreProjectId);
        }

        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'full_name' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'url' => ['required', 'string', 'max:255', $urlRule],
            'language' => ['nullable', 'string', 'max:50'],
            'stars' => ['sometimes', 'integer', 'min:0'],
            'forks' => ['sometimes', 'integer', 'min:0'],
            'score' => ['sometimes', 'numeric', 'min:0', 'max:999.99'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['string', 'max:100'],
            'monetization' => ['nullable', 'string'],
            'difficulty' => ['sometimes', Rule::in(['easy', 'medium', 'hard'])],
            'is_featured' => ['sometimes', 'boolean'],
            'is_vip' => ['sometimes', 'boolean'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
        ]);
    }
}
