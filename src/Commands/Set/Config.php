<?php

namespace WildanMZaki\Wize\Commands\Set;

use WildanMZaki\Wize\Command;
use WildanMZaki\Wize\Config as WizeConfig;
use WildanMZaki\Wize\File;

class Config extends Command
{
    protected $signature = 'set:config {key} {value}';
    protected $description = 'Set and configure this cli tool';

    public function run()
    {
        $key = $this->argument('key');
        // Validate and convert boolean values
        $value = $this->argument('value');

        try {
            $status = WizeConfig::set($key, $value);
            if ($status) {
                $this->success('Config updated');
            }
        } catch (\Exception $er) {
            $this->danger($er->getMessage());
        }
    }
}
