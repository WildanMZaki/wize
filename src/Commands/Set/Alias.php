<?php

namespace WildanMZaki\Wize\Commands\Set;

use WildanMZaki\Wize\Command;
use WildanMZaki\Wize\File;

class Alias extends Command
{
    protected $signature = 'set:alias {alias} {command}';
    protected $description = 'Set additional alias';

    public function run()
    {
        $alias = $this->argument('alias');
        $command = $this->argument('command');

        $this->configs['aliases'][$alias] = $command;
        $configFile = _rootz(WIZE_CONFIG);
        $success = File::create($configFile, $this->configs);

        if ($success) {
            $this->inform('Alias registered successfully');
        }
    }
}
