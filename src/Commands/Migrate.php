<?php

namespace WildanMZaki\Wize\Commands;

use WildanMZaki\Wize\Command;
use WildanMZaki\Wize\File;

class Migrate extends Command
{
    protected $signature = 'migrate
        {--conn=default : Database connection that you want to choose}
        {-f : Force running in any environment}
    ';
    protected $description = 'Import all .sql files in your database_path/migrations directory';

    protected $conn;
    protected $migration_table;

    public function run()
    {
        $env = $this->config('env');
        if (strtolower($env) != 'development' && !$this->option('f')) {
            if (!$this->confirm("You are not in a development environment. Do you want to proceed?", 'n')) {
                return;
            }
        }

        $this->bootstrap_ci();
        $connection = $this->getDBConnection();

        try {
            $this->conn = $this->ci->load->database($connection, TRUE);
        } catch (\Exception $e) {
            $this->danger("Error loading database connection [$connection]: " . $e->getMessage());
            $this->end();
        }

        $this->migration_table = $this->config('migration.table');
        $database_path = $this->config('paths.database');
        $directory = $this->unifyPath(_rootz("$database_path/migrations"));

        if (!is_dir($directory)) {
            $this->danger("Migration directory not found: [$directory]");
            $this->end();
        }

        $this->say($this->colorize('Starting migrations:', 'blue'));
        if ($connection != 'default') {
            $this->say(($this->colorize('DB Conn:', 'green') . " `$connection`"));
        }
        $this->say(($this->colorize('DB Path:', 'green') . " $directory"));

        $this->ensureMigrationsTable();

        $files = $this->loadMigrationFiles($directory);
        if (empty($files)) {
            $this->inform("No migration files found in: [$directory]");
            return;
        }

        $executedMigrations = $this->getExecutedMigrations();
        $batch = $this->getLatestBatch() + 1;

        $executed_migration = 0;
        foreach ($files as $file) {
            $filename = basename($file);

            if (in_array($filename, $executedMigrations)) {
                continue;
            }

            try {
                if (!File::exists($file)) {
                    throw new \Exception("Failed to read the migration file: $filename");
                }

                $queries = $this->loadQueries($file);
                $total_queries = count($queries);
                $executed = 0;
                $start_time = microtime(true);;
                $this->ln();
                $this->justify($filename, ("$total_queries " . ($total_queries > 1 ? 'queries' : 'query')));

                $this->conn->trans_start();

                $flash_message = '';
                foreach ($queries as $query) {
                    $this->conn->query($query);
                    $executed++;
                    $percentage = $this->percentage($executed, $total_queries);
                    $remaining = $total_queries - $executed;
                    $flash_message = "Process: $percentage% with total $remaining remaining " . ($remaining > 1 ? 'queries' : 'query');
                    $this->flash($flash_message);
                }
                $flash_message = '';
                $this->flash($flash_message);

                $this->conn->trans_complete();

                if ($this->conn->trans_status() === false) {
                    throw new \Exception("Migration failed for file: $filename");
                }

                $this->recordMigration($filename, $batch);

                $done_time = microtime(true);
                $total_ms = round(($done_time - $start_time) * 1000, 0);
                $this->justify($this->label('DONE', 'blue') . ' Migrated successfully', $this->label(($total_ms . 'ms'), 'blue'));
                $executed_migration++;
            } catch (\Exception $e) {
                $this->flash('');
                $this->justify($flash_message, $this->label('Failed', 'yellow'));
                $this->ln();
                $this->danger($e->getMessage());
                $this->end();
            }
        }

        $this->ln();
        if ($executed_migration) {
            $this->success("All migrations completed.");
        } else {
            $this->inform('Nothing to migrate');
        }
    }

    protected function getDBConnection()
    {
        global $argv;

        $defaultConnection = $this->config('migration.connection') ?? 'default';
        $optionConnection = $this->option('conn');

        // Check if --conn exists explicitly in $argv
        $isConnExplicit = false;
        foreach ($argv as $arg) {
            if (strpos($arg, '--conn=') === 0) {
                $isConnExplicit = true;
                break;
            }
        }

        // Use the optionConnection if explicitly passed, otherwise fallback to the default
        return ($isConnExplicit && $optionConnection) ? $optionConnection : $defaultConnection;
    }


    protected function ensureMigrationsTable()
    {
        if (!$this->conn->table_exists($this->migration_table)) {
            $this->conn->query("
                CREATE TABLE {$this->migration_table} (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    name VARCHAR(255) NOT NULL,
                    executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    batch INT NOT NULL
                )
            ");
            $this->inform("Migrations table created successfully.");
        }
    }

    protected function loadMigrationFiles(string $directory): array
    {
        if (substr($directory, -1) !== DIRECTORY_SEPARATOR) {
            $directory .= DIRECTORY_SEPARATOR;
        }

        $files = glob("$directory*.sql");
        // sort($files); // Ensure consistent order
        usort($files, function ($a, $b) {
            $nameA = basename($a);
            $nameB = basename($b);

            if (str_starts_with($nameA, '_') && !str_starts_with($nameB, '_')) {
                return -1;
            }
            if (!str_starts_with($nameA, '_') && str_starts_with($nameB, '_')) {
                return 1;
            }

            // Default sorting (numerically)
            return strcmp($nameA, $nameB);
        });
        return $files;
    }

    protected function loadQueries(string $filePath): array
    {
        $queries = [];
        $templine = '';
        $inMultiLineComment = false;

        $lines = File::lines($filePath);
        foreach ($lines as $line) {
            $trimmedLine = trim($line);

            if (strpos($trimmedLine, '/*') === 0) {
                $inMultiLineComment = true;
            }

            if ($inMultiLineComment) {
                // Check if this line ends the multi-line comment
                if (strpos($trimmedLine, '*/') !== false) {
                    $inMultiLineComment = false;
                }
                continue;
            }

            // Skip single-line comments and empty lines
            if (substr($trimmedLine, 0, 2) === '--' || $trimmedLine === '') {
                continue;
            }

            // Accumulate the query
            $templine .= $line;

            // If the line ends with a semicolon, it marks the end of a query
            if (substr(rtrim($line), -1) === ';') {
                $queries[] = trim($templine); // Add the complete query
                $templine = ''; // Reset the line accumulator
            }
        }

        return $queries;
    }


    protected function getExecutedMigrations()
    {
        $query = $this->conn->select('name')->get($this->migration_table);
        $result = $query->result_array();

        return array_column($result, 'name');
    }

    protected function getLatestBatch()
    {
        $query = $this->conn->select_max('batch')->get($this->migration_table);
        $result = $query->row();

        return $result->batch ?? 0;
    }

    protected function recordMigration($filename, $batch)
    {
        $this->conn->insert($this->migration_table, [
            'name' => $filename,
            'batch' => $batch,
        ]);
    }
}
