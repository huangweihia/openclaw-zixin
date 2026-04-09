<?php

namespace App\Services;

use App\Models\AdSlot;
use Illuminate\Support\Facades\Auth;
/**
 * 仅保留广告位（AdSlot）兜底素材渲染：
 * - 不再依赖广告投放（advertisements）与点击/曝光统计表
 * - 展示尺寸与位置完全以广告位配置为准
 */
class AdPresentationService
{
    /**
     * 按广告位 code 解析当前应展示的素材或兜底。
     *
     * @return array<string, mixed>|null
     */
    public function resolve(string $slotCode): ?array
    {
        $slot = AdSlot::query()
            ->where('code', $slotCode)
            ->where('is_active', true)
            ->first();

        if (! $slot) {
            return null;
        }

        if (! $this->canViewSlot($slot)) {
            return null;
        }

        return $this->resolveSlot($slot);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function resolveSlot(AdSlot $slot): ?array
    {
        if (! $this->canViewSlot($slot)) {
            return null;
        }

        // 仅使用广告位兜底素材（图片/标题/链接/HTML）
        if ($this->slotHasDefaultCreative($slot)) {
            return [
                'kind' => 'default',
                'slot' => $slot,
                'floating' => $slot->type === 'float',
            ];
        }

        return null;
    }

    /**
     * 兜底素材：任一则可展示（不要求必须填写跳转链接，便于仅展示二维码图等）。
     */
    private function slotHasDefaultCreative(AdSlot $slot): bool
    {
        $s = static fn ($v) => is_string($v) && trim($v) !== '';

        return $s((string) ($slot->default_title ?? ''))
            || $s((string) ($slot->default_image_url ?? ''))
            || $s((string) ($slot->default_link_url ?? ''))
            || $s((string) ($slot->default_content ?? ''));
    }

    /**
     * 侧栏：按 position=left|right 取首个有素材的广告位（不含「浮动」类广告位）。
     *
     * @return array<string, mixed>|null
     */
    public function resolveFirstBySlotPosition(string $position): ?array
    {
        $slots = AdSlot::query()
            ->where('position', $position)
            ->where('is_active', true)
            ->where('type', '!=', 'float')
            ->orderByDesc('sort')
            ->get();

        foreach ($slots as $slot) {
            $pack = $this->resolveSlot($slot);
            if ($pack !== null) {
                return $pack;
            }
        }

        return null;
    }

    /**
     * 所有「广告位类型 = float」的版位各自解析一条兜底素材，用于右下角等浮动区。
     *
     * @return list<array<string, mixed>>
     */
    public function resolveFloatSlotPacks(): array
    {
        $slots = AdSlot::query()
            ->where('is_active', true)
            ->where(function ($q) {
                $q->where('type', 'float')
                    ->orWhereIn('position', ['left', 'right']);
            })
            ->orderByDesc('sort')
            ->orderBy('id')
            ->limit(5)
            ->get();

        $out = [];
        foreach ($slots as $slot) {
            $pack = $this->resolveSlot($slot);
            if ($pack !== null) {
                $out[] = $pack;
            }
        }

        return $out;
    }

    private function canViewSlot(AdSlot $slot): bool
    {
        $audience = strtolower(trim((string) ($slot->audience ?? 'all')));
        if ($audience === '') {
            $audience = 'all';
        }

        $user = Auth::user();
        $role = strtolower((string) ($user->role ?? ''));
        $isGuest = $user === null;
        $isMember = in_array($role, ['vip', 'svip', 'admin'], true);

        return match ($audience) {
            'all' => true,
            'guest' => $isGuest,
            'user' => ! $isGuest,
            'vip' => $role === 'vip' || $role === 'admin',
            'svip' => $role === 'svip' || $role === 'admin',
            'admin' => $role === 'admin',
            'member' => $isMember,
            'non_member' => ! $isMember,
            default => true,
        };
    }
}
