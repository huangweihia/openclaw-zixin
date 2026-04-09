<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Support\AdminUniqueCode;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(): JsonResponse
    {
        $categories = Category::query()
            ->orderByDesc('sort')
            ->orderBy('name')
            ->get();

        return response()->json(['categories' => $categories]);
    }

    public function show(int $categoryId): JsonResponse
    {
        $category = Category::query()->findOrFail($categoryId);

        return response()->json(['category' => $category]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validated($request);
        $data['slug'] = AdminUniqueCode::slug($data['name'], Category::class, 'slug', null, 'cat');
        $category = Category::query()->create($data);

        return response()->json([
            'message' => '分类已创建',
            'category' => $category,
        ], 201);
    }

    public function update(Request $request, int $categoryId): JsonResponse
    {
        $category = Category::query()->findOrFail($categoryId);
        $data = $this->validated($request, $category->id);
        unset($data['slug']);
        $category->fill($data)->save();

        return response()->json([
            'message' => '分类已更新',
            'category' => $category->fresh(),
        ]);
    }

    public function destroy(int $categoryId): JsonResponse
    {
        $category = Category::query()->findOrFail($categoryId);
        $category->delete();

        return response()->json(['message' => '分类已删除']);
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
            'sort' => ['sometimes', 'integer'],
            'is_premium' => ['sometimes', 'boolean'],
        ]);
    }
}
