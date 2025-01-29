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
            if (!$content) {
                $content = '';
            }
            return $content;
        }
    }

    public static function lines(string $filename, int $flags = 0): array|false
    {
        if (!self::exists($filename)) {
            return false;
        }

        return file($filename, $flags);
    }


    public static function create(string $filename, $content)
    {
        if (is_array($content) || is_object($content)) {
            $content = json_encode($content, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        }

        return file_put_contents($filename, $content);
    }

    public static function delete(string $filename): bool
    {
        if (self::exists($filename)) {
            return unlink($filename);
        }
        return false;
    }
}
