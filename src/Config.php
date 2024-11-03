<?php

namespace WildanMZaki\Wize;

class Config
{
    public static function extract()
    {
        return File::assoc()->parseJSON()->get(__DIR__ . '/default.config.json');
    }
}
