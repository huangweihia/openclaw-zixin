<?php

namespace App\Support;

use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

final class AdminListSearch
{
    /**
     * @param  class-string  $resourceFqcn
     * @param  class-string  $modelFqcn
     * @param  array<int, \Filament\Tables\Columns\Column>  $columns
     * @return array<int, \Filament\Tables\Columns\Column>
     */
    public static function markSearchable(string $resourceFqcn, string $modelFqcn, array $columns): array
    {
        $names = self::resolvedSearchColumnNames($resourceFqcn, $modelFqcn);
        $tableCols = self::tableColumns($modelFqcn);

        return array_map(function ($column) use ($names, $tableCols) {
            if (! $column instanceof TextColumn) {
                return $column;
            }

            $n = $column->getName();
            if ($names !== []) {
                if (in_array($n, $names, true)) {
                    return $column->searchable();
                }

                return $column;
            }

            // 无手工配置时：默认自动开启
            // 1) 主表真实列名（避免 getStateUsing 的虚拟字段）
            // 2) 关系列（如 user.name）
            if (str_contains($n, '.') || in_array($n, $tableCols, true)) {
                return $column->searchable();
            }

            return $column;
        }, $columns);
    }

    /**
     * @return list<string>
     */
    public static function resolvedSearchColumnNames(string $resourceFqcn, string $modelFqcn): array
    {
        // 统一自动模式：不再要求“列表搜索条件”手工配置。
        return [];
    }

    /**
     * @param  class-string  $modelFqcn
     * @return list<string>
     */
    public static function inferFromSchema(string $modelFqcn): array
    {
        if (! class_exists($modelFqcn) || ! is_subclass_of($modelFqcn, Model::class)) {
            return [];
        }

        /** @var Model $instance */
        $instance = new $modelFqcn;
        $table = $instance->getTable();
        if (! Schema::hasTable($table)) {
            return [];
        }

        $out = [];
        foreach (Schema::getColumnListing($table) as $col) {
            if (in_array($col, ['password', 'remember_token'], true)) {
                continue;
            }
            try {
                $type = Schema::getColumnType($table, $col);
            } catch (\Throwable) {
                continue;
            }
            if (self::isSearchableColumnType((string) $type)) {
                $out[] = $col;
            }
        }

        return array_values(array_unique($out));
    }

    private static function isSearchableColumnType(string $type): bool
    {
        if (str_contains($type, 'text') || str_contains($type, 'char') || str_contains($type, 'string')) {
            return true;
        }

        return in_array($type, ['datetime', 'date', 'time'], true);
    }

    /**
     * @param class-string $modelFqcn
     * @return list<string>
     */
    private static function tableColumns(string $modelFqcn): array
    {
        if (! class_exists($modelFqcn) || ! is_subclass_of($modelFqcn, Model::class)) {
            return [];
        }

        /** @var Model $instance */
        $instance = new $modelFqcn;
        $table = $instance->getTable();
        if (! Schema::hasTable($table)) {
            return [];
        }

        return array_values(Schema::getColumnListing($table));
    }
}
