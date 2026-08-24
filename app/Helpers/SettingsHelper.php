<?php

if (!function_exists('setting')) {
    function setting($key, $default = null) {
        return \App\Models\Setting::get($key, $default);
    }
}
if (!function_exists('site_name')) {
    function site_name() { return setting('site_name', config('app.name','Huvanti')); }
}
