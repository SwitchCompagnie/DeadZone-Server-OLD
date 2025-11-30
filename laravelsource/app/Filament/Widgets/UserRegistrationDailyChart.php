<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class UserRegistrationDailyChart extends ChartWidget
{
    protected ?string $heading = 'User Registrations Per Day';

    protected function getData(): array
    {
        $data = User::select(DB::raw('DATE(created_at) as day'), DB::raw('COUNT(*) as count'))
            ->where('created_at', '>=', now()->subDays(7))
            ->groupBy('day')
            ->orderBy('day')
            ->get();

        return [
            'labels' => $data->pluck('day')->toArray(),
            'datasets' => [
                [
                    'label' => 'New Users',
                    'data' => $data->pluck('count')->toArray(),
                    'backgroundColor' => '#10b981',
                    'borderColor' => '#047857',
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}