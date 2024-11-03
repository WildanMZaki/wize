<?php

namespace WildanMZaki\Wize;

use Exception;

class Template
{
    protected static $replacer = [];
    protected static $_placeholder = '{data}';
    protected static $extension = '.wz';
    protected static $_theme = '';

    public static function get(string $path)
    {
        $path = self::$_theme . '/' . rtrim($path, self::$extension);
        $path = ltrim($path, '/');
        $content = File::get(__DIR__ . "/templates/$path" . self::$extension);
        if (!empty(self::$replacer)) {
            foreach (self::$replacer as $key => $value) {
                $search = str_replace('data', $key, self::$_placeholder);
                $searchLc = str_replace('data', "lc_$key", self::$_placeholder);
                $content = str_replace($search, $value, $content);
                $content = str_replace($searchLc, strtolower($value), $content);
            }
        }
        return $content;
    }

    public static function replace(array $arr = []): self
    {
        self::$replacer = $arr;
        return new self;
    }

    public static function placeholder(string $placeholder): self
    {
        if (strpos($placeholder, 'data') === false) throw new Exception("Placeholder must have 'data' word");

        self::$_placeholder = $placeholder;
        return new self;
    }

    public static function ext(string $ext): self
    {
        if (strpos($ext, '.') !== 0) throw new Exception('Extension must start with . (dot)');
        self::$extension = $ext;
        return new self;
    }

    public static function theme(string $theme): self
    {
        self::$_theme = $theme;
        return new self;
    }
}
