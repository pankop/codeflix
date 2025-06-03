<?php

namespace App\Http\Middleware;

use App\Services\DeviceLimitService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class LogoutDevice
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */

    protected DeviceLimitService $deviceService;

    public function __construct(DeviceLimitService $deviceService)
    {
        $this->deviceService = $deviceService;
    }

    public function handle(Request $request, Closure $next): Response
    {
        // Pastikan middleware hanya berjalan jika user mengakses route logoutMore actions
        if ($this->isLogoutRequest($request)) {
            $deviceId = Session::get('device_id');

            if ($deviceId) {
                $this->deviceService->logoutDevice($deviceId);
            }
        }

    return $next($request);
    }

    private function isLogoutRequest(Request $request): bool
    {
        // Periksa apakah request ini adalah logout dari Laravel Fortify
        return $request->is('logout') || Route::currentRouteName() === 'logout';
    }
}
