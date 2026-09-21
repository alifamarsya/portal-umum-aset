<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->string('jenis_pengajuan', 50)->default('Permintaan')->after('deskripsi');
            $table->string('prioritas', 50)->default('Sedang')->after('jenis_pengajuan');
            $table->unsignedInteger('sla_jam')->default(48)->after('prioritas');
            $table->timestamp('sla_due_at')->nullable()->after('sla_jam');
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn(['jenis_pengajuan', 'prioritas', 'sla_jam', 'sla_due_at']);
        });
    }
};