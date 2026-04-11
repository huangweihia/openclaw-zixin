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
            Forms\Components\TextInput::make('display_name')->maxLength(65535),
                Forms\Components\TextInput::make('caption')->maxLength(65535),
                Forms\Components\TextInput::make('body')->maxLength(65535),
                Forms\Components\TextInput::make('rating')->numeric(),
                Forms\Components\TextInput::make('avatar_initial')->maxLength(65535),
                Forms\Components\TextInput::make('gradient_from')->maxLength(65535),
                Forms\Components\TextInput::make('gradient_to')->maxLength(65535),
                Forms\Components\TextInput::make('sort_order')->numeric(),
                Forms\Components\Toggle::make('is_published')
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('display_name')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('caption')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('body')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('rating')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('avatar_initial')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('gradient_from')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true)
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
