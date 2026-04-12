<?php

namespace App\Console\Commands;

use App\Models\UserPost;
use App\Services\UserPostBoostService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class RefreshUserPostBoostWeights extends Command
{
    protected $signature = 'user-posts:refresh-boost-weights';

    protected $description = '重新汇总投稿的 boost_weight（过期加热不再计入）';

    public function handle(): int
    {
        if (! Schema::hasTable('content_boosts') || ! Schema::hasColumn('user_posts', 'boost_weight')) {
            return self::SUCCESS;
        }

        UserPost::query()
            ->where(function ($q) {
                $q->where('boost_weight', '>', 0)->orWhereNotNull('last_boost_at');
            })
            ->orderBy('id')
            ->chunkById(200, function ($posts) {
                foreach ($posts as $post) {
                    UserPostBoostService::refreshBoostWeight($post);
                }
            });

        return self::SUCCESS;
    }
}
