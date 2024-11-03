<?php

namespace WildanMZaki\Wize\Commands\Create;

use WildanMZaki\Wize\Command;
use WildanMZaki\Wize\File;
use WildanMZaki\Wize\Template;

class Controller extends Command
{
    protected $signature = 'create:controller {name}
        {--module= : Specify module you want to use to create controllers}
        {--theme=default : Theme is template you want to use}
    ';

    protected $description = 'Help you create a controller';

    public function run()
    {
        $name = $this->argument('name');
        if (!$name) {
            $name = $this->ask('What is your controller name?');
        }


        $module = $this->config('module');
        if (!$module) {
            $path = _controllers("$name.php");
            $module = $name;
        } else {
            if ($this->option('module')) {
                $module = $this->option('module');
            } else {
                $nameParts = explode('/', $name);
                if (count($nameParts) > 1) {
                    $module = array_shift($nameParts);
                    $name = implode('/', $nameParts);
                }
            }
            if (!is_string($module)) {
                $this->warning('Unknown Module');
                $module = $this->ask('Which module do you want to use?');
            }
            $path = _moduleControllers($module, "$name.php");
        }

        if (File::exists($path)) {
            $this->danger("Controller '$name' already exists");
            $this->end();
        }

        $content = Template::theme($this->option('theme'))->replace([
            'name' => $name,
            'module' => strtolower($module),
        ])->get('controller');

        $this->ensureDirectory($path);
        $result = File::create($path, $content);
        if ($result !== false) {
            $this->success("Controller '$name' created successfully");
            $this->end();
        }
    }
}
