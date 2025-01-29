<?php

namespace WildanMZaki\Wize\Commands\Dev;

use WildanMZaki\Wize\Command;

class Debug extends Command
{
    protected $signature = 'dev:debug {method}';
    protected $description = 'Debugging utilities for development';

    public function run()
    {
        $method = $this->argument('method');
        if (!$method) {
            $method = $this->ask('Tell me what debug method do you want to execute!');
        }

        if (method_exists($this, $method)) {
            $this->$method();
        } else {
            $this->danger("Debug method '$method' does not exist.");
            $this->listAvailableMethods();
        }
    }

    private function listAvailableMethods()
    {
        $methods = array_filter(get_class_methods($this), function ($method) {
            return strpos($method, 'test_') === 0 || strpos($method, 'get_') === 0;
        });

        $this->say('Available debug methods:', $this->ansiColors['yellow']);
        foreach ($methods as $method) {
            $this->say(" - $method");
        }
        $this->ln();
    }

    private function test_confirm()
    {
        $response = $this->confirm("Are you sure you want to proceed?");
        if ($response) {
            $this->success("You selected Yes.");
        } else {
            $this->danger("You selected No.");
        }
    }

    private function get_ci_instance()
    {
        $this->bootstrap_ci();
        $ci = &$this->ci;

        $item = $ci->config->item('language');

        $this->debug('Config language: ' . $item);
    }

    private function test_colors()
    {
        for ($i = 30; $i <= 37; $i++) {
            echo "\033[" . $i . "mColor $i\033[0m\n";
        }
        $this->say($this->label('Test Label: Cyan', 'cyan'));
        $this->inform('Testing the inform function.');
    }

    private function test_justify()
    {
        $this->justify('Left Label', 'Right Label');
        $this->justify('Processing', $this->label('Done!', 'green'), '.');
        $this->justify('Migration', $this->label('Failed', 'red'), '-');
    }

    private function test_dynamic_action()
    {
        $this->dynamicAction('Simulating long-running task', function () {
            sleep(3); // Simulate a task taking time
        });
    }

    private function test_loading()
    {
        $this->say("Starting loading animation...");
        $this->dynamicAction("Loading something", function () {
            sleep(2); // Simulate a task taking time
        });
    }

    private function test_environment()
    {
        $env = $this->config('env');
        $this->inform("Current environment is: $env");
        if ($env !== 'development') {
            $this->danger("You are not in the development environment.");
        }
    }

    private function test_input_output()
    {
        $name = $this->ask("What's your name?");
        $this->inform("Hello, $name!");
    }
}
