{{-- 对齐《23-会员权益管理模块》权益对比表（精简版） --}}
<div class="oc-surface p-6 md:p-8 overflow-x-auto">
    <h2 class="text-lg font-bold oc-heading mb-4">会员权益对比</h2>
    <table class="w-full text-sm border-collapse min-w-[640px]">
        <thead>
            <tr class="border-b oc-border">
                <th class="text-left py-2 pr-4 font-semibold oc-heading">权益项</th>
                <th class="text-center py-2 px-2 font-semibold oc-heading">免费</th>
                <th class="text-center py-2 px-2 font-semibold oc-heading">VIP</th>
                <th class="text-center py-2 px-2 font-semibold oc-heading">SVIP</th>
            </tr>
        </thead>
        <tbody class="oc-muted">
            @foreach ([
                ['内容浏览', '约 70%', '100%', '100%'],
                ['副业案例库', '—', '✓', '✓'],
                ['AI 工具变现地图', '—', '✓', '✓'],
                ['运营 SOP / 付费资源', '—', '✓', '✓'],
                ['无广告体验', '—', '✓', '✓'],
                ['企业微信推送 / 邮件资讯', '—', '✓', '✓'],
                ['定制采集 / 远程协助', '—', '—', '✓'],
                ['自定义订阅', '—', '—', '✓'],
                ['发布额度（参考）', '5 次/月', '20 次/月', '不限'],
            ] as $row)
                <tr class="border-b oc-border">
                    <td class="py-2 pr-4 oc-heading font-medium">{{ $row[0] }}</td>
                    <td class="py-2 text-center">{{ $row[1] }}</td>
                    <td class="py-2 text-center">{{ $row[2] }}</td>
                    <td class="py-2 text-center">{{ $row[3] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <p class="text-xs oc-muted mt-4 mb-0">具体以订单、后台配置及内容「可见性」为准；管理员账号不参与付费体系展示。</p>
</div>
