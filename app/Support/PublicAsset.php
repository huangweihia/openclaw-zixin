<?php

namespace App\Support;

class PublicAsset
{
    /**
     * Public URL with filemtime query (cache bust when file changes).
     */
    public static function url(string $path): string
    {
        $path = ltrim($path, '/');
        $base = asset($path);

        if (config('openclaw.public_asset_use_filemtime', false)) {
            $full = public_path($path);
            $v = is_file($full) ? filemtime($full) : 0;

            return $base.'?v='.$v;
        }

        $v = config('openclaw.public_asset_version', '1');

        return $base.'?v='.rawurlencode((string) $v);
    }
}
