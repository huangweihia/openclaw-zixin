<?php

namespace App\Filament\Resources;

use App\Models\PersonalityQuizPlay;
use App\Filament\Resources\PersonalityQuizPlayResource\Pages;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms;
use Filament\Forms\Form;
use App\Filament\Resources\BaseAdminResource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class PersonalityQuizPlayResource extends BaseAdminResource
{
    protected static ?string $model = PersonalityQuizPlay::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationGroup = '人格测试';

    protected static ?int $navigationSort = 60;

    protected static ?string $modelLabel = '测评记录';

    protected static ?string $pluralModelLabel = '测评记录';

    public static function canCreate(): bool
    {
        return static::canViewAny() && false;
    }

    public static function canEdit($record): bool
    {
        return static::canViewAny() && false;
    }

    public static function canDelete($record): bool
    {
        return static::canViewAny() && false;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['user']);
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('guest_token')->maxLength(65535),
            Forms\Components\Select::make('user_id')
                ->relationship('user', 'name')
                ->searchable()
                ->preload()
                ->nullable(),
            Forms\Components\DateTimePicker::make('completed_at'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns(static::searchableColumns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('guest_token')
                    ->label('游客标识')
                    ->limit(20)
                    ->copyable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('登录用户')
                    ->searchable()
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('completed_at')->dateTime()->sortable()->toggleable(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('completed_at', 'desc')
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Infolists\Components\Section::make('测评会话')
                ->icon('heroicon-o-sparkles')
                ->columns(2)
                ->schema([
                    Infolists\Components\TextEntry::make('id')->label('记录 ID'),
                    Infolists\Components\TextEntry::make('guest_token')
                        ->label('游客 Token')
                        ->copyable()
                        ->placeholder('—')
                        ->columnSpanFull(),
                    Infolists\Components\TextEntry::make('user.name')
                        ->label('登录用户')
                        ->placeholder('游客或未登录'),
                    Infolists\Components\TextEntry::make('completed_at')
                        ->label('完成时间')
                        ->dateTime(),
                ]),
            Infolists\Components\Section::make('系统记录')
                ->icon('heroicon-o-clock')
                ->collapsed()
                ->columns(2)
                ->schema([
                    Infolists\Components\TextEntry::make('created_at')->label('创建时间')->dateTime(),
                    Infolists\Components\TextEntry::make('updated_at')->label('更新时间')->dateTime(),
                ]),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\PersonalityQuizPlayResource\Pages\ListPersonalityQuizPlays::route('/'),
            'view' => \App\Filament\Resources\PersonalityQuizPlayResource\Pages\ViewPersonalityQuizPlay::route('/{record}'),
        ];
    }
}
