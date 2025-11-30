<?php

namespace App\Console\Commands;

use App\Models\Setting;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BroadcastMaintenanceMessage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'maintenance:broadcast';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Broadcast maintenance message to all connected players if maintenance mode is active';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        // Check if maintenance mode is active
        if (!Setting::isMaintenanceMode()) {
            return self::SUCCESS;
        }

        try {
            // Get maintenance message and ETA
            $message = Setting::getMaintenanceMessage();
            $eta = Setting::getMaintenanceETA();

            // Build the broadcast message
            $broadcastMessage = "MAINTENANCE: {$message} ETA: {$eta}";

            // Send the broadcast
            $response = Http::timeout(5)->post(env('API_BASE_URL') . '/api/broadcast/send', [
                'protocol' => 'warn',
                'arguments' => [$broadcastMessage],
            ]);

            if ($response->successful()) {
                $responseData = $response->json();
                $clientCount = $responseData['clientCount'] ?? 0;

                Log::info("Maintenance broadcast sent to {$clientCount} player(s)");
                $this->info("Maintenance message broadcast to {$clientCount} player(s)");
            } else {
                Log::error('Failed to broadcast maintenance message: Server returned an error');
                $this->error('Failed to broadcast maintenance message');
            }
        } catch (\Exception $e) {
            Log::error('Failed to broadcast maintenance message: ' . $e->getMessage());
            $this->error('Error broadcasting maintenance message: ' . $e->getMessage());
        }

        return self::SUCCESS;
    }
}
