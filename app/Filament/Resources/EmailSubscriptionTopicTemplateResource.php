<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EmailSubscriptionTopicTemplateResource\Pages;
use App\Filament\Resources\BaseAdminResource;
use App\Models\EmailSubscriptionTopicTemplate;
use App\Models\EmailTemplate;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;

class EmailSubscriptionTopicTemplateResource extends BaseAdminResource
{
    protected static ?string $model = EmailSubscriptionTopicTemplate::class;

    protected static ?string $navigationIcon = 'heroicon-o-link';

    protected static ?string $navigationGroup = '营销与触达';

    protected static ?int $navigationSort = 44;

    protected static ?string $modelLabel = '订阅邮件模板映射';

    protected static ?string $pluralModelLabel = '订阅邮件模板映射';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('topic_key')
                ->label('订阅场景')
                ->options(EmailSubscriptionTopicTemplate::TOPIC_LABELS)
                ->required()
                ->disabled(fn (?EmailSubscriptionTopicTemplate $record) => $record !== null),
            Forms\Components\Select::make('template_key')
                ->label('邮件模板（email_templates.key）')
                ->options(fn () => EmailTemplate::query()
                    ->where('is_active', true)
                    ->orderBy('name')
                    ->pluck('name', 'key')
                    ->all())
                ->searchable()
                ->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns(static::searchableColumns([
                Tables\Columns\TextColumn::make('topic_key')
                    ->label('场景键')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('topic_label')
                    ->label('说明')
                    ->getStateUsing(fn (EmailSubscriptionTopicTemplate $record): string => EmailSubscriptionTopicTemplate::TOPIC_LABELS[$record->topic_key] ?? $record->topic_key),
                Tables\Columns\TextColumn::make('template_key')
                    ->label('模板 key')
                    ->searchable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('更新于')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('topic_key')
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
            'index' => Pages\ListEmailSubscriptionTopicTemplates::route('/'),
            'create' => Pages\CreateEmailSubscriptionTopicTemplate::route('/create'),
            'edit' => Pages\EditEmailSubscriptionTopicTemplate::route('/{record}/edit'),
        ];
    }
}
