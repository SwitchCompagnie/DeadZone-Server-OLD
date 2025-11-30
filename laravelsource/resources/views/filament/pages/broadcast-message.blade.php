<x-filament-panels::page>
    <div class="mb-6 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-6 space-y-6">

        <div class="space-y-5">
            <div>
                <h4 class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">Maintenance</h4>
                <div class="flex flex-wrap gap-2">
                    <x-filament::button
                        color="warning"
                        icon="heroicon-o-wrench"
                        size="sm"
                        wire:click="$set('data.message', 'Server restart in 5 minutes. Please find a safe zone !')">
                        Restart (5min)
                    </x-filament::button>
                    <x-filament::button
                        color="success"
                        icon="heroicon-o-check-circle"
                        size="sm"
                        wire:click="$set('data.message', 'Server maintenance completed. Welcome back survivors !')">
                        Maintenance Complete
                    </x-filament::button>
                </div>
            </div>

            <div>
                <h4 class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">Admin</h4>
                <div class="flex flex-wrap gap-2">
                    <x-filament::button
                        color="primary"
                        icon="heroicon-o-shield-check"
                        size="sm"
                        wire:click="$set('data.protocol', 'admin'); $set('data.message', 'Admin announcement: Please follow server rules!')">
                        Admin Announcement
                    </x-filament::button>
                </div>
            </div>

            <div>
                <h4 class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">Events</h4>
                <div class="flex flex-wrap gap-2">
                    <x-filament::button
                        color="info"
                        icon="heroicon-o-sparkles"
                        size="sm"
                        wire:click="$set('data.message', 'Special event starting now ! Check discord server !')">
                        Event Started
                    </x-filament::button>
                </div>
            </div>

            <div>
                <h4 class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">Community</h4>
                <div class="flex flex-wrap gap-2">
                    <x-filament::button
                        color="primary"
                        icon="heroicon-o-arrow-up-circle"
                        size="sm"
                        wire:click="$set('data.message', 'New update deployed ! Check the patch notes for details.')">
                        Update Deployed
                    </x-filament::button>
                    <x-filament::button
                        color="gray"
                        icon="heroicon-o-heart"
                        size="sm"
                        wire:click="$set('data.message', 'Thank you for playing ! Stay safe out there, survivors !')">
                        Thank You
                    </x-filament::button>
                    <x-filament::button
                        color="success"
                        icon="heroicon-o-check-badge"
                        size="sm"
                        wire:click="$set('data.message', 'Server performing smoothly. Happy hunting !')">
                        Status OK
                    </x-filament::button>
                </div>
            </div>

            <div>
                <h4 class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">Warnings</h4>
                <div class="flex flex-wrap gap-2">
                    <x-filament::button
                        color="warning"
                        icon="heroicon-o-signal-slash"
                        size="sm"
                        wire:click="$set('data.protocol', 'warn'); $set('data.message', 'Connection issues detected. We are working on it !')">
                        Connection Issue
                    </x-filament::button>
                    <x-filament::button
                        color="danger"
                        icon="heroicon-o-cpu-chip"
                        size="sm"
                        wire:click="$set('data.protocol', 'warn'); $set('data.message', 'High server load detected. Please be patient !')">
                        High Load
                    </x-filament::button>
                </div>
            </div>
        </div>
    </div>
    <form wire:submit="sendBroadcast">
        {{ $this->form }}
        <div class="mt-6 flex justify-end gap-3">
            @foreach($this->getFormActions() as $action)
                {{ $action }}
            @endforeach
        </div>
    </form>
    <x-filament-actions::modals />
</x-filament-panels::page>