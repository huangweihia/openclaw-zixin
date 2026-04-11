<?php

namespace App\Filament\Resources;

use App\Models\EmailTemplate;
use App\Filament\Resources\EmailTemplateResource\Pages;
use Filament\Forms;
use Filament\Forms\Form;
use App\Filament\Resources\BaseAdminResource;
use Filament\Tables;
use Filament\Tables\Table;
class EmailTemplateResource extends BaseAdminResource
{
    protected static ?string $model = EmailTemplate::class;

    protected static ?string $navigationIcon = 'heroicon-o-envelope';

    protected static ?string $navigationGroup = '营销与触达';

    protected static ?int $navigationSort = 40;

    protected static ?string $modelLabel = '邮件模板';

    protected static ?string $pluralModelLabel = '邮件模板';


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
                Forms\Components\TextInput::make('subject')->maxLength(65535),
                Forms\Components\Textarea::make('content')->columnSpanFull()->rows(6),
                Forms\Components\TextInput::make('plain_text')->maxLength(65535),
                Forms\Components\Textarea::make('variables')->columnSpanFull()->helperText('JSON 数组，可手填'),
                Forms\Components\Toggle::make('is_active')
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('name')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('key')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('subject')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('content')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('plain_text')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('variables')->limit(40)->toggleable(),
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
            'index' => \App\Filament\Resources\EmailTemplateResource\Pages\ListEmailTemplates::route('/'),
            'create' => \App\Filament\Resources\EmailTemplateResource\Pages\CreateEmailTemplate::route('/create'),
            'edit' => \App\Filament\Resources\EmailTemplateResource\Pages\EditEmailTemplate::route('/{record}/edit'),
        ];
    }
}
