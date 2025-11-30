<?php

namespace App\Filament\Resources\AdminAuditLogs\Pages;

use App\Filament\Resources\AdminAuditLogs\AdminAuditLogResource;
use Filament\Infolists\Components as InfolistComponents;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components;
use Filament\Schemas\Schema;

class ViewAdminAuditLog extends ViewRecord
{
    protected static string $resource = AdminAuditLogResource::class;

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Components\Section::make('General Information')
                    ->schema([
                        Components\Grid::make(2)
                            ->schema([
                                InfolistComponents\TextEntry::make('created_at')
                                    ->label('Date & Time')
                                    ->dateTime('d/m/Y H:i:s'),

                                InfolistComponents\TextEntry::make('user.name')
                                    ->label('User')
                                    ->default(fn ($record) => $record->user_name ?? 'System')
                                    ->url(fn ($record) => $record->user_id ? route('filament.admin.resources.users.edit', ['record' => $record->user_id]) : null),

                                InfolistComponents\TextEntry::make('user.email')
                                    ->label('User Email')
                                    ->default('N/A'),

                                InfolistComponents\TextEntry::make('action')
                                    ->label('Action')
                                    ->formatStateUsing(fn ($record) => $record->action_label)
                                    ->badge()
                                    ->color(fn ($record) => $record->action_color),
                            ]),
                    ]),

                Components\Section::make('Affected Resource')
                    ->schema([
                        Components\Grid::make(2)
                            ->schema([
                                InfolistComponents\TextEntry::make('resource_name')
                                    ->label('Resource Type'),

                                InfolistComponents\TextEntry::make('resource_title')
                                    ->label('Item'),

                                InfolistComponents\TextEntry::make('resource_id')
                                    ->label('Record ID'),

                                InfolistComponents\TextEntry::make('resource_type')
                                    ->label('Model Class')
                                    ->default('N/A'),
                            ]),

                        InfolistComponents\TextEntry::make('description')
                            ->label('Description')
                            ->columnSpanFull(),
                    ]),

                Components\Section::make('Old Values')
                    ->schema([
                        InfolistComponents\KeyValueEntry::make('old_values')
                            ->label('')
                            ->hiddenLabel()
                            ->columnSpanFull(),
                    ])
                    ->visible(fn ($record) => ! empty($record->old_values))
                    ->collapsible(),

                Components\Section::make('New Values')
                    ->schema([
                        InfolistComponents\KeyValueEntry::make('new_values')
                            ->label('')
                            ->hiddenLabel()
                            ->columnSpanFull(),
                    ])
                    ->visible(fn ($record) => ! empty($record->new_values))
                    ->collapsible(),

                Components\Section::make('Technical Information')
                    ->schema([
                        Components\Grid::make(2)
                            ->schema([
                                InfolistComponents\TextEntry::make('ip_address')
                                    ->label('IP Address'),

                                InfolistComponents\TextEntry::make('url')
                                    ->label('URL')
                                    ->limit(50)
                                    ->tooltip(fn ($record) => $record->url),

                                InfolistComponents\TextEntry::make('user_agent')
                                    ->label('User Agent')
                                    ->columnSpanFull()
                                    ->limit(100)
                                    ->tooltip(fn ($record) => $record->user_agent),
                            ]),
                    ])
                    ->collapsible()
                    ->collapsed(),

                Components\Section::make('Metadata')
                    ->schema([
                        InfolistComponents\KeyValueEntry::make('metadata')
                            ->label('')
                            ->hiddenLabel()
                            ->columnSpanFull(),
                    ])
                    ->visible(fn ($record) => ! empty($record->metadata))
                    ->collapsible()
                    ->collapsed(),
            ]);
    }
}
