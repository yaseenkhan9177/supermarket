<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BackupController extends Controller
{
    /**
     * Export active tenant database as compressed SQL file (.sql.gz) for direct download.
     */
    public function download(Request $request)
    {
        $user = auth()->user();
        if (!$user) {
            abort(401, 'Unauthenticated');
        }

        $isAdmin = ($user->role === 'owner' || $user->role === 'admin')
            || (method_exists($user, 'hasAnyRole') && $user->hasAnyRole(['owner', 'admin', 'Store Admin', 'Owner']));

        if (!$isAdmin) {
            abort(403, 'Only store owners or administrators can download database backups.');
        }

        @set_time_limit(600);
        @ini_set('memory_limit', '512M');

        $dbName = DB::connection()->getDatabaseName();
        $tables = DB::select('SHOW TABLES');
        $tableKey = 'Tables_in_' . $dbName;

        $sql = "-- Database Backup for {$dbName}\n";
        $sql .= "-- Generated at: " . now()->toDateTimeString() . "\n\n";
        $sql .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

        foreach ($tables as $tObj) {
            $tableName = $tObj->$tableKey ?? current((array)$tObj);
            if (!$tableName) {
                continue;
            }

            // Structure
            $createRes = DB::select("SHOW CREATE TABLE `{$tableName}`");
            $createSql = $createRes[0]->{'Create Table'} ?? null;

            if ($createSql) {
                $sql .= "DROP TABLE IF EXISTS `{$tableName}`;\n";
                $sql .= $createSql . ";\n\n";
            }

            // Data
            $rows = DB::table($tableName)->get();
            if ($rows->count() > 0) {
                foreach ($rows->chunk(100) as $chunk) {
                    $insertValues = [];
                    foreach ($chunk as $row) {
                        $values = array_map(function ($val) {
                            if (is_null($val)) return 'NULL';
                            if (is_bool($val)) return $val ? '1' : '0';
                            if (is_numeric($val)) return $val;
                            return DB::connection()->getPdo()->quote($val);
                        }, (array)$row);

                        $insertValues[] = "(" . implode(", ", $values) . ")";
                    }
                    $sql .= "INSERT INTO `{$tableName}` VALUES \n" . implode(",\n", $insertValues) . ";\n";
                }
                $sql .= "\n";
            }
        }

        $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";

        $compressed = gzencode($sql, 9);
        $fileName = 'backup_' . preg_replace('/[^a-zA-Z0-9_]/', '_', $dbName) . '_' . date('Y_m_d_His') . '.sql.gz';

        AuditLog::record(
            'database_backup',
            "Downloaded database backup file: {$fileName}",
            'System',
            null,
            ['database' => $dbName, 'filename' => $fileName]
        );

        return response()->streamDownload(function () use ($compressed) {
            echo $compressed;
        }, $fileName, [
            'Content-Type'        => 'application/gzip',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ]);
    }
}
