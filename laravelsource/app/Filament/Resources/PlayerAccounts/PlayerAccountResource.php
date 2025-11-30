<?php

namespace App\Filament\Resources\PlayerAccounts;

use App\Filament\Resources\PlayerAccounts\Pages\CreatePlayerAccount;
use App\Filament\Resources\PlayerAccounts\Pages\EditPlayerAccount;
use App\Filament\Resources\PlayerAccounts\Pages\ListPlayerAccounts;
use App\Filament\Resources\PlayerAccounts\Schemas\PlayerAccountForm;
use App\Filament\Resources\PlayerAccounts\Tables\PlayerAccountsTable;
use App\Models\PlayerAccount;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PlayerAccountResource extends Resource
{
    protected static ?string $model = PlayerAccount::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return PlayerAccountForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PlayerAccountsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPlayerAccounts::route('/'),
            'create' => CreatePlayerAccount::route('/create'),
            'edit' => EditPlayerAccount::route('/{record}/edit'),
        ];
    }
}
