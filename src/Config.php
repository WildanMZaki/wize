<?php

namespace WildanMZaki\Wize;

use Exception;

class Config
{
    public static function extract()
    {
        return File::assoc()->parseJSON()->get(__DIR__ . '/default.config.json');
    }

    public static function set($key, $value)
    {
        $configs = self::extract();
        $configRef = &$configs;

        $fragments = '';
        $keys = explode('.', $key);

        foreach ($keys as $i => $fragment) {
            $fragments .= ($i > 0 ? '.' : '') . $fragment;

            if (is_numeric($fragment)) {
                $fragment = (int) $fragment; // Convert to integer for array indexing
            }

            if (!isset($configRef[$fragment])) {
                throw new Exception("Key '$fragments' is undefined in the config");
            } else {
                if ($i === count($keys) - 1) {
                    $configRef[$fragment] = self::convert($value);
                } else {
                    // Navigate deeper into the config structure
                    $configRef = &$configRef[$fragment];
                }
            }
        }

        // Write changes to the config file
        return File::create(_rootz('/' . WIZE_CONFIG), $configs);
    }

    protected static function convert($value)
    {
        if (strtolower($value) === 'true') {
            return true;
        } elseif (strtolower($value) === 'false') {
            return false;
        } elseif (strtolower($value) === 'null') {
            return null;
        }

        return $value;
    }
}
