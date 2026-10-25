<?php
namespace App\Http\Controllers;
class BackupController extends Controller {
    public function download() {
        $db = config('database.connections.sqlite.database');
        if (config('database.default') === 'sqlite' && $db && file_exists($db)) {
            return response()->download($db, 'khatapos-backup-' . date('Y-m-d') . '.sqlite');
        }
        return back()->withErrors(['backup' => 'Backup download is available for the SQLite demo database. For MySQL, use mysqldump (see README).']);
    }
}
