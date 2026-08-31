<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            ['key' => 'platform_commission', 'value' => '10'],
            ['key' => 'auto_approve_jobs', 'value' => 'false'],
            ['key' => 'maintenance_mode', 'value' => 'false'],
            ['key' => 'email_notifications', 'value' => 'true'],
        ];

        foreach ($settings as $setting) {
            Setting::create($setting);
        }
    }
}
