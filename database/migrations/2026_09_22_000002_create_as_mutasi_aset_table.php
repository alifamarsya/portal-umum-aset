<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('as_mutasi_aset')) {
            Schema::create('as_mutasi_aset', function (Blueprint $table) {
                $table->id();
                $table->string('no_mutasi')->nullable();
                $table->foreignId('aset_id')->constrained('as_aset')->cascadeOnDelete();
                $table->string('dari_lokasi')->nullable();
                $table->string('ke_lokasi')->nullable();
                $table->string('dari_penanggung_jawab')->nullable();
                $table->string('ke_penanggung_jawab')->nullable();
                $table->text('alasan')->nullable();
                $table->string('status')->default('Selesai');
                $table->text('keterangan')->nullable();
                $table->string('dokumen')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('as_mutasi_aset');
    }
};
