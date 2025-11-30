<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Setting;
use Symfony\Component\HttpFoundation\Response;

class CheckMaintenanceMode
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if maintenance mode is enabled
        if (Setting::isMaintenanceMode()) {
            // Allow admin users to bypass maintenance mode
            if ($request->user() && $request->user()->is_admin) {
                return $next($request);
            }

            // Return maintenance view for web requests
            if (!$request->expectsJson()) {
                return response()->view('maintenance', [
                    'message' => Setting::getMaintenanceMessage(),
                    'eta' => Setting::getMaintenanceETA(),
                ], 503);
            }

            // Return JSON response for API requests
            return response()->json([
                'maintenance' => true,
                'message' => Setting::getMaintenanceMessage(),
                'eta' => Setting::getMaintenanceETA(),
            ], 503);
        }

        return $next($request);
    }
}
