<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PersonalityQuizPlay;
use App\Services\PersonalityQuizScoringService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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
        $payload['guest_play'] = $this->guestPlayMeta($request);

        return response()->json($payload);
    }

    public function submit(Request $request): JsonResponse
    {
        $baseRules = [
            'answers' => ['required', 'array'],
            'answers.*' => ['required', 'integer', 'min:1', 'max:9'],
        ];

        if ($request->user()) {
            $data = $request->validate($baseRules);

            return $this->completeSubmitForMember($data['answers']);
        }

        $data = $request->validate($baseRules + [
            'guest_token' => ['required', 'uuid'],
        ]);

        return $this->completeSubmitForGuest($data['guest_token'], $data['answers']);
    }

    /**
     * @param  array<int|string, int>  $answers
     */
    private function completeSubmitForMember(array $answers): JsonResponse
    {
        try {
            $result = $this->scoring->score($answers);
        } catch (InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json($result);
    }

    /**
     * @param  array<int|string, int>  $answers
     */
    private function completeSubmitForGuest(string $guestToken, array $answers): JsonResponse
    {
        try {
            return DB::transaction(function () use ($guestToken, $answers) {
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
                    $result = $this->scoring->score($answers);
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
}
