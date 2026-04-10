<?php

namespace Database\Seeders;

use App\Models\PersonalityDimension;
use App\Models\PersonalityQuestion;
use App\Models\PersonalityQuestionOption;
use App\Models\PersonalityQuizSetting;
use App\Models\PersonalityType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PersonalityQuizSeeder extends Seeder
{
    public function run(): void
    {
        if (PersonalityDimension::query()->exists()) {
            return;
        }

        DB::transaction(function () {
            PersonalityQuizSetting::query()->updateOrCreate(
                ['key' => 'low_match_threshold'],
                ['value' => '60']
            );

            $groups = [
                '自我视角' => [
                    ['code' => 'D01', 'name' => '自我评价', 'l' => '更容易看到自己的短板。', 'm' => '好坏都能接受，随状态波动。', 'h' => '对自己整体比较有把握。'],
                    ['code' => 'D02', 'name' => '自我一致性', 'l' => '偶尔说不清“我到底想要什么”。', 'm' => '大方向清楚，细节会摇摆。', 'h' => '对自己的偏好和底线较清晰。'],
                    ['code' => 'D03', 'name' => '进取与松弛', 'l' => '更在意当下舒服，少给自己加压。', 'm' => '想努力也想休息，经常拉扯。', 'h' => '容易被目标感驱动，想持续进步。'],
                ],
                '关系与情感' => [
                    ['code' => 'D04', 'name' => '关系安全感', 'l' => '小事也可能触发不安或联想。', 'm' => '会观察信号，但多数能自洽。', 'h' => '更愿意先相信对方的善意。'],
                    ['code' => 'D05', 'name' => '投入深度', 'l' => '投入前会反复衡量风险。', 'm' => '看对象与阶段，深浅不一。', 'h' => '一旦认真就会给足精力与时间。'],
                    ['code' => 'D06', 'name' => '亲密与独立', 'l' => '喜欢黏一点、热一点的互动。', 'm' => '需要亲密也需要透气。', 'h' => '再亲密也要保留个人空间。'],
                ],
                '态度与意义' => [
                    ['code' => 'D07', 'name' => '对人性的预设', 'l' => '默认多留一个心眼。', 'm' => '看场合，不轻信也不极端。', 'h' => '愿意先给信任与善意。'],
                    ['code' => 'D08', 'name' => '规则与灵活', 'l' => '规则太死会想绕路或变通。', 'm' => '该守守，该放放。', 'h' => '流程和边界让人安心。'],
                    ['code' => 'D09', 'name' => '目标感', 'l' => '经常觉得“做也行不做也行”。', 'm' => '有时鸡血，有时放空。', 'h' => '做事习惯带目标与路径。'],
                ],
                '行动方式' => [
                    ['code' => 'D10', 'name' => '动机取向', 'l' => '先求稳，少惹麻烦。', 'm' => '看收益也看成本。', 'h' => '更在意推进与结果。'],
                    ['code' => 'D11', 'name' => '决策速度', 'l' => '容易多想几轮再定。', 'm' => '一般节奏，偶尔拖。', 'h' => '定了就往前走，少回头。'],
                    ['code' => 'D12', 'name' => '执行风格', 'l' => '截止线附近效率最高。', 'm' => '看心情与外部压力。', 'h' => '不喜欢事情悬而未决。'],
                ],
                '社交表达' => [
                    ['code' => 'D13', 'name' => '社交启动', 'l' => '更被动，等别人开口。', 'm' => '熟了就自然。', 'h' => '愿意主动破冰与组局。'],
                    ['code' => 'D14', 'name' => '人际边界', 'l' => '熟了容易把人划进“自己人”。', 'm' => '分人分场景调节距离。', 'h' => '边界清晰，靠太近会警觉。'],
                    ['code' => 'D15', 'name' => '表达真实度', 'l' => '心里想什么更容易直说。', 'm' => '看气氛选择性表达。', 'h' => '更会在不同场合调整呈现方式。'],
                ],
            ];

            $sort = 0;
            foreach ($groups as $groupName => $items) {
                foreach ($items as $meta) {
                    $sort++;
                    $dim = PersonalityDimension::query()->create([
                        'code' => $meta['code'],
                        'name' => $meta['name'],
                        'model_group' => $groupName,
                        'sort_order' => $sort,
                        'explanation_l' => $meta['l'],
                        'explanation_m' => $meta['m'],
                        'explanation_h' => $meta['h'],
                        'is_active' => true,
                    ]);

                    foreach ([1, 2] as $qi) {
                        $q = PersonalityQuestion::query()->create([
                            'personality_dimension_id' => $dim->id,
                            'body' => $dim->name.' · 情景 '.$qi.'：以下哪句更像你？',
                            'sort_order' => $qi,
                            'is_active' => true,
                        ]);
                        $opts = [
                            ['不太像', 1],
                            ['有点像', 2],
                            ['很像我', 3],
                        ];
                        $os = 0;
                        foreach ($opts as [$label, $val]) {
                            $os++;
                            PersonalityQuestionOption::query()->create([
                                'personality_question_id' => $q->id,
                                'label' => $label,
                                'value' => $val,
                                'sort_order' => $os,
                            ]);
                        }
                    }
                }
            }

            $types = [
                ['code' => 'PLANNER', 'cn_name' => '规划型', 'intro' => '先把路线画清楚，再出发。', 'pattern' => 'HHHHHHHHHHHHHHH', 'sort' => 10],
                ['code' => 'EXPLORER', 'cn_name' => '探索型', 'intro' => '世界很大，先走走看。', 'pattern' => 'LLLLLLLLLLLLLLL', 'sort' => 20],
                ['code' => 'BALANCE', 'cn_name' => '平衡型', 'intro' => '不高不低，刚刚好。', 'pattern' => 'MMMMMMMMMMMMMMM', 'sort' => 30],
                ['code' => 'WAVE', 'cn_name' => '起伏型', 'intro' => '高低搭配，像心电图。', 'pattern' => 'HHLHHLHHLHHLHHL', 'sort' => 40],
                ['code' => 'GUARD', 'cn_name' => '守势型', 'intro' => '先站稳，再谈别的。', 'pattern' => 'LLHHMMHHLLMMHHH', 'sort' => 50],
                ['code' => 'RUSH', 'cn_name' => '推进型', 'intro' => '动起来，问题会变小。', 'pattern' => 'HHMMLLHHMMLLHHM', 'sort' => 60],
                ['code' => 'MIXED', 'cn_name' => '混合态', 'intro' => '标准答案配不上你。', 'pattern' => null, 'sort' => 90, 'fallback' => true],
            ];

            foreach ($types as $t) {
                PersonalityType::query()->create([
                    'code' => $t['code'],
                    'cn_name' => $t['cn_name'],
                    'intro' => $t['intro'],
                    'description' => '这是站点内置的示例解读，可在后台改成自己的文案。维度得分仅用于娱乐向展示。',
                    'pattern' => $t['pattern'],
                    'is_fallback' => ! empty($t['fallback']),
                    'is_active' => true,
                    'sort_order' => $t['sort'],
                ]);
            }
        });
    }
}
