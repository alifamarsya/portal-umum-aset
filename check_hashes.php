<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
use App\Models\AuditLog;
$prev = '';
foreach (AuditLog::orderBy('id')->get() as $l) {
    $h = hash('sha256', $l->user_id . $l->aksi . $l->modul . $l->entitas . $l->keterangan . $prev . $l->created_at);
    if ($l->id >= 44) {
        echo "ID {$l->id}: computed={$h}, in_db={$l->hash}, match=" . ($h === $l->hash ? 'YES' : 'NO') . PHP_EOL;
    }
    $prev = $l->hash;
}