<?php

namespace App\Filament\Resources\PlayerAccounts\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class PlayerAccountForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('player_id')
                    ->label('Player ID')
                    ->required()
                    ->disabled(fn ($record) => $record !== null)
                    ->maxLength(36),
                TextInput::make('email')
                    ->label('Email Address')
                    ->email()
                    ->required()
                    ->maxLength(255),
                TextInput::make('display_name')
                    ->label('Display Name')
                    ->required()
                    ->maxLength(100),
                TextInput::make('avatar_url')
                    ->label('Avatar URL')
                    ->url()
                    ->required()
                    ->maxLength(500),
                TextInput::make('last_login')
                    ->label('Last Login (timestamp)')
                    ->required()
                    ->numeric(),
                TextInput::make('country_code')
                    ->label('Country Code')
                    ->maxLength(10)
                    ->default(null),
                Textarea::make('hashed_password')
                    ->label('Hashed Password')
                    ->required()
                    ->columnSpanFull()
                    ->rows(3),
                Textarea::make('server_metadata_json')
                    ->label('Server Metadata (JSON)')
                    ->required()
                    ->columnSpanFull()
                    ->rows(10)
                    ->helperText('JSON format: contains notes, flags, and extra metadata')
                    ->formatStateUsing(fn ($state) => is_array($state) ? json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : $state)
                    ->dehydrateStateUsing(fn ($state) => is_string($state) ? json_decode($state, true) : $state),
                Textarea::make('player_objects_json')
                    ->label('Player Objects (JSON)')
                    ->required()
                    ->columnSpanFull()
                    ->rows(15)
                    ->helperText('JSON format: contains player data, survivors, buildings, resources, etc.')
                    ->formatStateUsing(fn ($state) => is_array($state) ? json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : $state)
                    ->dehydrateStateUsing(fn ($state) => is_string($state) ? json_decode($state, true) : $state),
                Textarea::make('neighbor_history_json')
                    ->label('Neighbor History (JSON)')
                    ->required()
                    ->columnSpanFull()
                    ->rows(10)
                    ->helperText('JSON format: contains neighbor map data')
                    ->formatStateUsing(fn ($state) => is_array($state) ? json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : $state)
                    ->dehydrateStateUsing(fn ($state) => is_string($state) ? json_decode($state, true) : $state),
                Textarea::make('inventory_json')
                    ->label('Inventory (JSON)')
                    ->required()
                    ->columnSpanFull()
                    ->rows(15)
                    ->helperText('JSON format: contains inventory items and schematics')
                    ->formatStateUsing(fn ($state) => is_array($state) ? json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : $state)
                    ->dehydrateStateUsing(fn ($state) => is_string($state) ? json_decode($state, true) : $state),
                Textarea::make('pay_vault_json')
                    ->label('Pay Vault (JSON)')
                    ->required()
                    ->columnSpanFull()
                    ->rows(10)
                    ->helperText('JSON format: contains player payment and purchase data')
                    ->formatStateUsing(fn ($state) => is_array($state) ? json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : $state)
                    ->dehydrateStateUsing(fn ($state) => is_string($state) ? json_decode($state, true) : $state),
            ]);
    }
}
