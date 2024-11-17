<?php

namespace WildanMZaki\Wize;

class Composer
{
    public static function find(): ?string
    {
        $currentDir = __DIR__;

        while (true) {
            $composerPath = $currentDir . DIRECTORY_SEPARATOR . 'composer.json';

            if (file_exists($composerPath)) {
                return $composerPath;
            }

            $parentDir = dirname($currentDir);
            if ($parentDir === $currentDir) {
                break;
            }
            $currentDir = $parentDir;
        }
        return null;
    }

    public static function extract()
    {
        $file = self::find();
        if ($file) {
            return File::parseJSON()->assoc()->get($file);
        }
        return null;
    }
}
