<?php

namespace App\Filament\Resources\ForumPosts\Pages;

use App\Filament\Resources\ForumPosts\ForumPostResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageForumPosts extends ManageRecords
{
    protected static string $resource = ForumPostResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
