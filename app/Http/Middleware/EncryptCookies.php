<?php

namespace App\Http\Middleware;

use Illuminate\Cookie\Middleware\EncryptCookies as Middleware;

class EncryptCookies extends Middleware
{
    /**
     * The names of the cookies that should not be encrypted.
     *
     * @var array<int, string>
     */
    protected $except = [
        // Livewire / Filament 用 fetch 发请求时，前端需能读出明文 token 写入 X-XSRF-TOKEN；加密后读不到 → 419
        'XSRF-TOKEN',
    ];
}
