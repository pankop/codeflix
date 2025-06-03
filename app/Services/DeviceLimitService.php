<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserDevice;
use Illuminate\Support\Str;
use Jenssegers\Agent\Agent;


class DeviceLimitService
{
    public function registerDevice(User $user)
    {
        $deviceInfo = $this->getDeviceInfo();

        $existingDevice = $this->findExistingDevice($user, $deviceInfo);

        if ($existingDevice) {
            $existingDevice->update(['last_active' => now()]);
            session(['device_id' => $existingDevice->device_id]);
            return $existingDevice;
        }

        if ($this->hasReachedDeviceLimit($user)) {
            return false; // Tidak bisa login di device tambahan
        }

        $device = $this->createNewDevice($user, $deviceInfo);
        session(['device_id' => $device->device_id]);
        return $device;
    }

    public function logoutDevice($deviceId): void
    {
        UserDevice::where('device_id', $deviceId)->delete();
        session()->forget('device_id');
    }

    private function getDeviceInfo(): array
    {
        $agent = app(Agent::class); // Ambil instance, bukan pakai Facade

        return [
            'device_name' => $this->generateDeviceName($agent),
            'device_type' => $agent->isDesktop() ? 'desktop' : ($agent->isPhone() ? 'phone' : 'tablet'),
            'platform' => $agent->platform(),
            'platform_version' => $agent->version($agent->platform()),
            'browser' => $agent->browser(),
            'browser_version' => $agent->version($agent->browser())
        ];
    }

    private function generateDeviceName($agent): string
    {
        return ucfirst($agent->platform()) . ' ' . ucfirst($agent->browser());
    }

    private function findExistingDevice(User $user, array $deviceInfo)
    {
        return UserDevice::where('user_id', $user->id)
            ->where('device_type', $deviceInfo['device_type'])
            ->where('platform', $deviceInfo['platform'])
            ->where('browser', $deviceInfo['browser'])
            ->first();
    }

    private function hasReachedDeviceLimit(User $user): bool
    {
        $maxDevices = $user->getCurrentPlan()->max_devices ?? 1;
        return UserDevice::where('user_id', $user->id)->count() >= $maxDevices;
    }

    private function createNewDevice(User $user, array $deviceInfo): UserDevice
    {
        return UserDevice::create([
            'user_id' => $user->id,
            'device_name' => $deviceInfo['device_name'],
            'device_id' => $this->generateDeviceId(),
            'device_type' => $deviceInfo['device_type'],
            'platform' => $deviceInfo['platform'],
            'platform_version' => $deviceInfo['platform_version'],
            'browser' => $deviceInfo['browser'],
            'browser_version' => $deviceInfo['browser_version'],
            'last_active' => now(),
        ]);
    }

    private function generateDeviceId(): string
    {
        return Str::random(32);
    }
}
