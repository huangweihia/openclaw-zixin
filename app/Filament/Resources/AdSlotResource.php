<?php

namespace App\Filament\Resources;

use App\Models\AdSlot;
use App\Filament\Resources\AdSlotResource\Pages;
use Filament\Forms;
use Filament\Forms\Form;
use App\Filament\Resources\BaseAdminResource;
use Filament\Tables;
use Filament\Tables\Table;
class AdSlotResource extends BaseAdminResource
{
    protected static ?string $model = AdSlot::class;

    protected static ?string $navigationIcon = 'heroicon-o-photo';

    protected static ?string $navigationGroup = '站点与外观';

    protected static ?int $navigationSort = 50;

    protected static ?string $modelLabel = '广告位';

    protected static ?string $pluralModelLabel = '广告位';


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
            Forms\Components\TextInput::make('name')->maxLength(65535),
                Forms\Components\TextInput::make('code')->maxLength(65535),
                Forms\Components\TextInput::make('position')->maxLength(65535),
                Forms\Components\TextInput::make('type')->maxLength(65535),
                Forms\Components\TextInput::make('width')->maxLength(65535),
                Forms\Components\TextInput::make('height')->maxLength(65535),
                Forms\Components\Toggle::make('is_active'),
                Forms\Components\TextInput::make('sort')->numeric(),
                Forms\Components\TextInput::make('default_title')->maxLength(65535),
                Forms\Components\TextInput::make('default_image_url')->maxLength(65535),
                Forms\Components\TextInput::make('default_link_url')->maxLength(65535),
                Forms\Components\Textarea::make('default_content')->columnSpanFull()->rows(6),
                Forms\Components\Toggle::make('show_default_when_empty'),
                Forms\Components\TextInput::make('audience')->maxLength(65535)
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('name')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('code')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('position')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('type')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('width')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('height')->limit(40)->toggleable(),
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
            'index' => \App\Filament\Resources\AdSlotResource\Pages\ListAdSlots::route('/'),
            'create' => \App\Filament\Resources\AdSlotResource\Pages\CreateAdSlot::route('/create'),
            'edit' => \App\Filament\Resources\AdSlotResource\Pages\EditAdSlot::route('/{record}/edit'),
        ];
    }
}
