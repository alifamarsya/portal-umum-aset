# Portum App Conventions

## 1. Maker-Checker Removal
Project portum-app sudah menghapus alur maker-checker dari UI dan bisnis proses:
- Kolom approval (`maker_id`, `checker_id`, `approval_status`, `approved_at`, `catatan_approval`) masih tersisa di struktur DB beberapa tabel.
- Kolom `approval_status` memiliki constraint `NOT NULL DEFAULT 'Diajukan'` di database.
- **PENTING**: Saat menulis seeder, model insert, atau formulir baru, **JANGAN mengisi `null` secara eksplisit** ke kolom `approval_status` karena akan menyebabkan error database SQLSTATE[23000]. Cukup abaikan kolom tersebut (omit key) agar database otomatis menggunakan default value.
- ETL Data Warehouse (`dw:etl`) dan modul operasional membaca semua data tanpa memfilter approval status.

## 2. Branding & UI Theme
- Warna primer brand: `#114E84` (navy).
- Header aplikasi tidak menggunakan search bar umum di navbar kanan.
- Dropdown notifikasi & profil terletak di header kanan.

## 3. Data Warehouse ETL
- Fact tables: `fact_biaya_bulanan`, `fact_pengadaan`, `fact_amortisasi_aset`.
- ETL dijalankan otomatis tiap bulan via scheduler: `php artisan dw:etl` pada tanggal 1 setiap bulan jam 02:00.
- Manual trigger ETL: `php artisan dw:etl --tahun=YYYY --bulan=MM`.
