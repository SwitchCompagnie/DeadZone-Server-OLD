<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Widgets\StatsOverviewWidget;
use BackedEnum;

class Dashboard extends BaseDashboard
{
    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-home';

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Widgets\UserStatsWidget::class,
            \App\Filament\Widgets\PlayerAccountStatsWidget::class,
            \App\Filament\Widgets\UserRegistrationMontlyChart::class,
            \App\Filament\Widgets\UserRegistrationDailyChart::class,
        ];
    }
}