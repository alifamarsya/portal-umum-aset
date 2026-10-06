# Portum App Conventions

## 1. Maker-Checker Removal
Project portum-app sudah menghapus alur maker-checker dari UI dan bisnis proses:
- Kolom approval (`maker_id`, `checker_id`, `approval_status`, `approved_at`, `catatan_approval`) masih tersisa di struktur DB beberapa tabel.
- Kolom `approval_status` memiliki constraint `NOT NULL DEFAULT 'Diajukan'` di database.
- **PENTING**: Saat menulis seeder, model insert, atau formulir baru, **JANGAN mengisi `null` secara eksplisit** ke kolom `approval_status` karena akan menyebabkan error database SQLSTATE[23000]. Cukup abaikan kolom tersebut (omit key) agar database otomatis menggunakan default value.
- ETL Data Warehouse (`dw:etl`) dan modul operasional membaca semua data tanpa memfilter approval status.

## 2. Branding & UI Theme
- Warna primer brand: `#114E84` (navy).
- Aksen emas (`#D4A038`): Hanya dipakai hemat untuk badge, subtitle, atau aksen status. JANGAN menggunakan gold sebagai ring/outline focus utama pada input/search/tabel.
- Header aplikasi tidak menggunakan search bar umum di navbar kanan.
- Dropdown notifikasi & profil terletak di header kanan.

## 3. Form Input & Focus State
- Hindari menggabungkan browser default `outline`, custom `ring`, dan `border` tebal sekaligus yang menyebabkan tumpang tindih visual (garis ganda).
- Standar perlakuan input/search:
  - Default: `border border-slate-200 rounded-xl` (atau `rounded-lg`)
  - Hover: sedikit menggelapkan border (`border-slate-300`) atau transisi halus tanpa ring tebal.
  - Focus: satu lapisan bersih — `focus:outline-none focus:border-[#114E84] focus:ring-2 focus:ring-[#114E84]/15`.

## 4. Dashboard Layout & Executive Monitoring Guidelines
- **Dashboard Admin**: Card "Tiket Layanan Terbaru" dan "Aktivitas Audit Log Terkini" disusun secara vertikal (atas-bawah) full-width, bukan bersampingan (2 kolom), agar tabel data leluasa.
- **Dashboard Pimpinan**:
  - Mengutamakan ringkasan eksekutif dengan maksimal 3 blok utama: Header singkat, 4-5 KPI inti satu baris, dan 2 area konten utama (Beban Kerja Tiket & Analitik Biaya DW ringkas).
  - Hindari tombol CTA besar di header pimpinan (misal: "Monitoring Tiket" / "Analitik Lengkap →"). Cukup sediakan satu link kecil konteks (misal: "Lihat tiket &rarr;").
  - Card monitoring tiket tidak menggunakan tabel detail penuh melainkan **Beban Kerja per Bagian** (3 bagian: Umum & RT, Aset & Logistik, Pengadaan) dengan metrik ringkas: Aktif, Selesai/Total, dan badge Overdue (merah jika > 0, hijau jika 0).
  - Detail operasional internal jika diperlukan diletakkan pada section collapsible (`<details>`) di bagian bawah agar first fold tetap bersih.

## 5. Typography, Copywriting & Button Standards
- **Typography & Copywriting**:
  - Subtitle halaman/card: Maksimal 1 baris, tidak boleh bertele-tele atau paragraf panjang.
  - Subtext / label pembantu kartu KPI: Singkat 2–4 kata (contoh: "Semua pengajuan", "Sedang dikerjakan", "Layanan tuntas", "Melebihi target SLA").
  - Judul Card / Section: Singkat, padat (contoh: "Beban Kerja Tiket", "Analitik Biaya", "Tiket Layanan Terbaru").
- **Button & Interactive Standards**:
  - Primary Button: `h-10 px-4 text-sm font-semibold rounded-xl bg-[#114E84] text-white hover:bg-[#0d3b64] shadow-xs transition-all`
  - Secondary / Outline Button: `h-10 px-4 text-sm font-semibold rounded-xl border border-slate-200 bg-white text-slate-700 hover:bg-slate-50 transition-all`
  - Ghost / Inline Link: `text-xs sm:text-sm font-semibold text-[#114E84] hover:text-[#0d3b64] transition-colors flex items-center gap-1`
  - Filter / Tab Chips: Seragam `px-3 py-1.5 rounded-lg text-xs font-semibold` dengan state aktif `bg-[#114E84] text-white` dan non-aktif `text-slate-600 hover:bg-slate-100`.

## 6. Data Warehouse ETL
- Fact tables: `fact_biaya_bulanan`, `fact_pengadaan`, `fact_amortisasi_aset`.
- ETL dijalankan otomatis tiap bulan via scheduler: `php artisan dw:etl` pada tanggal 1 setiap bulan jam 02:00.
- Manual trigger ETL: `php artisan dw:etl --tahun=YYYY --bulan=MM`.

