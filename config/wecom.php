<?php

return [

    /*
    |--------------------------------------------------------------------------
    | 企业微信自建应用（用于 OAuth 与消息推送）
    |--------------------------------------------------------------------------
    |
    | 在企微管理后台创建「自建应用」，配置可信域名与 OAuth 回调 URL，
    | 将 corpid、agentid、secret 填入 .env。文档：
    | https://developer.work.weixin.qq.com/document/path/91022
    |
    */

    'corp_id' => env('WECOM_CORP_ID', ''),

    'agent_id' => env('WECOM_AGENT_ID', ''),

    'agent_secret' => env('WECOM_AGENT_SECRET', ''),

    /**
     * OAuth 授权后回调完整 URL（须与企微后台「网页授权及 JS-SDK」中配置一致）。
     * 未设置时默认使用 route('wecom.oauth.callback') 的绝对地址。
     */
    'oauth_redirect_uri' => env('WECOM_OAUTH_REDIRECT_URI', ''),

];
