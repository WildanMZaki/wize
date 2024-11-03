<?php

namespace WildanMZaki\Wize;

class File
{
    private static $associative = false;
    private static $parsedToJSON = false;

    public static function exists(string $filename): bool
    {
        return file_exists($filename);
    }

    public static function check(string $filename, ?callable $cbError = null): bool
    {
        $available = self::exists($filename);
        if (!$available) $cbError($filename);
        return $available;
    }

    public static function assoc($state = true): self
    {
        self::$associative = $state;
        return new self;
    }

    public static function parseJSON($state = true): self
    {
        self::$parsedToJSON = $state;
        return new self;
    }

    public static function get(string $filename)
    {
        $content = file_get_contents($filename);
        if (self::$parsedToJSON) {
            self::$parsedToJSON = false;
            return json_decode($content, self::$associative);
        } else {
            return $content;
        }
    }

    public static function create(string $filename, $content)
    {
        if (is_array($content) || is_object($content)) {
            $content = json_encode($content, JSON_PRETTY_PRINT);
        }

        return file_put_contents($filename, $content);
    }
}
