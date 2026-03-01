<?php

namespace App\Filament\Resources\ContactMessages\Schemas;

use Filament\Schemas\Schema;

use Filament\Infolists\Components\TextEntry;

class ContactMessageInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name')
                    ->label('الاسم'),
                TextEntry::make('email')
                    ->label('البريد الإلكتروني')
                    ->copyable(),
                TextEntry::make('subject')
                    ->label('الموضوع')
                    ->columnSpanFull(),
                TextEntry::make('created_at')
                    ->label('الزمن')
                    ->dateTime(),
                TextEntry::make('message')
                    ->label('التفاصيل')
                    ->columnSpanFull()
                    ->markdown(),
            ]);
    }
}
