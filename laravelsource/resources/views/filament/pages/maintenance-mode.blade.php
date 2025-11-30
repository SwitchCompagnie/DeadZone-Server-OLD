<x-filament-panels::page>
    <div class="space-y-6">
        @if(App\Models\Setting::isMaintenanceMode())
            <div class="bg-red-50 dark:bg-red-900/20 border-l-4 border-red-500 rounded-lg p-6">
                <div class="space-y-3">
                        <div>
                            <p class="text-base font-semibold text-red-800 dark:text-red-200">
                                Maintenance Mode is Currently ACTIVE
                            </p>
                        </div>
                        <div class="space-y-2 text-sm text-red-700 dark:text-red-300">
                            <p>
                                Users cannot login or access the game. Only administrators can access the site.
                            </p>
                            <p>
                                <strong>Auto-broadcast:</strong> Your maintenance message is being automatically broadcast to all players every 30 seconds.
                            </p>
                        </div>
                    </div>
            </div>
        @else
            <div class="bg-green-50 dark:bg-green-900/20 border-l-4 border-green-500 rounded-lg p-6">
                <div class="space-y-3">
                        <div>
                            <p class="text-base font-semibold text-green-800 dark:text-green-200">
                                Site is Operating Normally
                            </p>
                        </div>
                        <div class="text-sm text-green-700 dark:text-green-300">
                            <p>
                                Users can login and access the game without restrictions.
                            </p>
                        </div>
                    </div>
            </div>
        @endif

        <form wire:submit="saveSettings">
            {{ $this->form }}
            <div class="mt-6 flex justify-end gap-3">
                @foreach($this->getFormActions() as $action)
                    {{ $action }}
                @endforeach
            </div>
        </form>

        <x-filament-actions::modals />
    </div>
</x-filament-panels::page>
