<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckMaintenanceMode
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->role === 'admin') {
            return $next($request);
        }

        $maintenanceMode = filter_var(Setting::get('maintenance_mode', false), FILTER_VALIDATE_BOOLEAN);

        if ($maintenanceMode) {
            return response()->json(['message' => 'Service unavailable. Maintenance in progress.'], 503);
        }

        return $next($request);
    }
}
