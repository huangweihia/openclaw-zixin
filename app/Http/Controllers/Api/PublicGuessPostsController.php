<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserPost;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

/**
 * 「猜你感兴趣」：仅用户投稿；10 条，必含当前池内加热权重最高的一条。
 */
class PublicGuessPostsController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        if (! Schema::hasColumn('user_posts', 'heat_score')) {
            return response()->json(['ok' => true, 'items' => []]);
        }

        $user = $request->user();
        $canVip = (bool) $user?->canAccessVipExclusiveContent();

        $q = UserPost::query()
            ->publicFeed()
            ->with('author:id,name')
            ->orderByDesc('boost_weight')
            ->orderByDesc('heat_score')
            ->orderByDesc('like_count')
            ->limit(200);

        $pool = $q->get();
        if ($pool->isEmpty()) {
            return response()->json(['ok' => true, 'items' => []]);
        }

        $topBoost = $pool->sortByDesc(fn (UserPost $p) => [$p->boost_weight, $p->heat_score])->first();
        $rest = $pool->filter(fn (UserPost $p) => $p->id !== $topBoost->id)->values();

        $weighted = $rest->map(function (UserPost $p) use ($canVip) {
            $m = ($p->visibility === 'vip' && $canVip) ? 1.15 : 1.0;

            return [
                'post' => $p,
                'w' => max(1, (int) (($p->boost_weight * 100 + $p->heat_score + $p->like_count * 2) * $m + random_int(1, 40))),
            ];
        });

        $pick = [];
        $candidates = $weighted->all();
        $need = min(9, count($candidates));
        for ($i = 0; $i < $need; $i++) {
            $sum = array_sum(array_column($candidates, 'w'));
            if ($sum <= 0) {
                break;
            }
            $r = random_int(1, $sum);
            $acc = 0;
            foreach ($candidates as $idx => $row) {
                $acc += $row['w'];
                if ($r <= $acc) {
                    $pick[] = $row['post'];
                    $candidates[$idx]['w'] = 0;
                    break;
                }
            }
        }

        $merged = collect([$topBoost])->merge($pick)->unique('id')->take(10)->values();

        $items = $merged->map(function (UserPost $p) {
            return [
                'id' => $p->id,
                'title' => $p->title,
                'url' => route('posts.show', $p),
                'type' => $p->type,
                'boost_weight' => (int) $p->boost_weight,
                'heat_score' => (int) $p->heat_score,
            ];
        });

        return response()->json(['ok' => true, 'items' => $items]);
    }
}
