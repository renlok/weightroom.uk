<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Admin extends Model
{
    protected $table = 'admin_settings';
    protected $primaryKey = 'setting_id';
    protected $fillable = ['setting_value'];
    
    public static function getValue($settingName)
    {
        $setting = Admin::where('setting_name', $settingName)->first();
        if ($setting == null)
        {
            $setting = new Admin;
            $setting->setting_name = $settingName;
            $setting->setting_value = 0;
            $setting->save();
        }
        return $setting->setting_value;
    }
    
    public static function InvitesEnabled()
    {
        return Admin::getValue('invites_enabled');
    }
    
    public static function getSettings()
    {
        $settings_raw = Admin::all();
        $settings = [];
        foreach ($settings_raw as $setting)
        {
            $settings[$setting->setting_name] = $setting->setting_value;
        }
        return $settings;
    }
}
