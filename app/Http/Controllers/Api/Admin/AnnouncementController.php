<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class AnnouncementController extends Controller
{
    public function index(): JsonResponse
    {
        $announcements = Announcement::query()
            ->with('creator:id,name,email')
            ->orderByDesc('is_published')
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->get();

        return response()->json(['announcements' => $announcements]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'min:5', 'max:255'],
            'content' => ['required', 'string'],
            'priority' => ['sometimes', Rule::in(['low', 'medium', 'high'])],
            'display_position' => ['sometimes', Rule::in(['top', 'bottom', 'left', 'right'])],
            'is_floating' => ['sometimes', 'boolean'],
            'cover_image' => ['nullable', 'string', 'max:500'],
            'float_width' => ['nullable', 'integer', 'min:80', 'max:480'],
            'float_height' => ['nullable', 'integer', 'min:40', 'max:600'],
            'is_published' => ['sometimes', 'boolean'],
            'expires_at' => ['nullable', 'date'],
        ]);

        $data = $this->filterAnnouncementColumns($data);
        $row = new Announcement($data);
        $row->created_by = $request->user()->id;
        if (! empty($data['is_published'])) {
            $row->published_at = now();
        }
        $row->save();

        return response()->json([
            'message' => '公告已创建',
            'announcement' => $row->fresh()->load('creator:id,name,email'),
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $announcement = Announcement::query()->findOrFail($id);
        $data = $request->validate([
            'title' => ['sometimes', 'string', 'min:5', 'max:255'],
            'content' => ['sometimes', 'string'],
            'priority' => ['sometimes', Rule::in(['low', 'medium', 'high'])],
            'display_position' => ['sometimes', Rule::in(['top', 'bottom', 'left', 'right'])],
            'is_floating' => ['sometimes', 'boolean'],
            'cover_image' => ['nullable', 'string', 'max:500'],
            'float_width' => ['nullable', 'integer', 'min:80', 'max:480'],
            'float_height' => ['nullable', 'integer', 'min:40', 'max:600'],
            'is_published' => ['sometimes', 'boolean'],
            'expires_at' => ['nullable', 'date'],
        ]);

        $data = $this->filterAnnouncementColumns($data);
        $announcement->fill($data);
        if (array_key_exists('is_published', $data) && $data['is_published'] && $announcement->published_at === null) {
            $announcement->published_at = now();
        }
        $announcement->save();

        return response()->json([
            'message' => '已更新',
            'announcement' => $announcement->fresh()->load('creator:id,name,email'),
        ]);
    }

    public function togglePublish(int $id): JsonResponse
    {
        $announcement = Announcement::query()->findOrFail($id);
        $announcement->is_published = ! $announcement->is_published;
        if ($announcement->is_published && $announcement->published_at === null) {
            $announcement->published_at = now();
        }
        $announcement->save();

        return response()->json([
            'message' => $announcement->is_published ? '已发布' : '已下架',
            'announcement' => $announcement->fresh()->load('creator:id,name,email'),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        Announcement::query()->whereKey($id)->delete();

        return response()->json(['message' => '已删除']);
    }

    /**
     * 仅写入 announcements 表中已存在的列，避免未执行迁移的环境在保存时报错。
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function filterAnnouncementColumns(array $data): array
    {
        $columns = array_flip(Schema::getColumnListing('announcements'));

        return array_filter(
            $data,
            static fn ($_, string $key) => isset($columns[$key]),
            ARRAY_FILTER_USE_BOTH
        );
    }
}
