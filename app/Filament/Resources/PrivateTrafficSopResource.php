<?php

namespace App\Filament\Resources;

use App\Models\PrivateTrafficSop;
use App\Filament\Resources\PrivateTrafficSopResource\Pages;
use Filament\Forms;
use Filament\Forms\Form;
use App\Filament\Resources\BaseAdminResource;
use Filament\Tables;
use Filament\Tables\Table;
class PrivateTrafficSopResource extends BaseAdminResource
{
    protected static ?string $model = PrivateTrafficSop::class;

    protected static ?string $navigationIcon = 'heroicon-o-share';

    protected static ?string $navigationGroup = '资源与增长';

    protected static ?int $navigationSort = 30;

    protected static ?string $modelLabel = '私域 SOP';

    protected static ?string $pluralModelLabel = '私域 SOP';


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
                Forms\Components\TextInput::make('slug')->maxLength(65535),
                Forms\Components\Textarea::make('summary')->columnSpanFull()->rows(6),
                Forms\Components\Textarea::make('content')->columnSpanFull()->rows(6),
                Forms\Components\TextInput::make('contact_note')->maxLength(65535),
                Forms\Components\Toggle::make('vip_gate_engagement'),
                Forms\Components\TextInput::make('platform')->maxLength(65535),
                Forms\Components\TextInput::make('type')->maxLength(65535),
                Forms\Components\Textarea::make('checklist')->columnSpanFull()->helperText('JSON 数组，可手填'),
                Forms\Components\Textarea::make('templates')->columnSpanFull()->helperText('JSON 数组，可手填'),
                Forms\Components\Textarea::make('metrics')->columnSpanFull()->helperText('JSON 数组，可手填'),
                Forms\Components\Textarea::make('tools')->columnSpanFull()->helperText('JSON 数组，可手填'),
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
                Tables\Columns\TextColumn::make('title')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('slug')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('summary')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('content')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('contact_note')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('vip_gate_engagement')->limit(40)->toggleable(),
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
            'index' => \App\Filament\Resources\PrivateTrafficSopResource\Pages\ListPrivateTrafficSops::route('/'),
            'create' => \App\Filament\Resources\PrivateTrafficSopResource\Pages\CreatePrivateTrafficSop::route('/create'),
            'edit' => \App\Filament\Resources\PrivateTrafficSopResource\Pages\EditPrivateTrafficSop::route('/{record}/edit'),
        ];
    }
}
