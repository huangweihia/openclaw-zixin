<?php

namespace App\Filament\Resources;

use App\Models\PremiumResource;
use App\Filament\Resources\MemberPremiumResource\Pages;
use Filament\Forms;
use Filament\Forms\Form;
use App\Filament\Resources\BaseAdminResource;
use Filament\Tables;
use Filament\Tables\Table;
class MemberPremiumResource extends BaseAdminResource
{
    protected static ?string $model = PremiumResource::class;

    protected static ?string $navigationIcon = 'heroicon-o-gift';

    protected static ?string $navigationGroup = '资源与增长';

    protected static ?int $navigationSort = 10;

    protected static ?string $modelLabel = '会员资源';

    protected static ?string $pluralModelLabel = '会员资源';


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
                Forms\Components\TextInput::make('type')->maxLength(65535),
                Forms\Components\Textarea::make('content')->columnSpanFull()->rows(6),
                Forms\Components\TextInput::make('download_link')->maxLength(65535),
                Forms\Components\TextInput::make('extract_code')->maxLength(65535),
                Forms\Components\TextInput::make('original_price')->numeric()->step(0.01),
                Forms\Components\Textarea::make('tags')->columnSpanFull()->helperText('JSON 数组，可手填'),
                Forms\Components\TextInput::make('visibility')->maxLength(65535),
                Forms\Components\TextInput::make('download_count')->numeric(),
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
                Tables\Columns\TextColumn::make('type')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('content')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('download_link')->limit(40)->toggleable(),
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
            'index' => \App\Filament\Resources\MemberPremiumResource\Pages\ListMemberPremiums::route('/'),
            'create' => \App\Filament\Resources\MemberPremiumResource\Pages\CreateMemberPremium::route('/create'),
            'edit' => \App\Filament\Resources\MemberPremiumResource\Pages\EditMemberPremium::route('/{record}/edit'),
        ];
    }
}
