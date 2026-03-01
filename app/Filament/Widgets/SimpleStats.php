<?php

namespace App\Filament\Widgets;

use App\Models\RiskPoint;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SimpleStats extends BaseWidget
{
    protected function getStats(): array
    {
        // Get basic counts
        $totalRiskPoints = RiskPoint::count();
        $totalUsers = User::count();

        // Get status breakdown
        $approvedPoints = RiskPoint::where('status', 'approved')->count();
        $pendingPoints = RiskPoint::where('status', 'pending')->count();
        $rejectedPoints = RiskPoint::where('status', 'rejected')->count();

        return [
            Stat::make('Total Risk Zones', number_format($totalRiskPoints))
                ->description('All reported danger zones')
                ->descriptionIcon('heroicon-o-map')
                ->color('primary'),

            Stat::make('Approved Zones', number_format($approvedPoints))
                ->description('Currently visible on map')
                ->descriptionIcon('heroicon-o-check-circle')
                ->color('success'),

            Stat::make('Pending Review', number_format($pendingPoints))
                ->description('Awaiting admin verification')
                ->descriptionIcon('heroicon-o-clock')
                ->color('warning'),

            Stat::make('Rejected Reports', number_format($rejectedPoints))
                ->description('Reports deemed invalid')
                ->descriptionIcon('heroicon-o-x-circle')
                ->color('danger'),

            Stat::make('Total Users', number_format($totalUsers))
                ->description('Registered admins/users')
                ->descriptionIcon('heroicon-o-users')
                ->color('gray'),
        ];
    }

    protected function getColumns(): int
    {
        return 3;
    }
}
