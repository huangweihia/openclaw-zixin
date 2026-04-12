<?php

namespace App\Filament\Resources\SubscriptionResource\RelationManagers;

use App\Filament\Resources\SubscriptionResource;
use App\Models\Subscription;
use App\Support\AdminListSearch;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class SubscriptionsRelationManager extends RelationManager
{
    protected static string $relationship = 'subscriptions';

    protected static ?string $title = '订阅记录';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('plan')->maxLength(65535),
            Forms\Components\TextInput::make('amount')->numeric()->step(0.01),
            Forms\Components\TextInput::make('status')->maxLength(65535),
            Forms\Components\DateTimePicker::make('started_at'),
            Forms\Components\DateTimePicker::make('expires_at'),
            Forms\Components\TextInput::make('payment_id')->numeric(),
            Forms\Components\TextInput::make('payment_method')->maxLength(65535),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('plan')
            ->columns(AdminListSearch::markSearchable(SubscriptionResource::class, Subscription::class, [
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('plan')->limit(32),
                Tables\Columns\TextColumn::make('amount')->sortable(),
                Tables\Columns\TextColumn::make('status')->badge(),
                Tables\Columns\TextColumn::make('started_at')->dateTime()->toggleable(),
                Tables\Columns\TextColumn::make('expires_at')->dateTime()->toggleable(),
                Tables\Columns\TextColumn::make('payment_method')->limit(24)->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ]))
            ->headerActions([])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }
}
