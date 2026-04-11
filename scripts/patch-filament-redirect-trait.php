<?php

/**
 * 为所有 Resource 的 Create*/Edit* 页面注入 RedirectsToIndexAfterSave trait。
 * 用法（项目根目录）：php scripts/patch-filament-redirect-trait.php
 */

$base = dirname(__DIR__).DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'Filament'.DIRECTORY_SEPARATOR.'Resources';
$files = array_merge(
    glob($base.'/**/Pages/Edit*.php') ?: [],
    glob($base.'/**/Pages/Create*.php') ?: [],
);

$useLine = 'use App\\Filament\\Resources\\Pages\\Concerns\\RedirectsToIndexAfterSave;';
$traitUse = '    use RedirectsToIndexAfterSave;';

foreach ($files as $path) {
    $c = file_get_contents($path);
    if ($c === false || str_contains($c, 'RedirectsToIndexAfterSave')) {
        continue;
    }
    if (! preg_match('/^namespace\s+([^;]+);/m', $c, $m)) {
        continue;
    }
    // 在 namespace 后插入 use
    if (! str_contains($c, $useLine)) {
        $c = preg_replace('/^(namespace\s+[^;]+;)/m', "$1\n\n".$useLine, $c, 1);
    }
    // 在 class X extends Y { 后插入 trait
    $c = preg_replace(
        '/(class\s+\w+\s+extends\s+[^\s{]+\s*\{)\s*\n/',
        "$1\n".$traitUse."\n\n",
        $c,
        1
    );
    file_put_contents($path, $c);
    echo "patched: $path\n";
}

echo 'done. count='.count($files)."\n";
