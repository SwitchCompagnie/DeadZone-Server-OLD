<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MaintenanceController extends Controller
{
    /**
     * Get maintenance mode status
     *
     * @return JsonResponse
     */
    public function status(): JsonResponse
    {
        return response()->json([
            'maintenance' => Setting::isMaintenanceMode(),
            'message' => Setting::getMaintenanceMessage(),
            'eta' => Setting::getMaintenanceETA(),
        ]);
    }
}
