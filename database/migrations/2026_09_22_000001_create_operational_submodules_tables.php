<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Membuat tabel-tabel pencatatan operasional internal (tanpa maker-checker):
     * - Bagian Umum & RT:
     *   um_fasilitas_kantor, um_pemeliharaan_gedung, um_checklist_kebersihan, um_k3_insiden,
     *   dk_arsip_dokumen, dk_dokumen_legalitas
     * - Bagian Aset & Logistik:
     *   as_aset_histories, as_disposal_aset, as_rekonsiliasi_aset,
     *   as_penerimaan_barang, as_distribusi_barang, as_pembayaran_tagihan
     * - Bagian Pengadaan & Pemeliharaan:
     *   pm_jadwal_pemeliharaan, pm_monitoring_kondisi, pm_pengawasan_penggunaan,
     *   pm_tindak_lanjut_perbaikan, pm_perencanaan_kebutuhan
     */
    public function up(): void
    {
        // ── 1. Umum & RT ────────────────────────────────────────────────────────
        if (!Schema::hasTable('um_fasilitas_kantor')) {
            Schema::create('um_fasilitas_kantor', function (Blueprint $table) {
                $table->id();
                $table->string('nama_fasilitas');
                $table->string('kode')->nullable();
                $table->string('kategori')->nullable();
                $table->string('lokasi')->nullable();
                $table->string('kondisi')->nullable();
                $table->date('tanggal_perolehan')->nullable();
                $table->string('penanggung_jawab')->nullable();
                $table->text('keterangan')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('um_pemeliharaan_gedung')) {
            Schema::create('um_pemeliharaan_gedung', function (Blueprint $table) {
                $table->id();
                $table->string('jenis_pekerjaan');
                $table->text('uraian');
                $table->string('lokasi')->nullable();
                $table->date('tanggal_rencana')->nullable();
                $table->date('tanggal_realisasi')->nullable();
                $table->string('vendor')->nullable();
                $table->decimal('biaya', 15, 2)->nullable()->default(0);
                $table->string('status')->nullable()->default('Dijadwalkan');
                $table->string('dokumen')->nullable();
                $table->string('dibuat_oleh')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('um_checklist_kebersihan')) {
            Schema::create('um_checklist_kebersihan', function (Blueprint $table) {
                $table->id();
                $table->date('tanggal');
                $table->string('jenis');
                $table->string('area')->nullable();
                $table->string('petugas')->nullable();
                $table->string('status')->nullable()->default('Belum Dilakukan');
                $table->text('catatan')->nullable();
                $table->string('dibuat_oleh')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('um_k3_insiden')) {
            Schema::create('um_k3_insiden', function (Blueprint $table) {
                $table->id();
                $table->date('tanggal');
                $table->string('jenis');
                $table->string('lokasi')->nullable();
                $table->text('uraian');
                $table->string('korban')->nullable();
                $table->text('tindak_lanjut')->nullable();
                $table->string('status')->nullable()->default('Open');
                $table->string('dokumen')->nullable();
                $table->string('dibuat_oleh')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('dk_arsip_dokumen')) {
            Schema::create('dk_arsip_dokumen', function (Blueprint $table) {
                $table->id();
                $table->string('kode_arsip')->nullable();
                $table->string('judul');
                $table->string('jenis')->nullable();
                $table->string('kategori')->nullable();
                $table->date('tanggal_dokumen')->nullable();
                $table->string('lokasi_arsip')->nullable();
                $table->unsignedInteger('masa_retensi')->nullable();
                $table->string('status_arsip')->nullable()->default('Aktif');
                $table->text('keterangan')->nullable();
                $table->string('lampiran')->nullable();
                $table->string('dibuat_oleh')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('dk_dokumen_legalitas')) {
            Schema::create('dk_dokumen_legalitas', function (Blueprint $table) {
                $table->id();
                $table->string('nama_dokumen');
                $table->string('jenis')->nullable();
                $table->string('no_dokumen')->nullable();
                $table->string('penerbit')->nullable();
                $table->date('tanggal_terbit')->nullable();
                $table->date('tanggal_berlaku')->nullable();
                $table->string('status')->nullable()->default('Aktif');
                $table->text('keterangan')->nullable();
                $table->string('dokumen')->nullable();
                $table->timestamps();
            });
        }

        // ── 2. Aset & Logistik ──────────────────────────────────────────────────
        if (!Schema::hasTable('as_aset_histories')) {
            Schema::create('as_aset_histories', function (Blueprint $table) {
                $table->id();
                $table->foreignId('aset_id')->constrained('as_aset')->cascadeOnDelete();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('field_changed');
                $table->text('old_value')->nullable();
                $table->text('new_value')->nullable();
                $table->text('keterangan')->nullable();
                $table->timestamp('changed_at')->useCurrent();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('as_disposal_aset')) {
            Schema::create('as_disposal_aset', function (Blueprint $table) {
                $table->id();
                $table->string('no_disposal')->nullable();
                $table->foreignId('aset_id')->constrained('as_aset')->cascadeOnDelete();
                $table->date('tanggal_pengajuan')->nullable();
                $table->string('alasan_penghapusan')->nullable();
                $table->string('metode')->nullable();
                $table->decimal('nilai_buku_terakhir', 18, 2)->nullable()->default(0);
                $table->string('status')->default('Diajukan');
                $table->text('keterangan')->nullable();
                $table->string('dokumen')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('as_rekonsiliasi_aset')) {
            Schema::create('as_rekonsiliasi_aset', function (Blueprint $table) {
                $table->id();
                $table->string('no_rekonsiliasi')->nullable();
                $table->string('periode')->nullable();
                $table->date('tanggal')->nullable();
                $table->foreignId('aset_id')->nullable()->constrained('as_aset')->nullOnDelete();
                $table->string('jenis')->default('Rekonsiliasi');
                $table->string('kategori_awal')->nullable();
                $table->string('kategori_baru')->nullable();
                $table->string('kondisi_awal')->nullable();
                $table->string('kondisi_baru')->nullable();
                $table->text('hasil_rekonsiliasi')->nullable();
                $table->string('status')->default('Selesai');
                $table->string('petugas')->nullable();
                $table->string('dokumen')->nullable();
                $table->text('keterangan')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('as_penerimaan_barang')) {
            Schema::create('as_penerimaan_barang', function (Blueprint $table) {
                $table->id();
                $table->string('no_penerimaan')->nullable();
                $table->date('tanggal')->nullable();
                $table->string('vendor')->nullable();
                $table->string('nama_barang');
                $table->integer('jumlah')->default(1);
                $table->string('satuan')->nullable()->default('Unit');
                $table->string('kondisi')->default('Baik');
                $table->string('penerima')->nullable();
                $table->string('status')->default('Diterima');
                $table->string('dokumen')->nullable();
                $table->text('keterangan')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('as_distribusi_barang')) {
            Schema::create('as_distribusi_barang', function (Blueprint $table) {
                $table->id();
                $table->string('no_distribusi')->nullable();
                $table->date('tanggal')->nullable();
                $table->string('nama_barang');
                $table->integer('jumlah')->default(1);
                $table->string('satuan')->nullable()->default('Unit');
                $table->string('tujuan_unit')->nullable();
                $table->string('penerima')->nullable();
                $table->string('status')->default('Terkirim');
                $table->string('dokumen')->nullable();
                $table->text('keterangan')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('as_pembayaran_tagihan')) {
            Schema::create('as_pembayaran_tagihan', function (Blueprint $table) {
                $table->id();
                $table->string('no_tagihan')->nullable();
                $table->date('tanggal_tagihan')->nullable();
                $table->date('tanggal_bayar')->nullable();
                $table->string('vendor')->nullable();
                $table->text('uraian')->nullable();
                $table->decimal('nilai', 18, 2)->default(0);
                $table->string('status')->default('Belum Bayar');
                $table->string('no_rekening')->nullable();
                $table->string('dokumen')->nullable();
                $table->text('keterangan')->nullable();
                $table->timestamps();
            });
        }

        // ── 3. Pengadaan & Pemeliharaan ─────────────────────────────────────────
        if (!Schema::hasTable('pm_jadwal_pemeliharaan')) {
            Schema::create('pm_jadwal_pemeliharaan', function (Blueprint $table) {
                $table->id();
                $table->string('no_jadwal')->nullable();
                $table->foreignId('aset_id')->nullable()->constrained('as_aset')->nullOnDelete();
                $table->string('nama_aset')->nullable();
                $table->string('jenis_pemeliharaan')->nullable();
                $table->date('tanggal_rencana')->nullable();
                $table->date('tanggal_realisasi')->nullable();
                $table->string('pelaksana')->nullable();
                $table->string('status')->default('Direncanakan');
                $table->text('keterangan')->nullable();
                $table->string('dokumen')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('pm_monitoring_kondisi')) {
            Schema::create('pm_monitoring_kondisi', function (Blueprint $table) {
                $table->id();
                $table->foreignId('aset_id')->nullable()->constrained('as_aset')->nullOnDelete();
                $table->string('nama_aset')->nullable();
                $table->date('tanggal_inspeksi')->nullable();
                $table->string('kondisi')->nullable();
                $table->text('temuan')->nullable();
                $table->text('rekomendasi')->nullable();
                $table->string('petugas')->nullable();
                $table->string('status')->default('Selesai');
                $table->string('dokumen')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('pm_pengawasan_penggunaan')) {
            Schema::create('pm_pengawasan_penggunaan', function (Blueprint $table) {
                $table->id();
                $table->foreignId('aset_id')->nullable()->constrained('as_aset')->nullOnDelete();
                $table->string('nama_aset')->nullable();
                $table->date('tanggal')->nullable();
                $table->string('pengguna')->nullable();
                $table->text('uraian_penggunaan')->nullable();
                $table->string('kesesuaian')->nullable();
                $table->text('catatan')->nullable();
                $table->string('petugas')->nullable();
                $table->string('dokumen')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('pm_tindak_lanjut_perbaikan')) {
            Schema::create('pm_tindak_lanjut_perbaikan', function (Blueprint $table) {
                $table->id();
                $table->string('no_tindak_lanjut')->nullable();
                $table->foreignId('aset_id')->nullable()->constrained('as_aset')->nullOnDelete();
                $table->string('nama_aset')->nullable();
                $table->foreignId('ticket_id')->nullable()->constrained('tickets')->nullOnDelete();
                $table->string('sumber')->nullable();
                $table->text('uraian_kerusakan')->nullable();
                $table->date('tanggal_laporan')->nullable();
                $table->date('tanggal_perbaikan')->nullable();
                $table->string('teknisi')->nullable();
                $table->text('hasil_perbaikan')->nullable();
                $table->string('status')->default('Dilaporkan');
                $table->string('dokumen')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('pm_perencanaan_kebutuhan')) {
            Schema::create('pm_perencanaan_kebutuhan', function (Blueprint $table) {
                $table->id();
                $table->string('no_rencana')->nullable();
                $table->string('periode')->nullable();
                $table->string('jenis_kebutuhan')->nullable();
                $table->string('nama_item');
                $table->integer('jumlah')->default(1);
                $table->string('satuan')->nullable()->default('Unit');
                $table->text('spesifikasi')->nullable();
                $table->decimal('estimasi_harga', 18, 2)->nullable()->default(0);
                $table->string('prioritas')->default('Normal');
                $table->string('status')->default('Draft');
                $table->text('keterangan')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pm_perencanaan_kebutuhan');
        Schema::dropIfExists('pm_tindak_lanjut_perbaikan');
        Schema::dropIfExists('pm_pengawasan_penggunaan');
        Schema::dropIfExists('pm_monitoring_kondisi');
        Schema::dropIfExists('pm_jadwal_pemeliharaan');

        Schema::dropIfExists('as_pembayaran_tagihan');
        Schema::dropIfExists('as_distribusi_barang');
        Schema::dropIfExists('as_penerimaan_barang');
        Schema::dropIfExists('as_rekonsiliasi_aset');
        Schema::dropIfExists('as_disposal_aset');
        Schema::dropIfExists('as_aset_histories');

        Schema::dropIfExists('dk_dokumen_legalitas');
        Schema::dropIfExists('dk_arsip_dokumen');
        Schema::dropIfExists('um_k3_insiden');
        Schema::dropIfExists('um_checklist_kebersihan');
        Schema::dropIfExists('um_pemeliharaan_gedung');
        Schema::dropIfExists('um_fasilitas_kantor');
    }
};
