<?php

namespace App\Modules\Setting\Repositories;

use App\Modules\Setting\Models\Setting;

class SettingRepository implements SettingRepositoryInterface
{
    public function getAllSettings()
    {
        return Setting::pluck('value', 'key')->all();
    }

    public function getByKey(string $key)
    {
        return Setting::where('key', $key)->value('value');
    }

    public function saveSettings(array $settings)
    {
        foreach ($settings as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }
    }
}
