<?php

namespace App\Support;

use App\Models\AdminResourceSearchConfig;
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
        if ($names === []) {
            return $columns;
        }

        return array_map(function ($column) use ($names) {
            if (! $column instanceof TextColumn) {
                return $column;
            }
            $n = $column->getName();
            if (! in_array($n, $names, true)) {
                return $column;
            }

            return $column->searchable();
        }, $columns);
    }

    /**
     * @return list<string>
     */
    public static function resolvedSearchColumnNames(string $resourceFqcn, string $modelFqcn): array
    {
        if (Schema::hasTable('admin_resource_search_configs')) {
            $row = AdminResourceSearchConfig::query()->where('resource_class', $resourceFqcn)->first();
            if ($row !== null && is_array($row->search_column_names) && $row->search_column_names !== []) {
                return array_values(array_filter(array_map('strval', $row->search_column_names)));
            }
        }

        return self::inferFromSchema($modelFqcn);
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
}
