<?php

namespace WildanMZaki\Wize\Facades;

use WildanMZaki\Wize\Console;

class Wizely
{
    protected static function getBasePath(): string
    {
        $currentDir = __DIR__;
        $possible_dev_root = null;

        while (true) {
            $composerPath = $currentDir . '/composer.json';

            if (file_exists($composerPath)) {
                $composerConfig = json_decode(file_get_contents($composerPath), true);

                // Check if wildanmzaki/wize is listed in require or require-dev
                if (
                    isset($composerConfig['require']['wildanmzaki/wize']) ||
                    isset($composerConfig['require-dev']['wildanmzaki/wize'])
                ) {
                    return realpath($currentDir);
                }

                // Dev Fallback
                if (isset($composerConfig['name']) && $composerConfig['name'] === 'wildanmzaki/wize') {
                    $possible_dev_root = realpath($currentDir);
                }
            }

            $parentDir = dirname($currentDir);

            // Stop if we reach the root directory without finding a valid composer.json
            if ($parentDir === $currentDir) {
                if ($possible_dev_root) return $possible_dev_root; // Fallback in dev mode
                throw new \Exception("Base path could not be determined. 'composer.json' with 'wildanmzaki/wize' dependency not found.");
            }

            // Move up one directory level
            $currentDir = $parentDir;
        }
    }

    public static function call(string $command, array $options = [])
    {
        if (!defined('BASE_PATH')) define('BASE_PATH', self::getBasePath());
        if (!defined('WIZE_CONFIG')) define(
            'WIZE_CONFIG',
            'wize.config.json'
        );

        $configs = [];
        $configFile =  ltrim(BASE_PATH, '/') . '/' . WIZE_CONFIG;
        if (file_exists($configFile)) {
            $configs = json_decode(file_get_contents($configFile), true);
        }

        $caller = basename(__FILE__);

        $console = new Console();
        $console->setConfig($configs);
        $console->setCaller($caller);

        $fragments = explode(' ', $command);
        $command = array_shift($fragments);

        $argumentsAndOptions = [...$fragments, ...$options];

        $console->run($command ?? '', $argumentsAndOptions);
    }
}
