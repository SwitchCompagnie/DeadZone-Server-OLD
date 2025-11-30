<?php

namespace App\Filament\Resources\PlayerAccounts\Pages;

use App\Filament\Resources\PlayerAccounts\PlayerAccountResource;
use App\Traits\FilamentAuditTrait;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPlayerAccount extends EditRecord
{
    use FilamentAuditTrait;

    protected static string $resource = PlayerAccountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
