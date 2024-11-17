<?php

namespace WildanMZaki\Wize\Commands\Migrate;

use WildanMZaki\Wize\Commands\Migrate;

class Status extends Migrate
{
    protected $signature = 'migrate:status
        {--conn=default : Database connection that you want to choose}
    ';
    protected $description = 'Show the status of migrations';

    // public function run()
    // {
    //     $this->bootstrap_ci();
    //     $connection = $this->getDBConnection();

    //     try {
    //         $this->conn = $this->ci->load->database($connection, true);
    //     } catch (\Exception $e) {
    //         $this->danger("Error loading database connection [$connection]: " . $e->getMessage());
    //         $this->end();
    //     }

    //     // Get configuration
    //     $this->migration_table = $this->config('migration.table');
    //     $database_path = $this->config('paths.database');
    //     $directory = $this->unifyPath(_rootz("$database_path/migrations"));

    //     if (!is_dir($directory)) {
    //         $this->danger("Migration directory not found: [$directory]");
    //         $this->end();
    //     }

    //     // Load migrations
    //     $this->say($this->colorize('Checking migration status:', 'blue'));
    //     if ($connection !== 'default') {
    //         $this->say($this->colorize('DB Conn:', 'green') . " `$connection`");
    //     }
    //     $this->say($this->colorize('DB Path:', 'green') . " $directory");

    //     $this->ensureMigrationsTable();
    //     $executedMigrations = $this->getExecutedMigrations();
    //     $allFiles = $this->loadMigrationFiles($directory);

    //     if (empty($allFiles)) {
    //         $this->inform("No migration files found in: [$directory]");
    //         return;
    //     }

    //     $this->ln();
    //     $this->say($this->colorize('Migration Status:', 'yellow'));

    //     $executed = [];
    //     $pending = [];

    //     foreach ($allFiles as $file) {
    //         $filename = basename($file);
    //         if (in_array($filename, $executedMigrations)) {
    //             $executed[] = $filename;
    //         } else {
    //             $pending[] = $filename;
    //         }
    //     }

    //     // Show executed migrations
    //     if (!empty($executed)) {
    //         $this->say($this->colorize('Executed Migrations:', 'green'));
    //         foreach ($executed as $migration) {
    //             $this->say(" - $migration");
    //         }
    //     } else {
    //         $this->inform('No migrations have been executed yet.');
    //     }

    //     $this->ln();

    //     // Show pending migrations
    //     if (!empty($pending)) {
    //         $this->say($this->colorize('Pending Migrations:', 'yellow'));
    //         foreach ($pending as $migration) {
    //             $this->say(" - $migration");
    //         }
    //     } else {
    //         $this->inform('All migrations are up to date.');
    //     }

    //     $this->ln();
    //     $this->success("Migration status check completed.");
    // }

    public function run()
    {
        $this->bootstrap_ci();
        $connection = $this->getDBConnection();

        try {
            $this->conn = $this->ci->load->database($connection, true);
        } catch (\Exception $e) {
            $this->danger("Error loading database connection [$connection]: " . $e->getMessage());
            $this->end();
        }

        // Get configuration
        $this->migration_table = $this->config('migration.table');
        $database_path = $this->config('paths.database');
        $directory = $this->unifyPath(_rootz("$database_path/migrations"));

        if (!is_dir($directory)) {
            $this->danger("Migration directory not found: [$directory]");
            $this->end();
        }

        // Load migrations
        $this->say($this->colorize('Checking migration status:', 'blue'));
        if ($connection !== 'default') {
            $this->say($this->colorize('DB Conn:', 'green') . " `$connection`");
        }
        $this->say($this->colorize('DB Path:', 'green') . " $directory");

        $executedMigrations = $this->getExecutionMigrations();
        $allFiles = $this->loadMigrationFiles($directory);

        if (empty($allFiles)) {
            $this->inform("No migration files found in: [$directory]");
            return;
        }

        $this->ln();
        $this->say($this->colorize('Migration Status:', 'blue'));

        $executed = [];
        $pending = [];

        foreach ($allFiles as $file) {
            $filename = basename($file);
            if (isset($executedMigrations[$filename])) {
                $executed[$filename] = $executedMigrations[$filename];
            } else {
                $pending[] = $filename;
            }
        }

        // Show executed migrations with details
        if (!empty($executed)) {
            $this->say($this->colorize('> Executed Migrations:', 'green'));
            $this->justify(('   ' . $this->label('Migration name:', 'blue')), $this->label('Executed At | Batch', 'blue'));
            foreach ($executed as $name => $details) {
                $executedAt = $details['executed_at'] ?? 'N/A';
                $batch = $details['batch'] ?? 'N/A';
                $this->justify(" - $name", ("$executedAt |" . $this->centerize($batch, 7)));
            }
        } else {
            $this->inform('No migrations have been executed yet.');
        }

        $this->ln();

        // Show pending migrations
        if (!empty($pending)) {
            $this->say($this->colorize('> Pending Migrations:', 'yellow'));
            foreach ($pending as $migration) {
                $this->justify(" - $migration", '');
            }
        } else {
            $this->inform('All migrations are up to date.');
        }

        $this->ln();
        $this->success("Migration status check completed.");
    }

    /**
     * Get executed migrations with details.
     */
    protected function getExecutionMigrations(): array
    {
        $query = $this->conn->select('name, executed_at, batch')->get($this->migration_table);
        $result = $query->result_array();

        $migrations = [];
        foreach ($result as $row) {
            $migrations[$row['name']] = [
                'executed_at' => $row['executed_at'],
                'batch' => $row['batch']
            ];
        }

        return $migrations;
    }
}
