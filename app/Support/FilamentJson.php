<?php

namespace App\Support;

final class FilamentJson
{
    /**
     * @param  mixed  $state
     */
    public static function pretty($state): string
    {
        if ($state === null || $state === '') {
            return '—';
        }
        if (is_array($state)) {
            return json_encode($state, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        }
        $s = (string) $state;
        $decoded = json_decode($s, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return json_encode($decoded, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        }

        return $s;
    }
}
