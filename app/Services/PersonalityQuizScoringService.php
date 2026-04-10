<?php

namespace App\Services;

use App\Models\PersonalityDimension;
use App\Models\PersonalityQuizSetting;
use App\Models\PersonalityType;
use InvalidArgumentException;

final class PersonalityQuizScoringService
{
    public function buildPublicPayload(): array
    {
        $dimensions = PersonalityDimension::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->with(['questions' => function ($q) {
                $q->where('is_active', true)->orderBy('sort_order')->with(['options' => fn ($o) => $o->orderBy('sort_order')]);
            }])
            ->get();

        $questions = [];
        foreach ($dimensions as $dim) {
            foreach ($dim->questions as $q) {
                $questions[] = [
                    'id' => $q->id,
                    'dimension_id' => $dim->id,
                    'dimension_code' => $dim->code,
                    'body' => $q->body,
                    'options' => $q->options->map(fn ($o) => [
                        'id' => $o->id,
                        'label' => $o->label,
                        'value' => (int) $o->value,
                    ])->values()->all(),
                ];
            }
        }

        return [
            'dimensions' => $dimensions->map(fn ($d) => [
                'id' => $d->id,
                'code' => $d->code,
                'name' => $d->name,
                'model_group' => $d->model_group,
                'sort_order' => $d->sort_order,
            ])->values()->all(),
            'questions' => $questions,
            'settings' => [
                'low_match_threshold' => (int) (PersonalityQuizSetting::getValue('low_match_threshold', '60') ?? 60),
            ],
            'disclaimer' => '本测试仅供娱乐，不构成任何心理或职业建议。',
        ];
    }

    /**
     * @param  array<int|string, int>  $answers  question_id => option value (1-3)
     * @return array<string, mixed>
     */
    public function score(array $answers): array
    {
        $dimensions = PersonalityDimension::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->with(['questions' => function ($q) {
                $q->where('is_active', true)->with('options');
            }])
            ->get();

        if ($dimensions->isEmpty()) {
            throw new InvalidArgumentException('题库未配置');
        }

        $allowedByQuestion = [];
        foreach ($dimensions as $dim) {
            foreach ($dim->questions as $q) {
                $allowed = $q->options->pluck('value')->map(fn ($v) => (int) $v)->unique()->sort()->values()->all();
                $allowedByQuestion[$q->id] = $allowed;
            }
        }

        $rawScores = [];
        $levels = [];
        foreach ($dimensions as $dim) {
            $sum = 0;
            foreach ($dim->questions as $q) {
                if (! array_key_exists($q->id, $answers) && ! array_key_exists((string) $q->id, $answers)) {
                    throw new InvalidArgumentException('题目未完成：#'.$q->id);
                }
                $val = (int) ($answers[$q->id] ?? $answers[(string) $q->id]);
                $allowed = $allowedByQuestion[$q->id] ?? [];
                if ($allowed !== [] && ! in_array($val, $allowed, true)) {
                    throw new InvalidArgumentException('选项值无效：题目 #'.$q->id);
                }
                $sum += $val;
            }
            $rawScores[$dim->id] = $sum;
            $levels[$dim->id] = $this->sumToLevel($sum);
        }

        $dimensionOrder = $dimensions->pluck('id')->all();
        $userVector = array_map(fn ($id) => $this->levelNum($levels[$id]), $dimensionOrder);
        $dimCount = count($userVector);
        $maxDistance = max(1, 2 * $dimCount);

        $matchTypes = PersonalityType::query()
            ->where('is_active', true)
            ->where('is_fallback', false)
            ->whereNotNull('pattern')
            ->where('pattern', '!=', '')
            ->orderBy('sort_order')
            ->get();

        $ranked = $matchTypes->map(function (PersonalityType $type) use ($userVector, $maxDistance) {
            $vector = $this->parsePatternForLength((string) $type->pattern, count($userVector));
            $distance = 0;
            $exact = 0;
            for ($i = 0; $i < count($userVector); $i++) {
                $diff = abs($userVector[$i] - ($vector[$i] ?? 2));
                $distance += $diff;
                if ($diff === 0) {
                    $exact++;
                }
            }
            $similarity = (int) max(0, round((1 - $distance / $maxDistance) * 100));

            return [
                'type' => $type,
                'distance' => $distance,
                'exact' => $exact,
                'similarity' => $similarity,
            ];
        })->sortBy([
            ['distance', 'asc'],
            ['exact', 'desc'],
            ['similarity', 'desc'],
        ])->values();

        $bestRow = $ranked->first();
        $bestSimilarity = $bestRow ? (int) $bestRow['similarity'] : 0;
        $threshold = (int) (PersonalityQuizSetting::getValue('low_match_threshold', '60') ?? 60);
        $fallback = PersonalityType::query()->where('is_active', true)->where('is_fallback', true)->first();

        $usedFallback = false;
        if ($bestRow === null || $matchTypes->isEmpty()) {
            $final = $fallback ?? $this->makeSyntheticFallback();
            $usedFallback = true;
        } elseif ($bestSimilarity < $threshold && $fallback) {
            $final = $fallback;
            $usedFallback = true;
        } else {
            $final = $bestRow['type'];
        }

        $dimPayload = $dimensions->map(function ($d) use ($rawScores, $levels) {
            $lvl = $levels[$d->id];

            return [
                'id' => $d->id,
                'code' => $d->code,
                'name' => $d->name,
                'model_group' => $d->model_group,
                'level' => $lvl,
                'raw_score' => $rawScores[$d->id],
                'explanation' => match ($lvl) {
                    'L' => $d->explanation_l,
                    'M' => $d->explanation_m,
                    default => $d->explanation_h,
                },
            ];
        })->values()->all();

        $catalogBestSimilarity = $bestRow ? (int) $bestRow['similarity'] : null;

        return [
            'final' => [
                'code' => $final->code,
                'cn_name' => $final->cn_name,
                'intro' => $final->intro,
                'description' => $final->description,
            ],
            'match' => [
                'display_similarity' => $usedFallback ? (int) ($catalogBestSimilarity ?? 0) : $bestSimilarity,
                'used_fallback' => $usedFallback,
                'best_standard_code' => $bestRow['type']->code ?? null,
                'best_standard_similarity' => $catalogBestSimilarity,
                'low_match_threshold' => $threshold,
            ],
            'dimensions' => $dimPayload,
            'ranked_preview' => $ranked->take(5)->map(fn ($r) => [
                'code' => $r['type']->code,
                'cn_name' => $r['type']->cn_name,
                'similarity' => $r['similarity'],
                'distance' => $r['distance'],
            ])->values()->all(),
        ];
    }

    private function sumToLevel(int $score): string
    {
        if ($score <= 3) {
            return 'L';
        }
        if ($score === 4) {
            return 'M';
        }

        return 'H';
    }

    private function levelNum(string $level): int
    {
        return match ($level) {
            'L' => 1,
            'M' => 2,
            default => 3,
        };
    }

    /**
     * @return list<int>
     */
    private function parsePatternForLength(string $pattern, int $length): array
    {
        $chars = str_split(str_replace('-', '', strtoupper(trim($pattern))));
        $out = [];
        foreach ($chars as $ch) {
            if ($ch === 'L') {
                $out[] = 1;
            } elseif ($ch === 'M') {
                $out[] = 2;
            } else {
                $out[] = 3;
            }
        }
        while (count($out) < $length) {
            $out[] = 2;
        }
        if (count($out) > $length) {
            $out = array_slice($out, 0, $length);
        }

        return $out;
    }

    private function makeSyntheticFallback(): PersonalityType
    {
        $t = new PersonalityType;
        $t->code = 'UNKNOWN';
        $t->cn_name = '未命名';
        $t->intro = '系统暂未匹配到合适类型';
        $t->description = '请稍后在后台补充兜底类型或调整阈值。';

        return $t;
    }
}
