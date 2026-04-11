<?php

namespace App\Filament\Resources;

use App\Models\SkinConfig;
use App\Filament\Resources\SkinConfigResource\Pages;
use Filament\Forms;
use Filament\Forms\Form;
use App\Filament\Resources\BaseAdminResource;
use Filament\Tables;
use Filament\Tables\Table;
class SkinConfigResource extends BaseAdminResource
{
    protected static ?string $model = SkinConfig::class;

    protected static ?string $navigationIcon = 'heroicon-o-swatch';

    protected static ?string $navigationGroup = '站点与外观';

    protected static ?int $navigationSort = 30;

    protected static ?string $modelLabel = '皮肤配置';

    protected static ?string $pluralModelLabel = '皮肤配置';


    public static function canCreate(): bool
    {
        return static::canViewAny() && true;
    }

    public static function canEdit($record): bool
    {
        return static::canViewAny() && true;
    }

    public static function canDelete($record): bool
    {
        return static::canViewAny() && true;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')->required()->maxLength(100),
            Forms\Components\TextInput::make('code')
                ->maxLength(128)
                ->disabled()
                ->dehydrated()
                ->visibleOn('edit')
                ->helperText('创建时由系统根据名称生成。'),
            Forms\Components\TextInput::make('owner_user_id')->numeric()->nullable(),
            Forms\Components\Textarea::make('description')->columnSpanFull()->rows(4)->maxLength(500),
            Forms\Components\TextInput::make('preview_image')->maxLength(255)->nullable(),
            Forms\Components\Repeater::make('_css_var_rows')
                ->label('主题色与 CSS 变量')
                ->helperText('必填变量：primary、secondary、bg-primary、text-primary。gradient-primary 保存时由主色/辅色自动生成。')
                ->schema([
                    Forms\Components\TextInput::make('k')
                        ->required()
                        ->maxLength(64)
                        ->helperText('如 primary，对应前台 --primary'),
                    Forms\Components\ColorPicker::make('v')
                        ->required()
                        ->hex(),
                ])
                ->columns(2)
                ->addActionLabel('添加变量')
                ->reorderable()
                ->collapsible()
                ->columnSpanFull(),
            Forms\Components\Select::make('type')
                ->options([
                    'free' => '免费',
                    'vip' => 'VIP',
                    'svip' => 'SVIP',
                ])
                ->required()
                ->native(false),
            Forms\Components\Toggle::make('is_private'),
            Forms\Components\TextInput::make('custom_source')->maxLength(64)->nullable(),
            Forms\Components\TextInput::make('sort')->numeric()->default(0),
            Forms\Components\Toggle::make('is_active')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('name')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('code')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('owner_user_id')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('description')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('preview_image')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('css_variables')
                    ->label('主题变量预览')
                    ->formatStateUsing(function ($state): string {
                        if (! is_array($state)) {
                            return '';
                        }
                        $parts = [];
                        foreach (array_slice($state, 0, 6, true) as $k => $v) {
                            if (is_string($v) && strlen($v) > 28) {
                                $v = substr($v, 0, 28).'…';
                            }
                            $parts[] = $k.': '.(is_string($v) ? $v : json_encode($v));
                        }

                        return implode('；', $parts);
                    })
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }
    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\SkinConfigResource\Pages\ListSkinConfigs::route('/'),
            'create' => \App\Filament\Resources\SkinConfigResource\Pages\CreateSkinConfig::route('/create'),
            'edit' => \App\Filament\Resources\SkinConfigResource\Pages\EditSkinConfig::route('/{record}/edit'),
        ];
    }
}
