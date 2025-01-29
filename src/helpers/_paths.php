<?php

if (!function_exists('_rootz')) {
    function _rootz(string $path = '')
    {
        $path = ltrim($path, '/');
        // return BASE_PATH . "/$path";
        return str_replace('/', DIRECTORY_SEPARATOR, BASE_PATH . "/$path");
    }
}

if (!function_exists('_application')) {
    function _application(string $path = '')
    {
        $path = ltrim($path, '/');
        return _rootz("/application/$path");
    }
}

if (!function_exists('_helpers')) {
    function _helpers(string $path = '')
    {
        $path = ltrim($path, '/');
        return _application("/helpers/$path");
    }
}

if (!function_exists('_libraries')) {
    function _libraries(string $path = '')
    {
        $path = ltrim($path, '/');
        return _application("/libraries/$path");
    }
}

if (!function_exists('_controllers')) {
    function _controllers(string $path = '')
    {
        $path = ltrim($path, '/');
        return _application("/controllers/$path");
    }
}

if (!function_exists('_models')) {
    function _models(string $path = '')
    {
        $path = ltrim($path, '/');
        return _application("/models/$path");
    }
}

if (!function_exists('_modules')) {
    function _modules(string $path = '')
    {
        $path = ltrim($path, '/');
        return _application("/modules/$path");
    }
}

if (!function_exists('_moduleControllers')) {
    function _moduleControllers(string $module, string $path = '')
    {
        $module = ltrim($module, '/');
        $path = ltrim($path, '/');
        return _modules("/$module/controllers/$path");
    }
}

if (!function_exists('_moduleModels')) {
    function _moduleModels(string $module, string $path = '')
    {
        $module = ltrim($module, '/');
        $path = ltrim($path, '/');
        return _modules("/$module/models/$path");
    }
}

if (!function_exists('_moduleViews')) {
    function _moduleViews(string $module, string $path = '')
    {
        $module = ltrim($module, '/');
        $path = ltrim($path, '/');
        return _modules("/$module/view/$path");
    }
}
