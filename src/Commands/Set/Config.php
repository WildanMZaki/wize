<?php

namespace WildanMZaki\Wize\Commands\Set;

use WildanMZaki\Wize\Command;
use WildanMZaki\Wize\File;

class Config extends Command
{
    protected $signature = 'set:config {key} {value}';
    protected $description = 'Set and configure this cli tool';

    public function run()
    {
        $key = $this->argument('key');
        // Validate and convert boolean values
        $value = $this->convert($this->argument('value'));

        // Start at the top level of configs
        $configRef = &$this->configs;
        $fragments = '';
        $keys = explode('.', $key);

        foreach ($keys as $i => $fragment) {
            $fragments .= ($i > 0 ? '.' : '') . $fragment;

            if (is_numeric($fragment)) {
                $fragment = (int) $fragment; // Convert to integer for array indexing
            }

            if (!isset($configRef[$fragment])) {
                // Just give error if the requested key doesn't exists
                $this->danger("Key '$fragments' is undefined in the config");
                $this->end();
            } else {
                if ($i === count($keys) - 1) {
                    $configRef[$fragment] = $value;
                } else {
                    // Navigate deeper into the config structure
                    $configRef = &$configRef[$fragment];
                }
            }
        }

        $this->setConfigs($this->configs);

        // Write changes to the config file
        if (File::create(_rootz('/' . WIZE_CONFIG), $this->configs)) {
            $this->success('Config updated');
            $this->end();
        }
    }


    protected function convert($value)
    {
        if (strtolower($value) === 'true') {
            return true;
        } elseif (strtolower($value) === 'false') {
            return false;
        }

        return $value;
    }
}
