<?php

namespace WildanMZaki\Wize\Commands\Create;

use WildanMZaki\Wize\Command;
use WildanMZaki\Wize\File;
use WildanMZaki\Wize\Template;

class Helper extends Command
{
    protected $signature = 'create:helper {name}
        {--theme=default : Theme is template you want to use}
        {--no-suffix : Define do you want create helper file with suffix _helper or no}
        {--fn= : You can create initial function here, you can create multiple by separating function name with comma}
        {--no-safety : Don\'t create condition to check is the function exists or no}
    ';
    protected $description = 'This command can help you create helper file';

    public function run()
    {
        $name = $this->argument('name');
        if (!$name) {
            $name = $this->ask('What is your helper name?');
        }
        $name = str_replace('.php', '', $name);
        $name = str_replace('helper', '', $name);

        if (!$this->option('no-suffix')) {
            $name .= "_helper";
        }

        $fns = $this->option('fn');
        $fn = '';
        if ($fns) {
            $functions = explode(',', $fns);
            $tem = $this->option('no-safety') ? 'unsafe-fn' : 'safe-fn';
            foreach ($functions as $fun) {
                $fn .= Template::replace(['name' => $fun])->get($tem);
            }
        }

        $content = Template::theme($this->option('theme'))->get('helper');
        $content .= $fn;

        $path = _helpers("$name.php");
        if (File::exists($path)) {
            $this->danger("Helper '$name' already exists");
            $this->end();
        }

        $result = File::create($path, $content);
        if ($result !== false) {
            $this->success("Helper [$path] created successfully");
        }
    }
}
