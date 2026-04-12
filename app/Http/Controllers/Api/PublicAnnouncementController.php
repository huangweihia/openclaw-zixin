<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * 站点公告（与前台跑马灯/浮动公告同源 announcements 表）
 */
class PublicAnnouncementController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        if (! Schema::hasTable('announcements')) {
            return response()->json(['success' => true, 'data' => []]);
        }

        $placement = strtolower(trim((string) $request->query('placement', '')));
        $floating = $request->query('floating');
        $wantFloating = $floating === '1' || $floating === 'true';

        $q = Announcement::query()
            ->where('is_published', true)
            ->where(function ($query) {
                $query->whereNull('expires_at')->orWhere('expires_at', '>', now());
            });

        if (Schema::hasColumn('announcements', 'is_floating')) {
            if ($wantFloating) {
                $q->where('is_floating', true);
            } else {
                $q->where(function ($sub) {
                    $sub->where('is_floating', false)->orWhereNull('is_floating');
                });
            }
        } elseif ($wantFloating) {
            return response()->json(['success' => true, 'data' => []]);
        }

        if ($placement !== '' && Schema::hasColumn('announcements', 'display_position')) {
            $q->where(function ($sub) use ($placement) {
                $sub->where('display_position', $placement);
                if ($placement === 'top') {
                    $sub->orWhereNull('display_position')->orWhere('display_position', '');
                }
            });
        }

        $rows = $q
            ->orderByRaw("CASE priority WHEN 'high' THEN 1 WHEN 'medium' THEN 2 ELSE 3 END")
            ->orderByDesc('published_at')
            ->limit(min(max((int) $request->query('limit', 30), 1), 50))
            ->get();

        $data = $rows->map(function (Announcement $a) {
            $plain = trim(preg_replace('/\s+/u', ' ', strip_tags((string) $a->content)));

            return [
                'id' => $a->id,
                'title' => $a->title,
                'summary' => Str::limit($plain, 200),
                'published_at' => $a->published_at?->toIso8601String(),
                'expires_at' => $a->expires_at?->toIso8601String(),
                'priority' => $a->priority,
            ];
        })->values();

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    public function show(int $id): JsonResponse
    {
        if (! Schema::hasTable('announcements')) {
            return response()->json(['message' => '服务暂不可用'], 503);
        }

        $a = Announcement::query()
            ->whereKey($id)
            ->where('is_published', true)
            ->where(function ($query) {
                $query->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->firstOrFail();

        $payload = [
            'id' => $a->id,
            'title' => $a->title,
            'content' => $a->content,
            'published_at' => $a->published_at?->toIso8601String(),
            'expires_at' => $a->expires_at?->toIso8601String(),
            'priority' => $a->priority,
        ];
        if (Schema::hasColumn('announcements', 'cover_image')) {
            $payload['cover_image'] = $a->cover_image;
        }

        return response()->json([
            'success' => true,
            'data' => $payload,
        ]);
    }
}
