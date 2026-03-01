<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->required(),
                TextInput::make('password')
                    ->password()
                    ->required()
                    ->dehydrateStateUsing(fn ($state) => bcrypt($state))
                    ->dehydrated(fn ($state) => filled($state))
                    ->required(fn (string $context): bool => $context === 'create'),
                Toggle::make('is_admin')
                    ->label('Administrator')
                    ->helperText('Grant admin access to Filament dashboard'),
                Toggle::make('is_unlimited')
                    ->label('Unlimited Credits')
                    ->helperText('Grant unlimited search credits (bypasses daily limit)'),
                \Filament\Forms\Components\Select::make('speciality_id')
                    ->relationship('speciality', 'name')
                    ->label('Speciality'),
                \Filament\Forms\Components\TextInput::make('linkedin_url')
                    ->url()
                    ->label('LinkedIn URL'),
                \Filament\Forms\Components\Select::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ])
                    ->default('pending')
                    ->required(),
                DateTimePicker::make('email_verified_at'),
            ]);
    }
}
