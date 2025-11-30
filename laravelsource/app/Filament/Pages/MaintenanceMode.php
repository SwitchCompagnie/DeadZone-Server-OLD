<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;

class MaintenanceMode extends Page implements HasForms
{
    use InteractsWithForms;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-wrench-screwdriver';
    protected static ?string $navigationLabel = 'Maintenance Mode';
    protected static ?string $title = 'Maintenance Mode Settings';
    protected static ?int $navigationSort = 10;
    protected string $view = 'filament.pages.maintenance-mode';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'maintenance_mode' => Setting::isMaintenanceMode(),
            'maintenance_message' => Setting::getMaintenanceMessage(),
            'maintenance_eta' => Setting::getMaintenanceETA(),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Toggle::make('maintenance_mode')
                    ->label('Enable Maintenance Mode')
                    ->helperText('When enabled, users will not be able to login or access the game. Admins can still access the site.')
                    ->inline(false)
                    ->onColor('danger')
                    ->offColor('success'),

                Textarea::make('maintenance_message')
                    ->label('Maintenance Message')
                    ->required()
                    ->rows(3)
                    ->maxLength(500)
                    ->placeholder('Enter the message to display during maintenance...')
                    ->helperText('This message will be displayed to users when they try to access the site.'),

                TextInput::make('maintenance_eta')
                    ->label('Estimated Time of Completion')
                    ->required()
                    ->placeholder('00:00')
                    ->helperText('Enter the estimated completion time in 24-hour format (e.g., 14:30 for 2:30 PM)')
                    ->regex('/^([0-1]?[0-9]|2[0-3]):[0-5][0-9]$/')
                    ->validationMessages([
                        'regex' => 'Please enter a valid time in HH:MM format (e.g., 14:30)',
                    ]),
            ])
            ->statePath('data');
    }

    public function saveSettings(): void
    {
        $data = $this->form->getState();

        try {
            Setting::set('maintenance_mode', $data['maintenance_mode'] ? 'true' : 'false');
            Setting::set('maintenance_message', $data['maintenance_message']);
            Setting::set('maintenance_eta', $data['maintenance_eta']);

            // Clear all settings cache to ensure immediate update
            Setting::clearCache();

            $status = $data['maintenance_mode'] ? 'enabled' : 'disabled';

            Notification::make()
                ->title('Settings Saved')
                ->body("Maintenance mode has been {$status} successfully.")
                ->success()
                ->send();

        } catch (\Exception $e) {
            Notification::make()
                ->title('Error Saving Settings')
                ->body('Unable to save maintenance settings: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Save Settings')
                ->icon('heroicon-o-check-circle')
                ->action('saveSettings')
                ->color('primary')
                ->requiresConfirmation()
                ->modalHeading('Confirm Changes')
                ->modalDescription(fn () => $this->data['maintenance_mode']
                    ? 'Are you sure you want to ENABLE maintenance mode? Users will not be able to login or play the game.'
                    : 'Are you sure you want to DISABLE maintenance mode? Users will be able to access the site normally.')
                ->modalSubmitActionLabel('Yes, Save Changes'),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
