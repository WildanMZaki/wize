<?php

namespace WildanMZaki\Wize\Commands;

use WildanMZaki\Wize\Command;
use WildanMZaki\Wize\File;

class Migrate extends Command
{
    protected $signature = 'migrate';
    protected $description = 'Migrate tool for migrating all your .sql files inside your database_path/migrations directory';

    protected $ci;
    protected $migration_table;

    public function run()
    {
        $this->ci = $this->ci_instance();

        if (!isset($this->ci->db)) {
            $this->ci->load->database();
        }

        $this->migration_table = $this->config('migration.table');
        $database_path = $this->config('paths.database');
        $directory = $this->unifyPath(_rootz("$database_path/migrations"));

        if (!is_dir($directory)) {
            $this->danger("Migration directory not found: [$directory]");
            $this->end();
        }

        $this->inform("Starting migrations from directory: [$directory]");

        $this->ensureMigrationsTable();

        $files = $this->loadMigrationFiles($directory);
        if (empty($files)) {
            $this->inform("No migration files found in: [$directory]");
            return;
        }

        sort($files);

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

                $this->ci->db->trans_start();

                foreach ($queries as $query) {
                    $this->ci->db->query($query);
                    $executed++;
                    $percentage = $this->percentage($executed, $total_queries);
                    $remaining = $total_queries - $executed;
                    $this->flash(("Process: $percentage% with total $remaining remaining " . ($remaining > 1 ? 'queries' : 'query')));
                }
                $this->flash('');

                $this->ci->db->trans_complete();

                if ($this->ci->db->trans_status() === false) {
                    throw new \Exception("Migration failed for file: $filename");
                }

                $this->recordMigration($filename, $batch);

                $done_time = microtime(true);
                $total_ms = round(($done_time - $start_time) * 1000, 0);
                $this->justify($this->label('DONE', 'blue') . ' Migrated successfully', $this->label(($total_ms . 'ms'), 'blue'));
                $executed_migration++;
            } catch (\Exception $e) {
                $this->justify($this->label('Failed', 'yellow'), '');
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

    protected function ensureMigrationsTable()
    {
        if (!$this->ci->db->table_exists($this->migration_table)) {
            $this->ci->db->query("
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
        sort($files); // Ensure consistent order
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
        $query = $this->ci->db->select('name')->get($this->migration_table);
        $result = $query->result_array();

        return array_column($result, 'name');
    }

    protected function getLatestBatch()
    {
        $query = $this->ci->db->select_max('batch')->get($this->migration_table);
        $result = $query->row();

        return $result->batch ?? 0;
    }

    protected function recordMigration($filename, $batch)
    {
        $this->ci->db->insert($this->migration_table, [
            'name' => $filename,
            'batch' => $batch,
        ]);
    }
}
