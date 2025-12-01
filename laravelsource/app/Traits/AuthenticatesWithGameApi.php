<?php

namespace App\Traits;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

trait AuthenticatesWithGameApi
{
    /**
     * Authenticate with the game API (handles both login and registration).
     */
    protected function authenticateWithGameApi(string $username, string $password, ?string $email = null, ?string $countryCode = null, string $endpoint = '/api/auth'): ?string
    {
        try {
            $apiUrl = config('app.api_base_url');
            Log::info("API auth attempt for user: {$username} to {$apiUrl}{$endpoint}");

            $payload = ['username' => $username, 'password' => $password];
            if ($email !== null) {
                $payload['email'] = $email;
            }
            if ($countryCode !== null) {
                $payload['countryCode'] = $countryCode;
            }

            $response = Http::timeout(10)->post($apiUrl.$endpoint, $payload);

            if ($response->successful()) {
                Log::info("API auth successful for user: {$username}");

                return $response->json()['token'] ?? null;
            }

            Log::warning("API auth failed for user: {$username}, status: {$response->status()}, body: {$response->body()}");

            return null;
        } catch (\Exception $e) {
            Log::error("API auth error for user {$username}: ".$e->getMessage());

            return null;
        }
    }

    /**
     * Get country code from IP address with caching.
     */
    protected function getCountryCodeFromIp(?string $ip): ?string
    {
        if (empty($ip) || in_array($ip, ['127.0.0.1', '::1', 'localhost'])) {
            return null;
        }

        return Cache::remember('country_code_'.md5($ip), 3600, fn () => $this->fetchCountryCodeFromApi($ip));
    }

    /**
     * Fetch country code from external API.
     */
    private function fetchCountryCodeFromApi(string $ip): ?string
    {
        try {
            // Use HTTPS for secure data transmission
            $response = Http::timeout(2)->get("https://ip-api.com/json/{$ip}?fields=countryCode");

            return $response->successful() ? ($response->json()['countryCode'] ?? null) : null;
        } catch (\Exception $e) {
            Log::debug("Failed to get country code for IP {$ip}: ".$e->getMessage());

            return null;
        }
    }
}
