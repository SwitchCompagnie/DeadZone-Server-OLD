<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Http;

class BroadcastMessage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-megaphone';
    protected static ?string $navigationLabel = 'Broadcast Message';
    protected static ?string $title = 'Send In-Game Message';
    protected string $view = 'filament.pages.broadcast-message';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'protocol' => 'plain',
            'message' => '',
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('protocol')
                    ->label('Message Type')
                    ->options([
                        'plain' => 'Plain Message',
                        'admin' => 'Admin Announcement',
                        'warn' => 'Warning',
                    ])
                    ->required()
                    ->default('plain')
                    ->helperText('Plain: Regular message | Admin: Blink announcement message | Warning: Warning message'),
                Textarea::make('message')
                    ->label('Message Content')
                    ->required()
                    ->rows(4)
                    ->maxLength(500)
                    ->placeholder('Enter your message here...')
                    ->helperText('Maximum 500 characters'),
            ])
            ->statePath('data');
    }

    public function sendBroadcast(): void
    {
        $data = $this->form->getState();

        try {
            $response = Http::timeout(10)->post(env('API_BASE_URL') . '/api/broadcast/send', [
                'protocol' => $data['protocol'],
                'arguments' => [$data['message']],
            ]);

            if ($response->successful()) {
                $responseData = $response->json();
                $clientCount = $responseData['clientCount'] ?? 0;

                Notification::make()
                    ->title('Broadcast Sent Successfully')
                    ->body("Message delivered to {$clientCount} player(s)")
                    ->success()
                    ->send();

                $this->form->fill([
                    'protocol' => 'plain',
                    'message' => '',
                ]);
            } else {
                Notification::make()
                    ->title('Broadcast Failed')
                    ->body('Unable to send message. Server returned an error.')
                    ->danger()
                    ->send();
            }
        } catch (\Exception $e) {
            Notification::make()
                ->title('Connection Error')
                ->body('Could not connect to game server: ' . $e->getMessage())
                ->danger()
                ->send();
            }
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('send')
                ->label('Send Broadcast')
                ->icon('heroicon-o-paper-airplane')
                ->action('sendBroadcast')
                ->color('primary')
                ->requiresConfirmation()
                ->modalHeading('Confirm Broadcast')
                ->modalDescription('Are you sure you want to send this message to all connected players?')
                ->modalSubmitActionLabel('Yes, Send'),
        ];
    }
}