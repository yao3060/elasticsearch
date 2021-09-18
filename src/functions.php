<?php

if (!function_exists('is_prod')) {
    function is_prod()
    {
        return getenv('APP_ENV') === 'production';
    }
}

if (!function_exists('is_local')) {
    function is_local()
    {
        return getenv('APP_ENV') === 'local' || getenv('APP_ENV') === 'dev';
    }
}
