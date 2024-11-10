<?php

namespace WildanMZaki\Wize;

use Exception;

// By: Wildan M Zaki, visit: https://github.com/WildanMZaki
// At: Mar 20, 2024

trait Util
{
    protected
        $ansiColors = [
            'reset' => "\033[0m",
            'white' => "\033[97m",
            'red' => "\033[31m",
            'green' => "\033[32m",
            'yellow' => "\033[33m",
            'blue' => "\033[34m",
            'bg_red' => "\033[41m",
            'bg_green' => "\033[42m",
            'bg_yellow' => "\033[43m",
            'bg_blue' => "\033[44m",
            'bg_white' => "\033[47m",
        ];

    public function ln()
    {
        echo PHP_EOL;
    }

    public function end()
    {
        $this->ln();
        exit(0);
    }

    public function ensureDirectory(string $path, bool $useDirname = true)
    {
        $directory = $useDirname ? dirname($path) : $path;
        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
            $this->inform("$directory directory created");
        }
    }

    public function pascalize(string $word, string $separator = '-', string $glue = ''): string
    {
        $words = explode($separator, $word);
        $words = array_map('ucfirst', $words);
        return implode($glue, $words);
    }

    public function normalize(string $word)
    {
        $word = preg_replace('/^\d+/', '', $word);
        $word = preg_replace('/[^a-zA-Z0-9]+/', '_', $word);
        return $word;
    }


    public function ask($question = 'Am i handsome?', $default = null)
    {
        $q = $question . ($default ? " [$default]" : '');
        $this->say($q, $this->ansiColors['green']);
        echo "> ";
        $answer = trim(fgets(STDIN));
        if (!$answer) {
            if (!$default) {
                $this->danger("Operation canceled!");
                $this->end();
            } else {
                $answer = $default;
            }
        }
        $this->ln();
        return $answer;
    }

    public function label($txt, $c = 'red')
    {
        return $this->ansiColors["bg_$c"] . " $txt " . $this->ansiColors['reset'];
    }

    public function inform($message = "Information", $bgColored = true)
    {
        $color = $bgColored ? '' : $this->ansiColors['blue'];
        $message = !$bgColored ? "Info: $message" : ($this->label('Info:', 'blue') . " $message");
        $this->say($message, $color);
    }

    public function warning($message = "Warning", $bgColored = true)
    {
        $color = $bgColored ? '' : $this->ansiColors['yellow'];
        $message = !$bgColored ? "Warning: $message" : ($this->label('Warning:', 'yellow') . " $message");
        $this->say($message, $color);
    }

    public function success($message = "Success", $bgColored = true)
    {
        $color = $bgColored ? '' : $this->ansiColors['green'];
        $message = !$bgColored ? "Success: $message" : ($this->label('Success:', 'green') . " $message");
        $this->say($message, $color);
    }

    public function danger($message = "Some Error happened", $bgColored = true)
    {
        $color = $bgColored ? '' : $this->ansiColors['red'];
        $message = !$bgColored ? "Error: $message" : ($this->label('Error:') . " $message");
        $this->say($message, $color);
    }

    public function colorize($text, $color = null)
    {
        if (is_null($color)) {
            return $text;
        } else {
            if (!in_array($color, array_keys($this->ansiColors))) throw new Exception("Unknown color: $color");
            $ansi = $this->ansiColors[$color];
            return ($ansi . $text . $this->ansiColors['reset']);
        }
    }

    public function say($message, $color = null)
    {
        if (is_null($color)) {
            echo " $message" . PHP_EOL;
            return;
        }
        echo $color . $message . $this->ansiColors['reset'] . PHP_EOL;
    }

    public function flash(string $message): void
    {
        echo "\r$message";
        flush();
    }
}
