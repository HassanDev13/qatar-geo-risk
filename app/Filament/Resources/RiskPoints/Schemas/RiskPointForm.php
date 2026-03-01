<?php

namespace App\Filament\Resources\RiskPoints\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class RiskPointForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('lat')
                    ->required()
                    ->numeric(),
                TextInput::make('lng')
                    ->required()
                    ->numeric(),
                TextInput::make('radius')
                    ->required()
                    ->numeric()
                    ->default(5000)
                    ->suffix('meters'),
                \Filament\Forms\Components\Select::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ])
                    ->required()
                    ->default('pending'),
                TextInput::make('submitted_by_ip')
                    ->disabled()
                    ->dehydrated(false),
                Textarea::make('description')
                    ->columnSpanFull(),
                FileUpload::make('image')
                    ->image()
                    ->disk('public')
                    ->directory('risk_points')
                    ->columnSpanFull(),
            ]);
    }
}
