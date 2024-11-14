<?php

namespace WildanMZaki\Wize\Commands\Create;

use WildanMZaki\Wize\Command;
use WildanMZaki\Wize\File;
use WildanMZaki\Wize\Template;

class Controller extends Command
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

    protected $signature = 'create:controller {name}
        {--module= : Specify module you want to use to create controllers}
        {--theme=default : Theme is template you want to use}
        {-m : Automatically create the related model}
    ';

    protected $description = 'Help you create a controller';

    public function run()
    {
        $name = $this->name ?? $this->argument('name');
        if (!$name) {
            $name = $this->ask('What is your controller name?');
        }

        $modularized = $this->config('module');
        if (!$modularized) {
            $path = _controllers("$name.php");
            $module = $name;
        } else {
            $module = null;
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
                $this->ln();
                $module = $this->ask('Which module do you want to use?');
            }
            $path = _moduleControllers($module, "$name.php");
        }

        if (File::exists($path)) {
            $this->danger("Controller '$name' already exists");
            $this->end();
        }

        $theme = $this->theme ?? $this->option('theme');
        $content = Template::theme($theme)->replace([
            'name' => $name,
            'module' => strtolower($module),
        ])->get('controller');

        $this->ensureDirectory($path);
        $result = File::create($path, $content);
        if ($result !== false) {
            $this->success("Controller [$path] created successfully");

            if ($this->option('m')) {
                $this->ln();
                $possible_name = "M" . strtolower($name);
                $model = new Model($possible_name, [
                    'module' => $module,
                    'theme' => $theme,
                ]);
                $model->setConfigs($this->configs);
                $model->run();
            }
        }
    }
}
