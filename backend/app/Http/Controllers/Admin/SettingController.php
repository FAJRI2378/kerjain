<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SettingRequest;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        $settings = [
            'platform_commission' => (int) Setting::get('platform_commission', 10),
            'auto_approve_jobs' => filter_var(Setting::get('auto_approve_jobs', false), FILTER_VALIDATE_BOOLEAN),
            'maintenance_mode' => filter_var(Setting::get('maintenance_mode', false), FILTER_VALIDATE_BOOLEAN),
            'email_notifications' => filter_var(Setting::get('email_notifications', true), FILTER_VALIDATE_BOOLEAN),
        ];

        return response()->json(['data' => $settings]);
    }

    public function update(SettingRequest $request)
    {
        Setting::set('platform_commission', $request->platform_commission);
        Setting::set('auto_approve_jobs', $request->auto_approve_jobs ? 'true' : 'false');
        Setting::set('maintenance_mode', $request->maintenance_mode ? 'true' : 'false');
        Setting::set('email_notifications', $request->email_notifications ? 'true' : 'false');

        return response()->json(['message' => 'Settings updated successfully.']);
    }
}
