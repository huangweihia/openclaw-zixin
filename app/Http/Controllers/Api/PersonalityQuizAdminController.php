<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PersonalityDimension;
use App\Models\PersonalityQuestion;
use App\Models\PersonalityQuestionOption;
use App\Models\PersonalityQuizSetting;
use App\Models\PersonalityType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PersonalityQuizAdminController extends Controller
{
    public function bootstrap(): JsonResponse
    {
        $dimensions = PersonalityDimension::query()
            ->orderBy('sort_order')
            ->with(['questions' => fn ($q) => $q->orderBy('sort_order')->with(['options' => fn ($o) => $o->orderBy('sort_order')])])
            ->get();

        $types = PersonalityType::query()->orderBy('sort_order')->get();
        $settings = PersonalityQuizSetting::query()->orderBy('key')->get(['key', 'value']);

        return response()->json([
            'dimensions' => $dimensions,
            'types' => $types,
            'settings' => $settings->mapWithKeys(fn ($r) => [$r->key => $r->value])->all(),
            'active_dimension_count' => PersonalityDimension::query()->where('is_active', true)->count(),
        ]);
    }

    public function storeDimension(Request $request): JsonResponse
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:16', 'unique:personality_dimensions,code'],
            'name' => ['required', 'string', 'max:255'],
            'model_group' => ['nullable', 'string', 'max:64'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:65535'],
            'explanation_l' => ['required', 'string'],
            'explanation_m' => ['required', 'string'],
            'explanation_h' => ['required', 'string'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $row = PersonalityDimension::query()->create([
            'code' => $data['code'],
            'name' => $data['name'],
            'model_group' => $data['model_group'] ?? null,
            'sort_order' => $data['sort_order'] ?? 0,
            'explanation_l' => $data['explanation_l'],
            'explanation_m' => $data['explanation_m'],
            'explanation_h' => $data['explanation_h'],
            'is_active' => $data['is_active'] ?? true,
        ]);

        return response()->json($row, 201);
    }

    public function updateDimension(Request $request, PersonalityDimension $dimension): JsonResponse
    {
        $data = $request->validate([
            'code' => ['sometimes', 'string', 'max:16', 'unique:personality_dimensions,code,'.$dimension->id],
            'name' => ['sometimes', 'string', 'max:255'],
            'model_group' => ['nullable', 'string', 'max:64'],
            'sort_order' => ['sometimes', 'integer', 'min:0', 'max:65535'],
            'explanation_l' => ['sometimes', 'string'],
            'explanation_m' => ['sometimes', 'string'],
            'explanation_h' => ['sometimes', 'string'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $dimension->fill($data);
        $dimension->save();

        return response()->json($dimension->fresh());
    }

    public function destroyDimension(PersonalityDimension $dimension): JsonResponse
    {
        $dimension->delete();

        return response()->json(['ok' => true]);
    }

    public function storeQuestion(Request $request): JsonResponse
    {
        $data = $request->validate([
            'personality_dimension_id' => ['required', 'exists:personality_dimensions,id'],
            'body' => ['required', 'string'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:65535'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $row = PersonalityQuestion::query()->create([
            'personality_dimension_id' => $data['personality_dimension_id'],
            'body' => $data['body'],
            'sort_order' => $data['sort_order'] ?? 0,
            'is_active' => $data['is_active'] ?? true,
        ]);

        return response()->json($row, 201);
    }

    public function updateQuestion(Request $request, PersonalityQuestion $question): JsonResponse
    {
        $data = $request->validate([
            'body' => ['sometimes', 'string'],
            'sort_order' => ['sometimes', 'integer', 'min:0', 'max:65535'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $question->fill($data);
        $question->save();

        return response()->json($question->fresh());
    }

    public function destroyQuestion(PersonalityQuestion $question): JsonResponse
    {
        $question->delete();

        return response()->json(['ok' => true]);
    }

    public function storeOption(Request $request): JsonResponse
    {
        $data = $request->validate([
            'personality_question_id' => ['required', 'exists:personality_questions,id'],
            'label' => ['required', 'string', 'max:255'],
            'value' => ['required', 'integer', 'min:1', 'max:9'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:65535'],
        ]);

        $row = PersonalityQuestionOption::query()->create([
            'personality_question_id' => $data['personality_question_id'],
            'label' => $data['label'],
            'value' => $data['value'],
            'sort_order' => $data['sort_order'] ?? 0,
        ]);

        return response()->json($row, 201);
    }

    public function updateOption(Request $request, PersonalityQuestionOption $option): JsonResponse
    {
        $data = $request->validate([
            'label' => ['sometimes', 'string', 'max:255'],
            'value' => ['sometimes', 'integer', 'min:1', 'max:9'],
            'sort_order' => ['sometimes', 'integer', 'min:0', 'max:65535'],
        ]);

        $option->fill($data);
        $option->save();

        return response()->json($option->fresh());
    }

    public function destroyOption(PersonalityQuestionOption $option): JsonResponse
    {
        $option->delete();

        return response()->json(['ok' => true]);
    }

    public function storeType(Request $request): JsonResponse
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:32', 'unique:personality_types,code'],
            'cn_name' => ['required', 'string', 'max:255'],
            'intro' => ['nullable', 'string', 'max:512'],
            'description' => ['nullable', 'string'],
            'image_url' => ['nullable', 'string', 'max:512'],
            'pattern' => ['nullable', 'string', 'max:32'],
            'is_fallback' => ['sometimes', 'boolean'],
            'is_active' => ['sometimes', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:65535'],
        ]);

        $isFallback = (bool) ($data['is_fallback'] ?? false);
        $this->assertPatternValid($data['pattern'] ?? null, $isFallback);
        if ($isFallback) {
            $this->clearOtherFallbacks(null);
        }

        $row = PersonalityType::query()->create([
            'code' => $data['code'],
            'cn_name' => $data['cn_name'],
            'intro' => $data['intro'] ?? null,
            'description' => $data['description'] ?? null,
            'image_url' => $data['image_url'] ?? null,
            'pattern' => $isFallback ? null : $this->normalizePatternString($data['pattern'] ?? null),
            'is_fallback' => $isFallback,
            'is_active' => $data['is_active'] ?? true,
            'sort_order' => $data['sort_order'] ?? 0,
        ]);

        return response()->json($row, 201);
    }

    public function updateType(Request $request, PersonalityType $type): JsonResponse
    {
        $data = $request->validate([
            'code' => ['sometimes', 'string', 'max:32', 'unique:personality_types,code,'.$type->id],
            'cn_name' => ['sometimes', 'string', 'max:255'],
            'intro' => ['nullable', 'string', 'max:512'],
            'description' => ['nullable', 'string'],
            'image_url' => ['nullable', 'string', 'max:512'],
            'pattern' => ['nullable', 'string', 'max:32'],
            'is_fallback' => ['sometimes', 'boolean'],
            'is_active' => ['sometimes', 'boolean'],
            'sort_order' => ['sometimes', 'integer', 'min:0', 'max:65535'],
        ]);

        $isFallback = array_key_exists('is_fallback', $data)
            ? (bool) $data['is_fallback']
            : (bool) $type->is_fallback;

        $pattern = array_key_exists('pattern', $data) ? $data['pattern'] : $type->pattern;
        $this->assertPatternValid($pattern, $isFallback);

        if ($isFallback) {
            $this->clearOtherFallbacks($type->id);
        }

        if (array_key_exists('is_fallback', $data) || array_key_exists('pattern', $data)) {
            $type->is_fallback = $isFallback;
            $type->pattern = $isFallback ? null : $this->normalizePatternString($pattern);
        }

        $type->fill(collect($data)->except(['pattern', 'is_fallback'])->all());
        $type->save();

        return response()->json($type->fresh());
    }

    public function destroyType(PersonalityType $type): JsonResponse
    {
        $type->delete();

        return response()->json(['ok' => true]);
    }

    public function putSetting(Request $request): JsonResponse
    {
        $data = $request->validate([
            'key' => ['required', 'string', 'max:64'],
            'value' => ['nullable', 'string'],
        ]);

        PersonalityQuizSetting::setValue($data['key'], $data['value']);

        return response()->json([
            'key' => $data['key'],
            'value' => $data['value'],
        ]);
    }

    private function activeDimensionCount(): int
    {
        return (int) PersonalityDimension::query()->where('is_active', true)->count();
    }

    private function normalizePatternString(?string $pattern): ?string
    {
        if ($pattern === null) {
            return null;
        }
        $s = strtoupper(str_replace(['-', ' '], '', trim($pattern)));

        return $s === '' ? null : $s;
    }

    /**
     * @throws ValidationException
     */
    private function assertPatternValid(?string $pattern, bool $isFallback): void
    {
        if ($isFallback) {
            return;
        }

        $n = $this->activeDimensionCount();
        if ($n === 0) {
            return;
        }

        $norm = $this->normalizePatternString($pattern);
        if ($norm === null || $norm === '') {
            throw ValidationException::withMessages([
                'pattern' => ['非兜底类型必须填写 pattern，且长度需等于当前启用维度数（'.$n.'）。'],
            ]);
        }

        if (strlen($norm) !== $n) {
            throw ValidationException::withMessages([
                'pattern' => ['pattern 去掉横线/空格后的长度必须为 '.$n.'（当前为 '.strlen($norm).'）。'],
            ]);
        }

        if (! preg_match('/^[LMH]+$/', $norm)) {
            throw ValidationException::withMessages([
                'pattern' => ['pattern 只能包含 L、M、H（可加横线分隔）。'],
            ]);
        }
    }

    private function clearOtherFallbacks(?int $exceptId): void
    {
        $q = PersonalityType::query()->where('is_fallback', true);
        if ($exceptId !== null) {
            $q->where('id', '!=', $exceptId);
        }
        $q->update(['is_fallback' => false]);
    }
}
