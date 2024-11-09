<?php

namespace WildanMZaki\Wize\Commands\Create;

use WildanMZaki\Wize\Command;

class Module extends Command
{
    protected $signature = 'create:module {name}
        {--theme=default : Theme is template you want to use}
        {-nc : Don\'t create the controller}
        {-nm : Don\'t create the model}
    ';

    protected $description = 'Help you create a module';

    public function run()
    {
        if (!$this->config('module')) {
            $this->danger('Your config say, that you are not using modularized codeigniter');
            $this->end();
        }
        $name = $this->argument('name');
        $theme = $this->option('theme');

        if (!$name) {
            $name = $this->ask('What is your module name?');
        }

        $options = [
            'module' => $name,
            'theme' => $theme,
        ];

        // Controller
        if (!$this->option('nc')) {
            $controller = new Controller(ucfirst($name), $options);
            $controller->setConfigs($this->configs);
            $controller->run();
            $this->ln();
        }

        if (!$this->option('nm')) {
            $possible_model = "M" . strtolower($name);
            $model = new Model($possible_model, $options);
            $model->setConfigs($this->configs);
            $model->run();
            $this->ln();
        }

        $this->success("Module $name created successfully");
    }
}
