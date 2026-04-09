<div id="oc-report-modal" class="oc-modal-overlay hidden" role="dialog" aria-modal="true" aria-labelledby="oc-report-title">
    <div class="oc-modal">
        <h3 id="oc-report-title" class="text-lg font-bold mb-3 oc-heading">举报评论</h3>
        <label class="oc-label" for="oc-report-reason">原因</label>
        <select id="oc-report-reason" class="oc-input mb-3">
            <option value="spam">广告垃圾</option>
            <option value="abuse">辱骂</option>
            <option value="harassment">骚扰</option>
            <option value="other">其他</option>
        </select>
        <label class="oc-label" for="oc-report-desc">补充说明（选填，最多 500 字）</label>
        <textarea id="oc-report-desc" class="oc-input mb-4" rows="3" maxlength="500" placeholder="可补充具体情况，便于审核"></textarea>
        <div class="flex gap-2 justify-end">
            <button type="button" class="btn btn-secondary text-sm" id="oc-report-cancel">取消</button>
            <button type="button" class="btn btn-primary text-sm" id="oc-report-submit">提交举报</button>
        </div>
    </div>
</div>
