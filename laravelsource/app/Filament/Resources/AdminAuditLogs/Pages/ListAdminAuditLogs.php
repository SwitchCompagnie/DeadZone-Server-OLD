<?php

namespace App\Filament\Resources\AdminAuditLogs\Pages;

use App\Filament\Resources\AdminAuditLogs\AdminAuditLogResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListAdminAuditLogs extends ListRecords
{
    protected static string $resource = AdminAuditLogResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All')
                ->badge(fn () => \App\Models\AdminAuditLog::count()),

            'view' => Tab::make('Views')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('action', 'view'))
                ->badge(fn () => \App\Models\AdminAuditLog::where('action', 'view')->count()),

            'create' => Tab::make('Creates')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('action', 'create'))
                ->badge(fn () => \App\Models\AdminAuditLog::where('action', 'create')->count()),

            'update' => Tab::make('Updates')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('action', 'update'))
                ->badge(fn () => \App\Models\AdminAuditLog::where('action', 'update')->count()),

            'delete' => Tab::make('Deletes')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('action', 'delete'))
                ->badge(fn () => \App\Models\AdminAuditLog::where('action', 'delete')->count()),

            'today' => Tab::make('Today')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereDate('created_at', today()))
                ->badge(fn () => \App\Models\AdminAuditLog::whereDate('created_at', today())->count()),

            'week' => Tab::make('This Week')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]))
                ->badge(fn () => \App\Models\AdminAuditLog::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count()),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [];
    }
}
