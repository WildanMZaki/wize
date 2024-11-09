<?php

namespace WildanMZaki\Wize\Commands\Create;

use WildanMZaki\Wize\Command;
use WildanMZaki\Wize\File;
use WildanMZaki\Wize\Template;

class Model extends Command
{
    protected $signature = 'create:model {name}
        {--module= : Specify module you want to use to create the model}
        {--theme=default : Theme is template you want to use for the model}
    ';
    protected $description = 'Help you create a model';

    public function run()
    {
        $name = $this->argument('name');
        if (!$name) {
            $name = $this->ask('What is your model name?');
        }


        $module = $this->config('module');
        if (!$module) {
            $path = _models("$name.php");
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
                $this->warning('Undefined Module');
                $module = $this->ask('Which module do you want to use?');
            }
            $path = _moduleModels($module, "$name.php");
        }

        if (File::exists($path)) {
            $this->danger("Model '$name' already exists");
            $this->end();
        }

        $content = Template::theme($this->option('theme'))->replace([
            'name' => $name,
            'module' => strtolower($module),
        ])->get('model');

        $this->ensureDirectory($path);
        $result = File::create($path, $content);
        if ($result !== false) {
            $this->success("Model '$name' created successfully");
            $this->end();
        }
    }
}
