<?php

namespace App\Support;

use App\Models\Comment;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class CommentThreadBuilder
{
    /**
     * 将根评论下的所有子回复打平为同一层级（仅内存展示，最多两级视觉结构）。
     *
     * @param  LengthAwarePaginator<int, Comment>  $rootPaginator
     */
    public static function attachNestedReplies(LengthAwarePaginator $rootPaginator, Model $commentable): void
    {
        if ($rootPaginator->isEmpty()) {
            return;
        }

        $all = Comment::query()
            ->where('commentable_type', $commentable->getMorphClass())
            ->where('commentable_id', $commentable->getKey())
            ->where('is_hidden', false)
            ->with('user')
            ->orderBy('created_at')
            ->get();

        foreach ($rootPaginator as $root) {
            $flat = self::flatDescendants((int) $root->id, $all);
            $root->setRelation('replies', $flat);
        }
    }

    /**
     * @param  Collection<int, Comment>  $all
     * @return Collection<int, Comment>
     */
    private static function flatDescendants(int $rootId, Collection $all): Collection
    {
        $out = collect();
        $queue = [$rootId];
        $visited = [$rootId => true];
        while (count($queue) > 0) {
            $pid = array_shift($queue);
            foreach ($all->where('parent_id', $pid) as $child) {
                if (isset($visited[$child->id])) {
                    continue;
                }
                $visited[$child->id] = true;
                $queue[] = $child->id;
                $out->push($child);
            }
        }

        return $out->sortBy('created_at')->values();
    }

    /**
     * @param  iterable<\App\Models\Comment>  $roots
     * @return array<int, int>
     */
    public static function collectTreeCommentIds(iterable $roots): array
    {
        $ids = [];

        foreach ($roots as $root) {
            $ids[] = $root->id;
            foreach ($root->replies ?? [] as $reply) {
                $ids[] = $reply->id;
            }
        }

        return array_values(array_unique($ids));
    }
}
