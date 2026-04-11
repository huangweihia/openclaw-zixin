<?php

namespace App\Filament\Resources\SkinConfigResource\Pages\Concerns;

use App\Support\SkinCssVariables;
use Illuminate\Validation\ValidationException;

trait ManagesSkinCssRepeater
{
    /**
     * @return array<int, array{k: string, v: string}>
     */
    protected static function defaultCssVarRows(): array
    {
        return SkinCssVariables::defaultCssVarRepeaterRows();
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function hydrateCssRepeater(array $data): array
    {
        $vars = $data['css_variables'] ?? [];
        if (! is_array($vars)) {
            $vars = [];
        }
        $rows = [];
        foreach ($vars as $k => $v) {
            if (! is_string($k) || $k === '') {
                continue;
            }
            if ($k === 'gradient-primary' || $k === 'gradient_primary') {
                continue;
            }
            $rows[] = [
                'k' => $k,
                'v' => is_string($v) ? $v : '',
            ];
        }
        if ($rows === []) {
            $rows = self::defaultCssVarRows();
        }
        $data['_css_var_rows'] = $rows;
        unset($data['css_variables']);

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     *
     * @throws ValidationException
     */
    protected function dehydrateCssRepeater(array $data): array
    {
        $rows = $data['_css_var_rows'] ?? [];
        unset($data['_css_var_rows']);
        $out = [];
        if (is_array($rows)) {
            foreach ($rows as $row) {
                $k = trim((string) ($row['k'] ?? ''));
                if ($k === '' || $k === 'gradient-primary' || $k === 'gradient_primary') {
                    continue;
                }
                $out[$k] = trim((string) ($row['v'] ?? ''));
            }
        }
        SkinCssVariables::assertRequiredPresent($out);
        $data['css_variables'] = SkinCssVariables::normalize($out);

        return $data;
    }
}
