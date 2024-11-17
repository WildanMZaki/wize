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
            'blue' => "\033[34m",
            'magenta' => "\033[35m",
            'cyan' => "\033[36m",
            'yellow' => "\033[33m",

            'bg_white' => "\033[47m",
            'bg_red' => "\033[41m",
            'bg_green' => "\033[42m",
            'bg_blue' => "\033[44m",
            'bg_magenta' => "\033[45m",
            'bg_cyan' => "\033[46m",
            'bg_yellow' => "\033[43m",
        ];

    public function ln()
    {
        echo PHP_EOL;
    }

    public function end($code = 0)
    {
        $this->ln();
        exit($code);
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

    public function confirm(string $question, ?string $default = 'y'): bool
    {
        $default = strtolower($default);
        if (!in_array($default, ['y', 'n'])) {
            throw new \InvalidArgumentException("Default must be 'y' or 'n'.");
        }

        $defaultPrompt = $default === 'y' ? '[Y/n]' : '[y/N]';
        $this->say("{$question} {$defaultPrompt}", $this->ansiColors['yellow']);
        echo "> ";
        $answer = trim(fgets(STDIN));

        $answer = ($answer === '') ? $default : strtolower($answer);

        if (in_array($answer, ['y', 'yes'])) {
            return true; // Confirmed
        } elseif (in_array($answer, ['n', 'no'])) {
            return false; // Denied
        }

        // If input is invalid, re-prompt
        $this->ln();
        $this->danger("Invalid input. Please enter 'y' or 'n'.");
        $this->ln();
        return $this->confirm($question, $default); // Recursive call to re-prompt
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

    public function debug($message = "Debugging..... hufft", $bgColored = true)
    {
        $color = $bgColored ? '' : $this->ansiColors['magenta'];
        $message = !$bgColored ? "Error: $message" : ($this->label('Debug:', 'magenta') . " $message");
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
            $startsWithColor = preg_match('/^\033\[[0-9;]*m/', $message);
            echo ($startsWithColor ? '' : ' ') . $message . PHP_EOL;
            return;
        }
        echo $color . $message . $this->ansiColors['reset'] . PHP_EOL;
    }

    public function flash(string $message): void
    {
        echo "\r$message";
        flush();
    }

    public function unifyPath(string $path): string
    {
        return str_replace('/', DIRECTORY_SEPARATOR, $path);
    }

    public function justify(string $left, string $right, string $fillChar = '.'): void
    {
        $terminalWidth = (int)shell_exec('tput cols') ?: 80;

        $visibleLeftLength = $this->stripAnsiLength($left);
        $visibleRightLength = $this->stripAnsiLength($right);

        // Calculate fill length
        $fillLength = $terminalWidth - $visibleLeftLength - $visibleRightLength - 20; // -2 for spaces
        $fillLength = max($fillLength, 0); // Ensure it's not negative

        // Create the line with fill characters
        $fill = str_repeat($fillChar, $fillLength);
        echo "$left $fill $right" . PHP_EOL;
    }

    // Utility function to calculate visible length (excluding ANSI codes)
    protected function stripAnsiLength(string $text): int
    {
        $ansiRegex = '/\033\[[0-9;]*m/';
        return strlen(preg_replace($ansiRegex, '', $text));
    }


    public function percentage(int $current, int $total, int $decimals = 0): float
    {
        if ($total <= 0) {
            return 0.0;
        }

        $percentage = ($current / $total) * 100;
        return round($percentage, $decimals);
    }
}
