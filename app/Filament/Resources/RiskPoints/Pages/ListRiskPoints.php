<?php

namespace App\Filament\Resources\RiskPoints\Pages;

use App\Filament\Resources\RiskPoints\RiskPointResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRiskPoints extends ListRecords
{
    protected static string $resource = RiskPointResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
