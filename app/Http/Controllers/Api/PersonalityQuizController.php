<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PersonalityQuizPlay;
use App\Services\PersonalityQuizScoringService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

class PersonalityQuizController extends Controller
{
    public function __construct(
        private readonly PersonalityQuizScoringService $scoring
    ) {}

    public function show(Request $request): JsonResponse
    {
        $payload = $this->scoring->buildPublicPayload();
        $picked = $this->pickRandomQuestions($payload);
        $questionIds = array_values(array_map(static fn ($q) => (int) $q['id'], $picked));
        $quizToken = (string) Str::uuid();
        Cache::put($this->quizCacheKey($quizToken), $questionIds, now()->addMinutes(30));

        $payload['quiz_token'] = $quizToken;
        $payload['questions'] = $picked;
        $payload['guest_play'] = $this->guestPlayMeta($request);

        return response()->json($payload);
    }

    public function submit(Request $request): JsonResponse
    {
        $baseRules = [
            'answers' => ['required', 'array'],
            'answers.*' => ['required', 'integer', 'min:1', 'max:9'],
            'quiz_token' => ['required', 'uuid'],
        ];

        if ($request->user()) {
            $data = $request->validate($baseRules);

            return $this->completeSubmitForMember($data['answers'], $data['quiz_token']);
        }

        $data = $request->validate($baseRules + [
            'guest_token' => ['required', 'uuid'],
        ]);

        return $this->completeSubmitForGuest($data['guest_token'], $data['answers'], $data['quiz_token']);
    }

    /**
     * @param  array<int|string, int>  $answers
     */
    private function completeSubmitForMember(array $answers, string $quizToken): JsonResponse
    {
        $questionIds = $this->loadQuestionIdsByToken($quizToken);
        if ($questionIds === null) {
            return response()->json(['message' => '题目会话已过期，请重新开始测试。'], 422);
        }

        try {
            $result = $this->scoring->scoreByQuestionIds($answers, $questionIds);
        } catch (InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json($result);
    }

    /**
     * @param  array<int|string, int>  $answers
     */
    private function completeSubmitForGuest(string $guestToken, array $answers, string $quizToken): JsonResponse
    {
        $questionIds = $this->loadQuestionIdsByToken($quizToken);
        if ($questionIds === null) {
            return response()->json(['message' => '题目会话已过期，请重新开始测试。'], 422);
        }

        try {
            return DB::transaction(function () use ($guestToken, $answers, $questionIds) {
                $exists = PersonalityQuizPlay::query()
                    ->where('guest_token', $guestToken)
                    ->lockForUpdate()
                    ->exists();

                if ($exists) {
                    return response()->json([
                        'message' => '每位游客仅可完整体验一次，注册账号后可再次参与。',
                        'code' => 'guest_limit_reached',
                        'register_url' => route('register'),
                    ], 403);
                }

                try {
                    $result = $this->scoring->scoreByQuestionIds($answers, $questionIds);
                } catch (InvalidArgumentException $e) {
                    return response()->json(['message' => $e->getMessage()], 422);
                }

                PersonalityQuizPlay::query()->create([
                    'guest_token' => $guestToken,
                    'user_id' => null,
                    'completed_at' => now(),
                ]);

                return response()->json($result);
            });
        } catch (\Throwable $e) {
            report($e);

            return response()->json(['message' => '提交失败，请稍后重试。'], 500);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function guestPlayMeta(Request $request): array
    {
        $registerUrl = route('register');

        if ($request->user()) {
            return [
                'mode' => 'member',
                'can_play' => true,
                'register_url' => $registerUrl,
            ];
        }

        $token = $request->query('guest_token');
        if (! is_string($token) || ! Str::isUuid($token)) {
            return [
                'mode' => 'guest',
                'can_play' => false,
                'reason' => 'invalid_guest_token',
                'message' => '缺少有效的游客标识，请刷新页面重试。',
                'register_url' => $registerUrl,
            ];
        }

        $used = PersonalityQuizPlay::query()->where('guest_token', $token)->exists();

        return [
            'mode' => 'guest',
            'can_play' => ! $used,
            'reason' => $used ? 'guest_limit_reached' : null,
            'message' => $used ? '每位游客仅可完整体验一次，注册账号后可再次参与。' : null,
            'register_url' => $registerUrl,
        ];
    }

    private function quizCacheKey(string $quizToken): string
    {
        return 'personality_quiz:quiz:'.$quizToken;
    }

    /**
     * @return list<int>|null
     */
    private function loadQuestionIdsByToken(string $quizToken): ?array
    {
        $ids = Cache::pull($this->quizCacheKey($quizToken));
        if (! is_array($ids) || $ids === []) {
            return null;
        }

        return array_values(array_map('intval', $ids));
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return list<array<string, mixed>>
     */
    private function pickRandomQuestions(array $payload): array
    {
        $questions = collect($payload['questions'] ?? []);
        $dims = collect($payload['dimensions'] ?? [])->pluck('id')->map(fn ($v) => (int) $v)->all();
        $picked = [];
        foreach ($dims as $dimId) {
            $bag = $questions->where('dimension_id', $dimId)->values();
            if ($bag->isEmpty()) {
                continue;
            }
            $take = min(2, $bag->count());
            $sample = $bag->shuffle()->take($take)->sortBy('id')->values()->all();
            foreach ($sample as $row) {
                $picked[] = $row;
            }
        }

        return array_values($picked);
    }
}
