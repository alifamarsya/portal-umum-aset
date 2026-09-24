# DB Column Verification

Sebelum menulis query, seeder, model, atau ETL yang menyebut nama kolom spesifik:
1. Baca migration file yang relevan di `database/migrations/` untuk konfirmasi nama kolom aktual dan tipe/constraint-nya.
2. Jangan mengasumsikan nama kolom dari referensi repo lain atau memori — selalu verifikasi ke skema project ini.
3. Jika kolom memiliki constraint `NOT NULL DEFAULT ...`, jangan masukkan nilai `null` secara eksplisit karena akan memicu `Integrity constraint violation: Column cannot be null`. Biarkan DB memakai nilai default-nya atau omit key tersebut.
4. Jika ragu kolom ada atau tidak, gunakan `Schema::hasColumn()` guard sebelum query/update.

Contoh perbedaan kolom penting di project ini:
- `as_pks`: `jatuh_tempo` (bukan `tanggal_akhir`)
- `pg_spk`: `nilai` (bukan `nilai_spk`), `tanggal_terbit` (bukan `tanggal`)
- `as_mutasi_aset`: `dari_lokasi`, `ke_lokasi`, `dari_penanggung_jawab`, `ke_penanggung_jawab`, `alasan` (bukan `dari`, `ke`, `jenis`)
- `pm_perencanaan_kebutuhan`: `jenis_kebutuhan` (bukan `jenis`)
