<?php

namespace App\Filament\Resources\PlayerAccounts\Pages;

use App\Filament\Resources\PlayerAccounts\PlayerAccountResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPlayerAccounts extends ListRecords
{
    protected static string $resource = PlayerAccountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
