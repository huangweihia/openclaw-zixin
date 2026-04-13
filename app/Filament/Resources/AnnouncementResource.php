<?php

namespace App\Filament\Resources;

use App\Models\Announcement;
use App\Filament\Resources\AnnouncementResource\Pages;
use Filament\Forms;
use Filament\Forms\Form;
use App\Filament\Resources\BaseAdminResource;
use Filament\Tables;
use Filament\Tables\Table;
class AnnouncementResource extends BaseAdminResource
{
    protected static ?string $model = Announcement::class;

    protected static ?string $navigationIcon = 'heroicon-o-megaphone';

    protected static ?string $navigationGroup = '营销与触达';

    protected static ?int $navigationSort = 10;

    protected static ?string $modelLabel = '公告';

    protected static ?string $pluralModelLabel = '公告';


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
            Forms\Components\TextInput::make('title')->maxLength(65535),
                Forms\Components\Textarea::make('content')->columnSpanFull()->rows(6),
                Forms\Components\TextInput::make('priority')->numeric(),
                Forms\Components\TextInput::make('display_position')->maxLength(65535),
                Forms\Components\Toggle::make('is_floating'),
                Forms\Components\TextInput::make('cover_image')->maxLength(65535),
                Forms\Components\TextInput::make('float_width')->numeric(),
                Forms\Components\TextInput::make('float_height')->numeric(),
                Forms\Components\Toggle::make('is_published'),
                Forms\Components\DateTimePicker::make('expires_at'),
                Forms\Components\TextInput::make('created_by')->maxLength(65535)
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns(static::searchableColumns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('title')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('content')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('priority')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('display_position')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('is_floating')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('cover_image')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true)
            ]))
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
            'index' => \App\Filament\Resources\AnnouncementResource\Pages\ListAnnouncements::route('/'),
            'create' => \App\Filament\Resources\AnnouncementResource\Pages\CreateAnnouncement::route('/create'),
            'edit' => \App\Filament\Resources\AnnouncementResource\Pages\EditAnnouncement::route('/{record}/edit'),
        ];
    }
}
