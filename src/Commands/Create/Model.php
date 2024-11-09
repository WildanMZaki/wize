<?php

namespace WildanMZaki\Wize\Commands\Create;

use WildanMZaki\Wize\Command;
use WildanMZaki\Wize\File;
use WildanMZaki\Wize\Template;

class Model extends Command
{
    protected $name;
    protected $module;
    protected $theme;

    public function __construct($name = null, $options = [])
    {
        parent::__construct();

        $defaults = [
            'module' => null,
            'theme' => 'default',
        ];
        $options = array_merge($defaults, $options);

        $this->name = $name;
        $this->module = $options['module'];
        $this->theme = $options['theme'];
    }

    protected $signature = 'create:model {name}
        {--module= : Specify module you want to use to create the model}
        {--theme=default : Theme is template you want to use for the model}
    ';

    protected $description = 'Help you create a model';

    public function run()
    {
        $name = $this->name ?? $this->argument('name');
        if (!$name) {
            $name = $this->ask('What is your model name?');
        }

        $modularized = $this->config('module');
        if (!$modularized) {
            $path = _models("$name.php");
            $module = $name;
        } else {
            if ($this->module) {
                $module = $this->module;
            } else if ($this->option('module')) {
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

        $theme = $this->theme ?? $this->option('theme');
        $content = Template::theme($theme)->replace([
            'name' => $name,
            'module' => strtolower($module),
        ])->get('model');

        $this->ensureDirectory($path);
        $result = File::create($path, $content);
        if ($result !== false) {
            $this->success("Model '$name' created successfully");
        }
    }
}
