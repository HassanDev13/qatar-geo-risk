<?php

namespace App\Filament\Resources\RiskPoints;

use App\Filament\Resources\RiskPoints\Pages\CreateRiskPoint;
use App\Filament\Resources\RiskPoints\Pages\EditRiskPoint;
use App\Filament\Resources\RiskPoints\Pages\ListRiskPoints;
use App\Filament\Resources\RiskPoints\Schemas\RiskPointForm;
use App\Filament\Resources\RiskPoints\Tables\RiskPointsTable;
use App\Models\RiskPoint;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class RiskPointResource extends Resource
{
    protected static ?string $model = RiskPoint::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return RiskPointForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RiskPointsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRiskPoints::route('/'),
            'create' => CreateRiskPoint::route('/create'),
            'edit' => EditRiskPoint::route('/{record}/edit'),
        ];
    }
}
