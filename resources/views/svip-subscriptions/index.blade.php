@extends('layouts.site')

@section('title', 'SVIP 定制订阅 — OpenClaw 智信')

@section('content')
    <p class="mb-4">
        <a href="{{ route('dashboard') }}" class="oc-link text-sm font-medium" style="text-decoration: none;">← 返回个人中心</a>
    </p>
    <h1 class="text-2xl md:text-3xl font-bold mb-6 oc-heading">SVIP 定制订阅</h1>
    <p class="text-sm oc-muted mb-4 max-w-2xl leading-relaxed">
        提交后，运营会结合 <strong>OpenClaw 数据交付能力</strong>与您确认订阅范围与推送节奏；实际计费与合约以双方约定为准，本页<strong>不收集固定金额与天数</strong>。
    </p>
    <p class="text-xs oc-muted mb-8 max-w-2xl leading-relaxed">
        <strong>说明：</strong>「私域运营 SOP」是站内可浏览的内容模块（与 VIP 权益相关）；SVIP 定制订阅指<strong>按约定周期接收情报/数据摘要</strong>的服务，两者概念不同。
    </p>

    <div class="oc-surface p-6 mb-8 max-w-2xl">
        <h2 class="text-lg font-bold oc-heading mb-4">新建需求</h2>
        <form method="post" action="{{ route('svip-subscriptions.store') }}" class="space-y-4">
            @csrf
            <div class="oc-field">
                <label class="oc-label" for="plan_name">订阅主题 / 方案名称</label>
                <input type="text" name="plan_name" id="plan_name" value="{{ old('plan_name') }}" required maxlength="255" class="oc-input" placeholder="例如：行业周报 + 竞品监控摘要" />
            </div>
            <div class="oc-field">
                <label class="oc-label" for="delivery_frequency">推送频率</label>
                <select name="delivery_frequency" id="delivery_frequency" class="oc-input">
                    @php $df = old('delivery_frequency'); @endphp
                    <option value="">请选择</option>
                    <option value="daily" @selected($df === 'daily')>每日</option>
                    <option value="workdays" @selected($df === 'workdays')>工作日</option>
                    <option value="weekly" @selected($df === 'weekly')>每周</option>
                    <option value="biweekly" @selected($df === 'biweekly')>双周</option>
                    <option value="monthly" @selected($df === 'monthly')>每月</option>
                    <option value="on_demand" @selected($df === 'on_demand')>按事件 / 按需</option>
                </select>
            </div>
            <div class="oc-field">
                <label class="oc-label" for="preferred_send_time">期望发送时间（本地）</label>
                <input type="time" name="preferred_send_time" id="preferred_send_time" value="{{ old('preferred_send_time') }}" class="oc-input" />
                <p class="text-xs oc-muted m-0 mt-1">例如每日推送希望几点送达（运营会再确认时区与可行性）。</p>
            </div>
            <div class="oc-field">
                <label class="oc-label" for="delivery_channel">接收渠道</label>
                <select name="delivery_channel" id="delivery_channel" class="oc-input">
                    @php $dc = old('delivery_channel'); @endphp
                    <option value="">请选择</option>
                    <option value="email" @selected($dc === 'email')>邮箱</option>
                    <option value="wecom" @selected($dc === 'wecom')>企业微信</option>
                    <option value="email_wecom" @selected($dc === 'email_wecom')>邮箱 + 企微</option>
                </select>
            </div>
            <div class="oc-field">
                <label class="oc-label" for="description">需求与交付说明（选填）</label>
                <textarea name="description" id="description" rows="5" maxlength="4000" class="oc-input" placeholder="希望覆盖的数据范围、补充说明等">{{ old('description') }}</textarea>
            </div>
            @if ($errors->any())
                <div class="oc-flash oc-flash--error text-sm" role="alert">
                    <ul class="m-0 pl-4">
                        @foreach ($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <button type="submit" class="btn btn-primary text-sm">提交需求</button>
        </form>
    </div>

    <div class="oc-surface p-6 max-w-4xl">
        <h2 class="text-lg font-bold oc-heading mb-4">我的记录</h2>
        @if ($items->isEmpty())
            <p class="text-sm oc-muted m-0">暂无记录。</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left border-collapse">
                    <thead>
                        <tr class="border-b oc-border">
                            <th class="py-2 pr-4 font-semibold oc-heading">方案</th>
                            <th class="py-2 pr-4 font-semibold oc-heading">频率 / 时间</th>
                            <th class="py-2 pr-4 font-semibold oc-heading">渠道</th>
                            <th class="py-2 pr-4 font-semibold oc-heading">预算(旧)</th>
                            <th class="py-2 pr-4 font-semibold oc-heading">状态</th>
                            <th class="py-2 pr-4 font-semibold oc-heading"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($items as $row)
                            <tr class="border-b oc-border align-top">
                                <td class="py-3 pr-4">
                                    <div class="font-medium oc-heading">{{ $row->plan_name }}</div>
                                    @if ($row->description)
                                        <div class="text-xs oc-muted mt-1 line-clamp-2">{{ $row->description }}</div>
                                    @endif
                                </td>
                                <td class="py-3 pr-4 text-xs oc-muted">
                                    {{ $row->delivery_frequency ?: '—' }}
                                    @if ($row->preferred_send_time)
                                        <span class="block mt-1">⏰ {{ $row->preferred_send_time }}</span>
                                    @endif
                                </td>
                                <td class="py-3 pr-4 text-xs oc-muted">{{ $row->delivery_channel ?: '—' }}</td>
                                <td class="py-3 pr-4 whitespace-nowrap text-sm oc-muted">
                                    @if ((float) $row->amount > 0)
                                        {{ number_format((float) $row->amount, 2) }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="py-3 pr-4">{{ $row->status }}</td>
                                <td class="py-3 pr-0">
                                    @if ($row->status === 'pending')
                                        <form method="post" action="{{ route('svip-subscriptions.destroy', $row) }}" class="inline" onsubmit="return confirm('确定删除该条待处理记录？');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-secondary text-xs">删除</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-6">{{ $items->links() }}</div>
        @endif
    </div>
@endsection
