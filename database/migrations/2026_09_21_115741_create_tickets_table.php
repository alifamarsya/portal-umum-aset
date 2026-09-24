<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_tiket')->unique();                          // REQ-YYYYMMDD-XXXX
            $table->string('judul');
            $table->text('deskripsi');
            $table->foreignId('kategori_id')->constrained('ticket_categories');
            $table->foreignId('pemohon_id')->constrained('users');            // User yang mengajukan
            $table->foreignId('department_id')->nullable()->constrained('internal_departments')->nullOnDelete();
            $table->foreignId('operator_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('kabag_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', [
                'Menunggu Verifikasi',
                'Dialokasikan',
                'Dalam Proses',
                'Selesai',
                'Ditolak',
                'Ditutup',
            ])->default('Menunggu Verifikasi');
            $table->text('alasan_penolakan')->nullable();
            $table->string('attachment')->nullable();                         // Path file lampiran
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};