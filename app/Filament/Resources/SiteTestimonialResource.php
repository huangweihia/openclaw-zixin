<?php

namespace App\Filament\Resources;

use App\Models\SiteTestimonial;
use App\Filament\Resources\SiteTestimonialResource\Pages;
use Filament\Forms;
use Filament\Forms\Form;
use App\Filament\Resources\BaseAdminResource;
use Filament\Tables;
use Filament\Tables\Table;
class SiteTestimonialResource extends BaseAdminResource
{
    protected static ?string $model = SiteTestimonial::class;

    protected static ?string $navigationIcon = 'heroicon-o-star';

    protected static ?string $navigationGroup = '站点与外观';

    protected static ?int $navigationSort = 20;

    protected static ?string $modelLabel = '首页评价';

    protected static ?string $pluralModelLabel = '首页评价';


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
            Forms\Components\TextInput::make('display_name')
                ->label('显示名')
                ->maxLength(65535),
            Forms\Components\TextInput::make('caption')
                ->label('副标题/说明标签')
                ->maxLength(65535),
            Forms\Components\Textarea::make('body')
                ->label('题干/正文')
                ->columnSpanFull()
                ->rows(6),
            Forms\Components\TextInput::make('rating')
                ->label('星级评分')
                ->numeric()
                ->minValue(1)
                ->maxValue(5),
            Forms\Components\TextInput::make('avatar_initial')
                ->label('头像首字')
                ->maxLength(8),
            Forms\Components\TextInput::make('gradient_from')
                ->label('渐变起始（Tailwind 类名）')
                ->maxLength(65535)
                ->helperText('例如 from-blue-400'),
            Forms\Components\TextInput::make('gradient_to')
                ->label('渐变结束（Tailwind 类名）')
                ->maxLength(65535)
                ->helperText('例如 to-blue-600'),
            Forms\Components\TextInput::make('sort_order')
                ->label('排序')
                ->numeric(),
            Forms\Components\Toggle::make('is_published')
                ->label('已发布'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns(static::searchableColumns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('display_name')->label('显示名')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('caption')->label('副标题/说明')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('body')->label('正文摘要')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('rating')->label('星级')->toggleable(),
                Tables\Columns\TextColumn::make('avatar_initial')->label('头像首字')->toggleable(),
                Tables\Columns\TextColumn::make('gradient_from')->label('渐变起始')->limit(24)->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')->label('创建时间')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
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
            'index' => \App\Filament\Resources\SiteTestimonialResource\Pages\ListSiteTestimonials::route('/'),
            'create' => \App\Filament\Resources\SiteTestimonialResource\Pages\CreateSiteTestimonial::route('/create'),
            'edit' => \App\Filament\Resources\SiteTestimonialResource\Pages\EditSiteTestimonial::route('/{record}/edit'),
        ];
    }
}
