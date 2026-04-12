<?php

namespace App\Filament\Resources;

use App\Models\SideHustleCase;
use App\Filament\Resources\SideHustleCaseResource\Pages;
use Filament\Forms;
use Filament\Forms\Form;
use App\Filament\Resources\BaseAdminResource;
use Filament\Tables;
use Filament\Tables\Table;
class SideHustleCaseResource extends BaseAdminResource
{
    protected static ?string $model = SideHustleCase::class;

    protected static ?string $navigationIcon = 'heroicon-o-briefcase';

    protected static ?string $navigationGroup = '资源与增长';

    protected static ?int $navigationSort = 20;

    protected static ?string $modelLabel = '副业案例';

    protected static ?string $pluralModelLabel = '副业案例';


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
                Forms\Components\TextInput::make('category')->maxLength(65535),
                Forms\Components\TextInput::make('type')->maxLength(65535),
                Forms\Components\TextInput::make('startup_cost')->maxLength(65535),
                Forms\Components\TextInput::make('time_investment')->maxLength(65535),
                Forms\Components\TextInput::make('resource_type')->maxLength(65535),
                Forms\Components\TextInput::make('resource_url')->maxLength(65535),
                Forms\Components\TextInput::make('estimated_income')->numeric()->step(0.01),
                Forms\Components\TextInput::make('actual_income')->numeric()->step(0.01),
                Forms\Components\Textarea::make('income_screenshots')->columnSpanFull()->helperText('JSON 数组，可手填'),
                Forms\Components\TextInput::make('steps')->maxLength(65535),
                Forms\Components\Textarea::make('tools')->columnSpanFull()->helperText('JSON 数组，可手填'),
                Forms\Components\Textarea::make('pitfalls')->columnSpanFull()->helperText('JSON 数组，可手填'),
                Forms\Components\Toggle::make('willing_to_consult'),
                Forms\Components\TextInput::make('contact_info')->maxLength(65535),
                Forms\Components\TextInput::make('visibility')->maxLength(65535),
                Forms\Components\TextInput::make('status')->maxLength(65535),
                Forms\Components\Textarea::make('audit_note')->columnSpanFull()->rows(6),
                Forms\Components\Select::make('audited_by')
                    ->relationship('auditor', 'name')
                    ->searchable()
                    ->preload()
                    ->nullable(),
                Forms\Components\DateTimePicker::make('audited_at'),
                Forms\Components\TextInput::make('view_count')->numeric(),
                Forms\Components\TextInput::make('like_count')->numeric(),
                Forms\Components\TextInput::make('comment_count')->numeric(),
                Forms\Components\TextInput::make('favorite_count')->numeric(),
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('title')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('slug')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('summary')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('content')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('category')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('type')->limit(40)->toggleable(),
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
            'index' => \App\Filament\Resources\SideHustleCaseResource\Pages\ListSideHustleCases::route('/'),
            'create' => \App\Filament\Resources\SideHustleCaseResource\Pages\CreateSideHustleCase::route('/create'),
            'edit' => \App\Filament\Resources\SideHustleCaseResource\Pages\EditSideHustleCase::route('/{record}/edit'),
        ];
    }
}
