<?php

namespace App\Filament\Resources;

use App\Models\EmailSetting;
use App\Filament\Resources\EmailSettingResource\Pages;
use Filament\Forms;
use Filament\Forms\Form;
use App\Filament\Resources\BaseAdminResource;
use Filament\Tables;
use Filament\Tables\Table;
class EmailSettingResource extends BaseAdminResource
{
    protected static ?string $model = EmailSetting::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationGroup = '营销与触达';

    protected static ?int $navigationSort = 70;

    protected static ?string $modelLabel = '邮件配置';

    protected static ?string $pluralModelLabel = '邮件配置';


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
                Forms\Components\TextInput::make('key')->maxLength(65535),
                Forms\Components\TextInput::make('value')->maxLength(65535),
                Forms\Components\Textarea::make('description')->columnSpanFull()->rows(6)
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns(static::searchableColumns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('name')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('key')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('value')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('description')->limit(40)->toggleable(),
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
            'index' => \App\Filament\Resources\EmailSettingResource\Pages\ListEmailSettings::route('/'),
            'create' => \App\Filament\Resources\EmailSettingResource\Pages\CreateEmailSetting::route('/create'),
            'edit' => \App\Filament\Resources\EmailSettingResource\Pages\EditEmailSetting::route('/{record}/edit'),
        ];
    }
}
