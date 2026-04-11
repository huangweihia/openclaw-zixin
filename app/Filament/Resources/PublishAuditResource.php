<?php

namespace App\Filament\Resources;

use App\Models\PublishAudit;
use App\Filament\Resources\PublishAuditResource\Pages;
use Filament\Forms;
use Filament\Forms\Form;
use App\Filament\Resources\BaseAdminResource;
use Filament\Tables;
use Filament\Tables\Table;
class PublishAuditResource extends BaseAdminResource
{
    protected static ?string $model = PublishAudit::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $navigationGroup = '内容与社区';

    protected static ?int $navigationSort = 70;

    protected static ?string $modelLabel = '发布审计';

    protected static ?string $pluralModelLabel = '发布审计';


    public static function canCreate(): bool
    {
        return static::canViewAny() && false;
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
            Forms\Components\TextInput::make('publish_id')->numeric(),
                Forms\Components\TextInput::make('user_id')->numeric(),
                Forms\Components\TextInput::make('auditor_id')->numeric(),
                Forms\Components\TextInput::make('status')->maxLength(65535),
                Forms\Components\TextInput::make('reject_reason')->maxLength(65535),
                Forms\Components\TextInput::make('suggest')->maxLength(65535),
                Forms\Components\TextInput::make('priority')->numeric(),
                Forms\Components\DateTimePicker::make('audited_at')
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('publish_id')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('user_id')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('auditor_id')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('status')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('reject_reason')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('suggest')->limit(40)->toggleable(),
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
            'index' => \App\Filament\Resources\PublishAuditResource\Pages\ListPublishAudits::route('/'),
            'edit' => \App\Filament\Resources\PublishAuditResource\Pages\EditPublishAudit::route('/{record}/edit'),
        ];
    }
}
