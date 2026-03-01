<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\SimpleStats;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    public function getWidgets(): array
    {
        return [SimpleStats::class];
    }

    public function getColumns(): int
    {
        return 1;
    }
}
