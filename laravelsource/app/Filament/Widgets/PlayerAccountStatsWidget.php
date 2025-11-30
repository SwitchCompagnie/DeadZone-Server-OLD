<?php

namespace App\Filament\Widgets;

use App\Models\PlayerAccount;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PlayerAccountStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $recentLogins = PlayerAccount::where('last_login', '>=', now()->subDays(7)->timestamp)->count();
        $topCountry = PlayerAccount::groupBy('country_code')
            ->selectRaw('country_code, COUNT(*) as total')
            ->orderBy('total', 'desc')
            ->first();

        return [
            Stat::make('Total Player Accounts', PlayerAccount::count())
                ->description('Total number of player accounts')
                ->icon('heroicon-o-user-group')
                ->color('primary'),
            Stat::make('Recent Logins (7 days)', $recentLogins)
                ->description('Logins in the last 7 days')
                ->icon('heroicon-o-clock')
                ->color('info'),
            Stat::make('Top Country', $topCountry ? ($topCountry->country_code ?? 'N/A') : 'N/A')
                ->description($topCountry ? "{$topCountry->total} players" : 'No players')
                ->icon('heroicon-o-globe-alt')
                ->color('success'),
        ];
    }
}