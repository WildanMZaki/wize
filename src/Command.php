<?php

namespace WildanMZaki\Wize;

use CI_Cli;

abstract class Command
{
    use Util;
    protected $caller = 'wize';

    protected $signature;
    protected $description;

    protected $_arguments = [];
    protected $_options = [];
    protected $configs = [];

    protected $default_options = [];

    protected $argumentDescriptions = [];
    protected $optionDescriptions = [];

    // Instance storage
    protected $ci;

    public function __construct()
    {
        $this->parseSignature();
    }

    public function setCaller($caller): void
    {
        $this->caller = $caller;
    }

    public function setConfigs(array $configs = []): void
    {
        $this->configs = [...$this->configs, ...$configs];
    }

    // public function config(string $key): mixed
    // {
    //     return $this->configs[$key] ?? null;
    // }

    public function config(string $key): mixed
    {
        $keys = explode('.', $key);
        $value = $this->configs;

        foreach ($keys as $keyPart) {
            if (is_array($value) && array_key_exists($keyPart, $value)) {
                $value = $value[$keyPart];
            } else {
                return null;
            }
        }

        return $value;
    }


    public function cmd(): string
    {
        preg_match('/^[^\s]+/', $this->signature, $matches);
        return $matches[0] ?? '';
    }

    public function sign(): string
    {
        return $this->signature ?? '';
    }

    public function desc(): string
    {
        return $this->description ?? '';
    }

    public function argument(string $key): ?string
    {
        return $this->_arguments[$key] ?? null;
    }

    public function option(string $key): ?string
    {
        return $this->_options[$key] ?? null;
    }

    // Targetted to be refactored next update, required for make the code cleaner when need to call another command inside command
    protected function prepareSubcommand(Command $command)
    {
        $command->setConfigs($this->configs);
        $command->_options = $this->_options;
        $command->ci = $this->ci;
    }

    protected function parseSignature()
    {
        preg_match_all('/\{([^}]+)\}/', $this->signature, $matches);
        foreach ($matches[1] as $param) {
            if (strpos($param, '--') === 0 || strpos($param, '-') === 0) {
                // It's an option
                $uniqReplacer = '_' . uniqid() . '_';
                $isLong = isset($string[1]) && $string[1] === '-';
                $param = ltrim($param, '-');
                $param = str_replace('-', $uniqReplacer, $param);
                $param = ($isLong ? '--' : '-') . $param;
                preg_match('/(-\w+)?(--\w+)?(=(\w+)?)?( : (.*))?/', $param, $optMatches);
                $short = $optMatches[1] ?? null;
                $long = $optMatches[2] ?? null;
                $name = $long ? ltrim($long, '--') : ltrim($short, '-');
                $name = str_replace($uniqReplacer, '-', $name);
                $default = isset($optMatches[4]) ? ($optMatches[4] === '' ? null : $optMatches[4]) : false;
                $description = $optMatches[6] ?? '';

                $this->_options[$name] = $default;
                $this->optionDescriptions[$name] = $description;
                $this->default_options[] = $name;
            } else {
                // It's an argument
                preg_match('/(\w+)(\?)?( : (.*))?/', $param, $argMatches);
                $name = $argMatches[1];
                $description = $argMatches[4] ?? '';
                $this->_arguments[$name] = null; // No default value for arguments
                $this->argumentDescriptions[$name] = $description;
            }
        }
    }

    public function setArgsAndOptions(array $args)
    {
        $argIndex = 0;
        $argKeys = array_keys($this->_arguments);

        foreach ($args as $arg) {
            if (strpos($arg, '-') === 0) {
                // It's a long form option
                [$key, $value] = explode('=', ltrim($arg, '-'), 2) + [1 => true];
                $this->_options[$key] = $value;
            } else {
                // It's an argument
                if (isset($argKeys[$argIndex])) {
                    $this->_arguments[$argKeys[$argIndex]] = $arg;
                    $argIndex++;
                }
            }
        }

        if ($this->option('help') || $this->option('h')) $this->showHelp();
    }

    public function showHelp()
    {
        $this->say($this->label('Command:', 'blue') . " {$this->cmd()} : {$this->desc()}");
        $this->ln();
        $usage = $this->label('Usage:', 'blue') . " php {$this->caller} {$this->cmd()}";
        $args = array_keys($this->_arguments);
        foreach ($args as $arg) {
            $usage .= " [$arg]";
        }
        foreach ($this->_options as $key => $value) {
            if (in_array($key, $this->default_options)) {
                $pref = strlen($key) === 1 ? '-' : '--';
                $usage .= " {$pref}{$key}";
                $usage .= ($value) ? "=$value" : " (true)";
            }
        }

        $this->say($usage);
        $this->helpArguments();
        $this->helpOptions();
        $this->end();
    }

    public function helpArguments()
    {
        $keys = array_keys($this->_arguments);
        if (!empty($keys)) {
            $this->ln();
            $this->say('Arguments:');
            $arguments = [];
            $max = 0;
            foreach ($keys as $arg) {
                $description = $this->argumentDescriptions[$arg] ?? '';
                $argument = " - $arg";
                $max = max($max, strlen($argument));
                $arguments[] = [$argument, $description];
            }

            $max++;
            foreach ($arguments as [$arg, $desc]) {
                $this->say(str_pad($arg, $max) . ($desc ? " : $desc" : ""));
            }
        }
    }
    public function helpOptions()
    {
        if (!empty($this->default_options)) {
            $this->ln();
            $this->say('Options:');
            $options = [];
            $max = 0;

            foreach ($this->_options as $opt => $value) {
                if (in_array($opt, $this->default_options)) {
                    $description = $this->optionDescriptions[$opt] ?? '';
                    $prefix = strlen($opt) === 1 ? '-' : '--';
                    $option = " {$prefix}{$opt}";
                    $option .= $value ? "={$value}" : (($value === false) ? '=false' : '');
                    $max = max($max, strlen($option));
                    $options[] = [$option, $description];
                }
            }

            $max++;
            foreach ($options as [$opt, $desc]) {
                $this->say(str_pad($opt, $max) . ($desc ? " : $desc" : ""));
            }
        }
    }

    public function bootstrap_ci()
    {
        try {
            if (isset($this->ci) && $this->ci instanceof CI_Cli) {
                return;
            }

            if (!defined('ENVIRONMENT')) {
                define('ENVIRONMENT', $this->config('env'));
            }

            if (!defined('FCPATH')) {
                define('FCPATH', _rootz($this->config('paths.root')));
            }

            if (!defined('APPPATH')) {
                define('APPPATH', _rootz($this->config('paths.application')) . DIRECTORY_SEPARATOR);
            }

            if (!defined('BASEPATH')) {
                define('BASEPATH', _rootz($this->config('paths.system')) . DIRECTORY_SEPARATOR);
            }

            if (!defined('VIEWPATH')) {
                $views_path = str_replace('/', DIRECTORY_SEPARATOR, $this->config('paths.views'));
                define('VIEWPATH', _rootz($views_path) . DIRECTORY_SEPARATOR);
            }

            if (!defined('BOOTSTRAP')) {
                define('BOOTSTRAP', true);
                require_once __DIR__ . '/bootstrap.php';
            }

            $this->ci = new CI_Cli();
        } catch (\Exception $er) {
            $this->danger($er->getMessage());
            $this->end();
        }
    }

    abstract public function run();
}
