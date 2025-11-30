<?php

namespace App\Filament\Resources\PlayerAccounts\Pages;

use App\Filament\Resources\PlayerAccounts\PlayerAccountResource;
use App\Traits\FilamentAuditTrait;
use Filament\Resources\Pages\CreateRecord;

class CreatePlayerAccount extends CreateRecord
{
    use FilamentAuditTrait;

    protected static string $resource = PlayerAccountResource::class;
}
