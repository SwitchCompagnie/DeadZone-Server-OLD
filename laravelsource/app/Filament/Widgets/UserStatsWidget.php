<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class UserStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Users', User::count())
                ->description('Total registered users')
                ->icon('heroicon-o-users')
                ->color('primary'),
            Stat::make('Admin Users', User::where('is_admin', true)->count())
                ->description('Number of administrators')
                ->icon('heroicon-o-shield-check')
                ->color('warning'),
            Stat::make('Verified Emails', User::whereNotNull('email_verified_at')->count())
                ->description('Users with verified email')
                ->icon('heroicon-o-check-circle')
                ->color('success'),
        ];
    }
}