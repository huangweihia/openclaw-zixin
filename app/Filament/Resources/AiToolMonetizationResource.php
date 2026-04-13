<?php

namespace App\Filament\Resources;

use App\Models\AiToolMonetization;
use App\Filament\Resources\AiToolMonetizationResource\Pages;
use Filament\Forms;
use Filament\Forms\Form;
use App\Filament\Resources\BaseAdminResource;
use Filament\Tables;
use Filament\Tables\Table;
class AiToolMonetizationResource extends BaseAdminResource
{
    protected static ?string $model = AiToolMonetization::class;

    protected static ?string $navigationIcon = 'heroicon-o-cpu-chip';

    protected static ?string $navigationGroup = '资源与增长';

    protected static ?int $navigationSort = 40;

    protected static ?string $modelLabel = 'AI 工具变现';

    protected static ?string $pluralModelLabel = 'AI 工具变现';


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
            Forms\Components\TextInput::make('tool_name')->maxLength(65535),
                Forms\Components\TextInput::make('slug')->maxLength(65535),
                Forms\Components\TextInput::make('tool_url')->maxLength(65535),
                Forms\Components\TextInput::make('category')->maxLength(65535),
                Forms\Components\Toggle::make('available_in_china'),
                Forms\Components\TextInput::make('pricing_model')->maxLength(65535),
                Forms\Components\Textarea::make('content')->columnSpanFull()->rows(6),
                Forms\Components\Textarea::make('monetization_scenes')->columnSpanFull()->helperText('JSON 数组，可手填'),
                Forms\Components\Textarea::make('prompt_templates')->columnSpanFull()->helperText('JSON 数组，可手填'),
                Forms\Components\Textarea::make('pricing_reference')->columnSpanFull()->helperText('JSON 数组，可手填'),
                Forms\Components\Textarea::make('channels')->columnSpanFull()->helperText('JSON 数组，可手填'),
                Forms\Components\Textarea::make('delivery_standards')->columnSpanFull()->helperText('JSON 数组，可手填'),
                Forms\Components\TextInput::make('visibility')->maxLength(65535),
                Forms\Components\TextInput::make('view_count')->numeric(),
                Forms\Components\TextInput::make('like_count')->numeric(),
                Forms\Components\TextInput::make('favorite_count')->numeric()
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns(static::searchableColumns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('tool_name')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('slug')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('tool_url')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('category')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('available_in_china')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('pricing_model')->limit(40)->toggleable(),
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
            'index' => \App\Filament\Resources\AiToolMonetizationResource\Pages\ListAiToolMonetizations::route('/'),
            'create' => \App\Filament\Resources\AiToolMonetizationResource\Pages\CreateAiToolMonetization::route('/create'),
            'edit' => \App\Filament\Resources\AiToolMonetizationResource\Pages\EditAiToolMonetization::route('/{record}/edit'),
        ];
    }
}
