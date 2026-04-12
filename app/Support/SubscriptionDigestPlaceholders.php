<?php

namespace App\Support;

use App\Models\Article;
use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * 订阅摘要类邮件（每日/每周）发送时填充的占位符，含「数据表首条」类 db_* 键。
 */
final class SubscriptionDigestPlaceholders
{
    /**
     * @param  Collection<int, Article>  $articles  已按发布时间排序的集合
     * @return array<string, string>
     */
    public static function build(string $siteName, string $articleListHtml, string $dateStr, Collection $articles): array
    {
        $lead = $articles->first();
        $project = Project::query()->orderByDesc('id')->first();
        $user = User::query()->orderBy('id')->first();

        $digestTitle = $lead?->title ?? '';
        $digestSummary = $lead && $lead->summary
            ? Str::limit(strip_tags((string) $lead->summary), 160)
            : ($lead ? Str::limit(strip_tags((string) $lead->content), 160) : '');
        $digestUrl = $lead ? route('articles.show', $lead) : '';

        $dbArticlesUrl = $lead ? route('articles.show', $lead) : '';
        $dbProjectsUrl = $project ? route('projects.show', $project) : '';

        return [
            'site_name' => $siteName,
            'article_list_html' => $articleListHtml,
            'date' => $dateStr,
            'digest_article_title' => $digestTitle,
            'digest_article_summary' => $digestSummary,
            'digest_article_url' => $digestUrl,
            'db_articles_title' => $digestTitle,
            'db_articles_summary' => $digestSummary,
            'db_articles_url' => $dbArticlesUrl,
            'db_projects_name' => $project?->name ?? '',
            'db_projects_url' => $dbProjectsUrl,
            'db_users_name' => $user?->name ?? '',
            'db_users_email' => $user?->email ?? '',
        ];
    }
}
