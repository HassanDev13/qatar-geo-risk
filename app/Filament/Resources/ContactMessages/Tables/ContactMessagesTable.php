<?php

namespace App\Filament\Resources\ContactMessages\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Table;

use Filament\Tables\Columns\TextColumn;

class ContactMessagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('الاسم')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label('البريد الإلكتروني')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('subject')
                    ->label('الموضوع')
                    ->searchable()
                    ->limit(50),
                TextColumn::make('created_at')
                    ->label('تاريخ الاستلام')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([ // Changed recordActions to actions as per standard Filament v3 but keeping recordActions if that's what was generated... wait
                ViewAction::make(),
                // EditAction removed as typically contact messages are read-only
            ])
            ->bulkActions([ // Changed to bulkActions
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
