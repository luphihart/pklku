<?php

namespace App\Modules\System\Services;

use App\Modules\System\Repositories\AuditLogRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SystemService
{
    protected $repo;

    public function __construct(AuditLogRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function getLogs() { return $this->repo->getPaginatedLogs(); }
    public function wipeLogs() { return $this->repo->clearLogs(); }

    /**
     * Generate pure PHP MySQL backup (cPanel safe, doesn't need binary tools like mysqldump).
     */
    public function generateBackupSql(): string
    {
        $tables = array_map('current', DB::select('SHOW TABLES'));
        $sql = "-- SI PKL SMK Database Backup\n";
        $sql .= "-- Generated: " . now()->format('Y-m-d H:i:s') . "\n";
        $sql .= "-- --------------------------------------------------------\n\n";

        // Disable foreign key checks during import
        $sql .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

        foreach ($tables as $table) {
            // Get create table query
            $createTable = DB::select("SHOW CREATE TABLE `{$table}`");
            $createStatement = ((array)$createTable[0])['Create Table'];

            $sql .= "-- Table structure for table `{$table}`\n";
            $sql .= "DROP TABLE IF EXISTS `{$table}`;\n";
            $sql .= $createStatement . ";\n\n";

            // Get table data
            $rows = DB::table($table)->get();
            if ($rows->count() > 0) {
                $sql .= "-- Dumping data for table `{$table}`\n";
                foreach ($rows as $row) {
                    $rowArray = (array)$row;
                    $keys = array_map(function($key) { return "`{$key}`"; }, array_keys($rowArray));
                    
                    $values = array_map(function($value) {
                        if (is_null($value)) {
                            return 'NULL';
                        }
                        // Escape string safely
                        return "'" . str_replace(["\\", "'", "\r", "\n"], ["\\\\", "\\'", "\\r", "\\n"], $value) . "'";
                    }, array_values($rowArray));

                    $sql .= "INSERT INTO `{$table}` (" . implode(', ', $keys) . ") VALUES (" . implode(', ', $values) . ");\n";
                }
                $sql .= "\n";
            }
        }

        $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";

        return $sql;
    }

    /**
     * Restore database from uploaded SQL file.
     */
    public function restoreFromSql(string $filePath): void
    {
        $sqlContent = file_get_contents($filePath);
        if (!$sqlContent) {
            throw new \Exception("File backup kosong atau tidak dapat dibaca.");
        }

        DB::transaction(function() use ($sqlContent) {
            // Temporary disable foreign keys to prevent drop order violations
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
            
            // Clean comments and execute raw SQL statements
            DB::unprepared($sqlContent);
            
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        });
    }

    /**
     * Wipe entire database and seed basic configurations and admin account (fresh start).
     */
    public function wipeDatabase(): void
    {
        // 1. Wipe and re-migrate all tables
        \Illuminate\Support\Facades\Artisan::call('migrate:fresh', ['--force' => true]);

        // 2. Seed basic settings configuration
        \Illuminate\Support\Facades\Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\SettingSeeder',
            '--force' => true
        ]);

        // 3. Create default Administrator account
        \App\Models\User::create([
            'name' => 'Administrator PKL',
            'email' => 'admin@pklsmk.sch.id',
            'password' => \Illuminate\Support\Facades\Hash::make('admin123'),
            'role' => 'admin',
            'phone' => '081234567890',
        ]);
    }
}
