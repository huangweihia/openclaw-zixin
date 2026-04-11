<?php

namespace App\Support;

use Filament\Forms;
use Filament\Tables;
use Illuminate\Support\Facades\Schema;

/**
 * 根据 users 表列自动生成 Filament 表单/表格字段，减少「迁库后忘改 Resource」的手工维护。
 * 敏感列永不展示；常用列仍由 UserResource 手写以保证交互与校验。
 */
final class FilamentUserSchema
{
    public const TABLE = 'users';

    /** 已在 UserResource 中手写表单的列（勿自动生成） */
    public const FORM_MANUAL_COLUMNS = [
        'name',
        'email',
        'password',
        'avatar',
        'bio',
        'enterprise_wechat_id',
        'privacy_mode',
        'role',
        'is_banned',
        'subscription_ends_at',
        'points_balance',
    ];

    /** 永不进入自动生成表单 */
    public const FORM_SKIP_COLUMNS = [
        'id',
        'remember_token',
        'created_at',
        'updated_at',
        // 未列入 User::$fillable 前不要自动生成可写控件，避免「能改不能存」
        'email_verified_at',
    ];

    /** 已在 UserResource 中手写表格的列 */
    public const TABLE_MANUAL_COLUMNS = [
        'id',
        'name',
        'email',
        'avatar',
        'role',
        'is_banned',
        'points_balance',
        'subscription_ends_at',
        'last_login_at',
        'bio',
        'enterprise_wechat_id',
        'created_at',
    ];

    /** 永不出现在表格中 */
    public const TABLE_SKIP_COLUMNS = [
        'password',
        'remember_token',
    ];

    /**
     * @return array<int, Forms\Components\Component>
     */
    public static function autoFormComponents(): array
    {
        if (! Schema::hasTable(self::TABLE)) {
            return [];
        }

        $names = Schema::getColumnListing(self::TABLE);
        $manual = array_flip(self::FORM_MANUAL_COLUMNS);
        $skip = array_flip(self::FORM_SKIP_COLUMNS);
        $fields = [];

        foreach ($names as $column) {
            if (isset($manual[$column]) || isset($skip[$column])) {
                continue;
            }
            $fields[] = self::guessFormField($column);
        }

        if ($fields === []) {
            return [];
        }

        return [
            Forms\Components\Section::make('扩展字段（自动生成）')
                ->description('来自 users 表、且未在上文手写的列；执行迁移新增字段后会自动出现在此区域。')
                ->schema($fields)
                ->columns(2)
                ->collapsible()
                ->collapsed(),
        ];
    }

    /**
     * @return array<int, Tables\Columns\Column>
     */
    public static function autoTableColumns(): array
    {
        if (! Schema::hasTable(self::TABLE)) {
            return [];
        }

        $names = Schema::getColumnListing(self::TABLE);
        $manual = array_flip(self::TABLE_MANUAL_COLUMNS);
        $skip = array_flip(self::TABLE_SKIP_COLUMNS);
        $columns = [];

        $rest = [];
        foreach ($names as $column) {
            if (isset($manual[$column]) || isset($skip[$column])) {
                continue;
            }
            $rest[] = $column;
        }
        sort($rest);

        foreach ($rest as $column) {
            $columns[] = self::guessTableColumn($column);
        }

        return $columns;
    }

    private static function guessFormField(string $column): Forms\Components\Component
    {
        if (str_starts_with($column, 'is_')) {
            return Forms\Components\Toggle::make($column);
        }

        if (str_ends_with($column, '_at')) {
            return Forms\Components\DateTimePicker::make($column)->nullable();
        }

        if (str_contains($column, '_url')
            || str_ends_with($column, '_link')
            || $column === 'avatar_url') {
            return Forms\Components\TextInput::make($column)->url()->maxLength(2048)->nullable();
        }

        if (str_ends_with($column, '_json') || $column === 'meta' || $column === 'settings') {
            return Forms\Components\Textarea::make($column)->rows(4)->columnSpanFull()->nullable();
        }

        return Forms\Components\TextInput::make($column)->maxLength(65535)->nullable();
    }

    private static function guessTableColumn(string $column): Tables\Columns\Column
    {
        if (str_starts_with($column, 'is_')) {
            return Tables\Columns\IconColumn::make($column)
                ->boolean()
                ->toggleable(isToggledHiddenByDefault: true);
        }

        if (str_ends_with($column, '_at')) {
            return Tables\Columns\TextColumn::make($column)
                ->dateTime()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true);
        }

        if ($column === 'avatar' || (str_contains($column, 'avatar') && str_contains($column, 'url'))) {
            return Tables\Columns\ImageColumn::make($column)
                ->height(32)
                ->toggleable(isToggledHiddenByDefault: true);
        }

        $text = Tables\Columns\TextColumn::make($column)
            ->limit(32)
            ->toggleable(isToggledHiddenByDefault: true)
            ->searchable(false);

        if (str_contains($column, 'openid') || str_contains($column, 'unionid')) {
            $text->copyable();
        }

        return $text;
    }
}
