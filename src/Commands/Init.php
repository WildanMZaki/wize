<?php

namespace WildanMZaki\Wize\Commands;

use WildanMZaki\Wize\Command;

class Init extends Command
{
    protected $signature = 'init --theme=default';
    protected $description = 'Initialize wize cli';

    public function run() {}
}
