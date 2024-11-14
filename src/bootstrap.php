<?php

// Act like HTTP Request
if (php_sapi_name() === 'cli') {
    $_SERVER['HTTPS'] = $_SERVER['HTTPS'] ?? 'off';
    $_SERVER['HTTP_HOST'] = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $_SERVER['SERVER_NAME'] = $_SERVER['SERVER_NAME'] ?? 'localhost';
    $_SERVER['REQUEST_URI'] = $_SERVER['REQUEST_URI'] ?? '/';
    $_SERVER['SCRIPT_NAME'] = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
    $_SERVER['SCRIPT_FILENAME'] = $_SERVER['SCRIPT_FILENAME'] ?? __FILE__;
    $_SERVER['REMOTE_ADDR'] = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    $_SERVER['SERVER_PORT'] = $_SERVER['SERVER_PORT'] ?? '80';
}

if (file_exists(APPPATH . 'config/' . ENVIRONMENT . '/constants.php')) {
    require_once(APPPATH . 'config/' . ENVIRONMENT . '/constants.php');
}
if (file_exists(APPPATH . 'config/constants.php')) {
    require_once(APPPATH . 'config/constants.php');
}

// Load global functions
require_once(BASEPATH . 'core/Common.php');

// Load Benchmark class and start timer
$BM = &load_class('Benchmark', 'core');
$BM->mark('total_execution_time_start');
$BM->mark('loading_time:_base_classes_start');

// Instantiate the Hooks class
$EXT = &load_class('Hooks', 'core');
$EXT->call_hook('pre_system');

// Load Config class and set up configuration items
global $CFG;
$CFG = &load_class('Config', 'core');

// Set charset
ini_set('default_charset', strtoupper(config_item('charset')));

if (extension_loaded('mbstring')) {
    define('MB_ENABLED', TRUE);
    // mbstring.internal_encoding is deprecated starting with PHP 5.6
    // and it's usage triggers E_DEPRECATED messages.
    @ini_set('mbstring.internal_encoding', $charset);
    // This is required for mb_convert_encoding() to strip invalid characters.
    // That's utilized by CI_Utf8, but it's also done for consistency with iconv.
    mb_substitute_character('none');
} else {
    define('MB_ENABLED', FALSE);
}

// There's an ICONV_IMPL constant, but the PHP manual says that using
// iconv's predefined constants is "strongly discouraged".
if (extension_loaded('iconv')) {
    define('ICONV_ENABLED', TRUE);
    // iconv.internal_encoding is deprecated starting with PHP 5.6
    // and it's usage triggers E_DEPRECATED messages.
    @ini_set('iconv.internal_encoding', $charset);
} else {
    define('ICONV_ENABLED', FALSE);
}

// Load compatibility features if needed
require_once(BASEPATH . 'core/compat/mbstring.php');
require_once(BASEPATH . 'core/compat/hash.php');
require_once(BASEPATH . 'core/compat/password.php');
require_once(BASEPATH . 'core/compat/standard.php');

// Initialize core classes
$UNI = &load_class('Utf8', 'core');         // UTF-8 Class
$URI = &load_class('URI', 'core');          // URI Class
$RTR = &load_class('Router', 'core');       // Router Class
$OUT = &load_class('Output', 'core');       // Output Class

// Load Input and Language classes
$IN = &load_class('Input', 'core');
$LANG = &load_class('Lang', 'core');

// Load the base controller class
require_once BASEPATH . 'core/Controller.php';

require_once __DIR__ . '/CI_Cli.php';
