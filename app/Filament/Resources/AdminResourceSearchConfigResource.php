<?php

namespace App\Filament\Resources;

use App\Models\AdminResourceSearchConfig;
use App\Filament\Resources\AdminResourceSearchConfigResource\Pages;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\File;

class AdminResourceSearchConfigResource extends BaseAdminResource
{
    protected static ?string $model = AdminResourceSearchConfig::class;

    protected static ?string $navigationIcon = 'heroicon-o-magnifying-glass';

    protected static ?string $navigationGroup = '系统';

    protected static ?int $navigationSort = 200;

    protected static ?string $modelLabel = '列表搜索条件';

    protected static ?string $pluralModelLabel = '列表搜索条件';

    public static function canCreate(): bool
    {
        return static::canViewAny();
    }

    public static function canEdit($record): bool
    {
        return static::canViewAny();
    }

    public static function canDelete($record): bool
    {
        return static::canViewAny();
    }

    /**
     * @return array<string, string>
     */
    public static function filamentResourceClassOptions(): array
    {
        $paths = File::glob(app_path('Filament/Resources/*Resource.php')) ?: [];
        $out = [];
        foreach ($paths as $path) {
            $base = basename($path, '.php');
            if ($base === 'BaseAdminResource' || $base === 'AdminResourceSearchConfigResource') {
                continue;
            }
            $fqcn = 'App\\Filament\\Resources\\'.$base;
            if (class_exists($fqcn)) {
                $out[$fqcn] = $base;
            }
        }
        ksort($out);

        return $out;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('resource_class')
                ->label('Filament 资源类名')
                ->required()
                ->searchable()
                ->options(fn () => static::filamentResourceClassOptions())
                ->disabledOn('edit'),
            Forms\Components\TagsInput::make('search_column_names')
                ->label('参与全局搜索的列名')
                ->placeholder('留空则按该资源对应数据表字段自动推断（字符串/日期类列）')
                ->helperText('支持主表字段名，如 name、email；关联列如 user.name 需在推断之外手动在此填写。')
                ->nullable(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns(static::searchableColumns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('resource_class')
                    ->label('资源类')
                    ->limit(48)
                    ->searchable()
                    ->wrap(),
                Tables\Columns\TextColumn::make('search_column_names')
                    ->label('已配置列')
                    ->formatStateUsing(function (?array $state): string {
                        if ($state === null || $state === []) {
                            return '（自动推断）';
                        }

                        return implode(', ', $state);
                    })
                    ->wrap(),
                Tables\Columns\TextColumn::make('updated_at')->dateTime()->sortable(),
            ]))
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAdminResourceSearchConfigs::route('/'),
            'create' => Pages\CreateAdminResourceSearchConfig::route('/create'),
            'edit' => Pages\EditAdminResourceSearchConfig::route('/{record}/edit'),
        ];
    }
}
