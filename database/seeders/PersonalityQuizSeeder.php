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
        DB::transaction(function () {
            PersonalityQuizSetting::query()->updateOrCreate(
                ['key' => 'low_match_threshold'],
                ['value' => '60']
            );
            PersonalityQuizSetting::query()->updateOrCreate(
                ['key' => 'enabled'],
                ['value' => '1']
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
                    $dim = PersonalityDimension::query()->firstOrCreate(
                        ['code' => $meta['code']],
                        [
                            'name' => $meta['name'],
                            'model_group' => $groupName,
                            'sort_order' => $sort,
                            'explanation_l' => $meta['l'],
                            'explanation_m' => $meta['m'],
                            'explanation_h' => $meta['h'],
                            'is_active' => true,
                        ]
                    );
                    $dim->fill([
                        'name' => $meta['name'],
                        'model_group' => $groupName,
                        'sort_order' => $sort,
                        'explanation_l' => $meta['l'],
                        'explanation_m' => $meta['m'],
                        'explanation_h' => $meta['h'],
                        'is_active' => true,
                    ])->save();

                    $questionBodies = [
                        '凌晨两点你突然复盘人生，在「'.$dim->name.'」上你更像哪种选手？',
                        '朋友群里开始“高强度发疯”，你在「'.$dim->name.'」上的默认姿势是：',
                        '项目开会进入“谁都不说重点”模式时，你在「'.$dim->name.'」上的反应更接近：',
                        '周一早上闹钟响第三遍，你在「'.$dim->name.'」上的内心 OS 更像：',
                    ];
                    $optionSets = [
                        [
                            ['我主打一个反着来', 1],
                            ['看心情随机触发', 2],
                            ['这就是我的出厂设置', 3],
                        ],
                        [
                            ['先观望，能苟就苟', 1],
                            ['先试探，再决定', 2],
                            ['直接上，别拦我', 3],
                        ],
                        [
                            ['像在说隔壁工位', 1],
                            ['像昨天的我', 2],
                            ['像每天都在直播我', 3],
                        ],
                        [
                            ['理智告诉我别这样', 1],
                            ['理智和本能打平手', 2],
                            ['本能已经接管全场', 3],
                        ],
                    ];

                    foreach ($questionBodies as $qi => $body) {
                        $q = PersonalityQuestion::query()->updateOrCreate(
                            [
                                'personality_dimension_id' => $dim->id,
                                'sort_order' => $qi + 1,
                            ],
                            [
                                'body' => $body,
                                'is_active' => true,
                            ]
                        );
                        $opts = $optionSets[$qi] ?? $optionSets[0];
                        foreach ($opts as $os => $pair) {
                            PersonalityQuestionOption::query()->updateOrCreate(
                                [
                                    'personality_question_id' => $q->id,
                                    'sort_order' => $os + 1,
                                ],
                                [
                                    'label' => $pair[0],
                                    'value' => $pair[1],
                                ]
                            );
                        }
                    }
                }
            }

            $types = [
                ['code' => 'PLANNER', 'cn_name' => '战术参谋型', 'intro' => '还没开始做，脑内已经彩排三轮。', 'pattern' => 'HHHHHHHHHHHHHHH', 'sort' => 10, 'img' => 'https://picsum.photos/seed/oc-planner/1200/630'],
                ['code' => 'EXPLORER', 'cn_name' => '野路子探索型', 'intro' => '先冲再说，路是走着走着歪出来的。', 'pattern' => 'LLLLLLLLLLLLLLL', 'sort' => 20, 'img' => 'https://picsum.photos/seed/oc-explorer/1200/630'],
                ['code' => 'BALANCE', 'cn_name' => '端水大师型', 'intro' => '情绪、效率、社交，主打一个不翻车。', 'pattern' => 'MMMMMMMMMMMMMMM', 'sort' => 30, 'img' => 'https://picsum.photos/seed/oc-balance/1200/630'],
                ['code' => 'WAVE', 'cn_name' => '间歇性开挂型', 'intro' => '状态好的时候像开挂，状态差的时候像在加载。', 'pattern' => 'HHLHHLHHLHHLHHL', 'sort' => 40, 'img' => 'https://picsum.photos/seed/oc-wave/1200/630'],
                ['code' => 'GUARD', 'cn_name' => '谨慎防守型', 'intro' => '先把风险打包，再谈梦想发货。', 'pattern' => 'LLHHMMHHLLMMHHH', 'sort' => 50, 'img' => 'https://picsum.photos/seed/oc-guard/1200/630'],
                ['code' => 'RUSH', 'cn_name' => '火力推进型', 'intro' => '想到就干，先把进度条拽到 80%。', 'pattern' => 'HHMMLLHHMMLLHHM', 'sort' => 60, 'img' => 'https://picsum.photos/seed/oc-rush/1200/630'],
                ['code' => 'MIXED', 'cn_name' => '抽象混合态', 'intro' => '你不是矛盾，你是复杂且有点好笑。', 'pattern' => null, 'sort' => 90, 'fallback' => true, 'img' => 'https://picsum.photos/seed/oc-mixed/1200/630'],
            ];

            foreach ($types as $t) {
                PersonalityType::query()->updateOrCreate(
                    ['code' => $t['code']],
                    [
                        'cn_name' => $t['cn_name'],
                        'intro' => $t['intro'],
                        'description' => '这是搞怪风格示例解读：你在不同场景下可能像“冷静策士”也可能像“热血莽夫”。别焦虑，这只是娱乐测试，笑完继续搬砖。',
                        'image_url' => $t['img'] ?? null,
                        'pattern' => $t['pattern'],
                        'is_fallback' => ! empty($t['fallback']),
                        'is_active' => true,
                        'sort_order' => $t['sort'],
                    ]
                );
            }
        });
    }
}
