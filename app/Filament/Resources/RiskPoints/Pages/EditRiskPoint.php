<?php

namespace App\Filament\Resources\RiskPoints\Pages;

use App\Filament\Resources\RiskPoints\RiskPointResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditRiskPoint extends EditRecord
{
    protected static string $resource = RiskPointResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
