<?php

namespace App\Modules\Setting\Repositories;

interface SettingRepositoryInterface
{
    public function getAllSettings();
    public function getByKey(string $key);
    public function saveSettings(array $settings);
}
