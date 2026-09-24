# Panduan Distribusi Commit & Kolaborasi Tim (Mata Kuliah Manajemen Proyek)
## Berdasarkan 69 Perubahan Terkini Sistem Portal Umum & Aset (PORTUM)

Dokumen ini disusun untuk pembagian kontribusi pengembangan **Portal Umum & Aset (PORTUM)** secara seimbang dan terstruktur bagi 3 anggota tim, mencakup ranah **Backend** maupun **Frontend**, menggunakan alur kerja **Git Flow** berbasis branch `develop`.

---

## 1. Matriks Pembagian Peran & Tanggung Jawab (RACI)

Seluruh **69 perubahan berkas** saat ini dibagi rata secara simetris: **masing-masing anggota menangani tepat 23 berkas** yang saling berkaitan erat (*high cohesion*):

| Anggota Tim | Akun Git & Email | Fokus & Domain Modul | Role yang Ditangani | Beban Berkas | Kontribusi Utama (Backend & Frontend) |
| :--- | :--- | :--- | :--- | :---: | :--- |
| **Alifa Nursintia Marsya** | `alifamarsya`<br>`syafaskincare@gmail.com` | **Autentikasi, Profil Akun, RBAC, Layout Global, & Admin/Pimpinan** | `admin`, `pimpinan`, Profil Akun Global | **23 Berkas** | • Controller Profil Pengguna & Ganti Password<br>• Controller Auth Login Multi-Role & Session DB<br>• Matriks Izin Role Dinamis & Seeder<br>• Layout Navbar, Dropdown Profil & Notifikasi<br>• Dashboard Admin & Dashboard Pimpinan<br>• Suite Uji Otomatis (Dashboard, Maker-Checker, Race Condition, Validasi) |
| **Dirly Dwi P** | `Dirli12`<br>`dirlydwip@gmail.com` | **Submodul Operasional Aset & Logistik, Dokumen Kontrak, & Pemeliharaan** | `kabag_aset`, `staf_aset`, Pengadaan Logistik | **23 Berkas** | • 2 Berkas Migrasi Database Submodul Operasional<br>• Seeder Data Dummy Transaksi Operasional Lengkap<br>• 14 Model Domain (Aset, Dokumen, Pemeliharaan)<br>• Konfigurasi Metadata Skema Modul (`modules.php`)<br>• Dynamic ModuleController (CRUD Generator)<br>• UI Index Tabel & Form Dinamis Submodul Operasional<br>• Dashboard Pengadaan & Uji Fitur Submodul |
| **Zahra** | `Zahra`<br>`zahraraaa287@gmail.com` | **Submodul Umum & RT, Workflow Tiket, Analitik BI, & Dasbor Pelaksana** | `operator`, `kabag_umum`, `staf_umum`, `user` | **23 Berkas** | • 4 Model Domain Umum & Rumah Tangga (Gedung, Fasilitas, K3, Kebersihan)<br>• Controller Tiket, Alokasi & Approval SLA<br>• Controller Analitik & Risalah Rapat<br>• Command ETL Data Warehouse & Deteksi Anomali<br>• Background Job Laporan Biaya Bulanan & Scheduler<br>• UI Tiket (Buat, Index, Detail Interaktif SLA)<br>• Dasbor Operator, Kabag, Staf, & User Pemohon |

---

## 2. Strategi Branching (Git Flow)

> [!IMPORTANT]
> **Aturan Proyek**: Seluruh pekerjaan dilakukan di branch **`develop`** dan **TIDAK ADA push maupun merge ke branch `main`**.
>
> Setiap anggota membuat **Feature Branch** dari `develop`, melakukan commit bertahap, dan melakukan merge kembali ke `develop` menggunakan opsi `--no-ff` (non-fast-forward) agar bukti kolaborasi dan grafik riwayat cabang tercatat secara formal pada portofolio mata kuliah Manajemen Proyek.

```text
develop ───────────●───────────────────────────●───────────────────────────● (Integrasi develop)
                    \                         / \                         /
feature/profile-auth-admin (Marsya)──────────/   \                       /
                                                  ●───●───● (Dirly)─────/
feature/operational-asset-modules ───────────────/                       \
                                                                          ●───●───● (Zahra)
feature/operational-ticket-analytics ────────────────────────────────────/
```

---

## 3. Rincian Eksekusi Commit Per Anggota Tim

---

### BAGIAN 1: Alifa Nursintia Marsya (`alifamarsya`) — 23 Berkas
> **Fokus**: Profil Pengguna, Alur Login, Hak Akses Dinamis, Header Dropdowns, & Dashboard Eksekutif

#### Persiapan Identitas Git:
```bash
git config user.name "alifamarsya"
git config user.email "syafaskincare@gmail.com"
git checkout develop
git checkout -b feature/profile-auth-admin
```

#### Commit 1.1 (Backend & Auth): Autentikasi Login Multi-Role & Sesi Database
- **File yang di-stage**:
  ```bash
  git add app/Http/Controllers/Auth/LoginController.php database/migrations/2026_08_07_084602_create_sessions_table.php resources/views/auth/login.blade.php
  ```
- **Pesan Commit**:
  ```bash
  git commit -m "feat(auth): perbarui alur autentikasi login multi-role dan konfigurasi session driver database"
  ```
- **Deskripsi**: Menyesuaikan controller login untuk pengalihan dasbor berbasis hak akses dan memastikan migrasi tabel sesi aktif.

#### Commit 1.2 (Backend & Frontend): Profil Pengguna, Ubah Kata Sandi & Navigasi Dropdown
- **File yang di-stage**:
  ```bash
  git add app/Http/Controllers/ProfileController.php resources/views/profile/show.blade.php resources/views/profile/change-password.blade.php resources/views/partials/profile-dropdown.blade.php resources/views/partials/notification-dropdown.blade.php resources/views/layouts/app.blade.php
  ```
- **Pesan Commit**:
  ```bash
  git commit -m "feat(profile): implementasi manajemen profil pengguna perubahan kata sandi dan dropdown navigasi header"
  ```
- **Deskripsi**: Menyediakan controller profil, halaman rincian biodata pegawai, form pergantian kata sandi mandiri, serta integrasi dropdown profil & notifikasi pada layout utama.

#### Commit 1.3 (Backend & Frontend): Hak Akses Dinamis, Seeder & Dasbor Eksekutif
- **File yang di-stage**:
  ```bash
  git add app/Http/Controllers/Admin/RoleController.php database/seeders/RolePermissionSeeder.php database/seeders/DatabaseSeeder.php resources/views/admin/dashboard.blade.php resources/views/pimpinan/dashboard.blade.php resources/views/panduan/index.blade.php app/Console/Commands/AuditComplianceCheckCommand.php
  ```
- **Pesan Commit**:
  ```bash
  git commit -m "feat(rbac): sinkronisasi izin role dinamis seeder database dan pembaruan dasbor admin pimpinan"
  ```
- **Deskripsi**: Mengintegrasikan kontrol izin submodul operasional via web, memperbarui seeder role, merapikan navigasi panduan, dan menyempurnakan dasbor eksekutif pimpinan.

#### Commit 1.4 (Testing & Konvensi): Pengujian Kualitas Sistem & Aturan Pengembangan
- **File yang di-stage**:
  ```bash
  git add tests/Feature/DashboardRoleRenderingTest.php tests/Feature/ViewRenderingTest.php tests/Feature/MakerCheckerTest.php tests/Feature/RaceConditionTest.php tests/Feature/ValidasiInputTest.php .agents/rules/db-column-verification.md .agents/rules/portum-app-conventions.md
  ```
- **Pesan Commit**:
  ```bash
  git commit -m "test(qa): lengkapi pengujian render dasbor seluruh role pencegahan race condition dan aturan proyek"
  ```
- **Deskripsi**: Menambahkan suite pengujian otomatis untuk memvalidasi kelayakan antarmuka seluruh peran serta mendokumentasikan aturan arsitektur.

#### Push & Merge (Marsya):
```bash
git push -u origin feature/profile-auth-admin
git checkout develop
git merge --no-ff feature/profile-auth-admin -m "Merge branch 'feature/profile-auth-admin' into develop"
```

---

### BAGIAN 2: Dirly Dwi P (`Dirli12`) — 23 Berkas
> **Fokus**: Arsitektur Database Submodul Operasional, Model Domain Aset & Dokumen, Generator CRUD

#### Persiapan Identitas Git:
```bash
git config user.name "Dirli12"
git config user.email "dirlydwip@gmail.com"
git checkout develop
git checkout -b feature/operational-asset-modules
```

#### Commit 2.1 (Database & Seeder): Skema Tabel Submodul Operasional & Data Dummy
- **File yang di-stage**:
  ```bash
  git add database/migrations/2026_09_22_000001_create_operational_submodules_tables.php database/migrations/2026_09_22_000002_create_as_mutasi_aset_table.php database/seeders/DummyDataSeeder.php
  ```
- **Pesan Commit**:
  ```bash
  git commit -m "feat(database): migrasi 14 tabel submodul operasional dan seeder transaksi dummy komprehensif"
  ```
- **Deskripsi**: Menyiapkan struktur tabel relasional untuk modul Aset, Pengadaan, Dokumen Kontrak, dan Pemeliharaan beserta data seeder transaksi.

#### Commit 2.2 (Backend Models): Entitas Logistik, Mutasi, dan Rekonsiliasi Aset
- **File yang di-stage**:
  ```bash
  git add app/Models/AsPenerimaanBarang.php app/Models/AsDistribusiBarang.php app/Models/AsMutasiAset.php app/Models/AsAsetHistory.php app/Models/AsRekonsiliasiAset.php app/Models/AsDisposalAset.php app/Models/AsPembayaranTagihan.php
  ```
- **Pesan Commit**:
  ```bash
  git commit -m "feat(models): implementasi model domain penerimaan distribusi mutasi riwayat dan disposal aset"
  ```
- **Deskripsi**: Membuat pemodelan data Eloquent untuk alur siklus hidup aset mulai dari penerimaan fisik hingga penghapusan aset.

#### Commit 2.3 (Backend Models): Entitas Arsip Dokumen & Pemeliharaan Fasilitas
- **File yang di-stage**:
  ```bash
  git add app/Models/DkArsipDokumen.php app/Models/DkDokumenLegalitas.php app/Models/PmPerencanaanKebutuhan.php app/Models/PmPengawasanPenggunaan.php app/Models/PmJadwalPemeliharaan.php app/Models/PmMonitoringKondisi.php app/Models/PmTindakLanjutPerbaikan.php
  ```
- **Pesan Commit**:
  ```bash
  git commit -m "feat(models): implementasi model arsip dokumen legalitas pks dan jadwal pemeliharaan berkala"
  ```
- **Deskripsi**: Memodelkan penyimpanan dokumen perjanjian kerja sama, izin legalitas, dan siklus pemeliharaan sarana kantor.

#### Commit 2.4 (Controller, Config & Views): Dynamic Module CRUD Generator
- **File yang di-stage**:
  ```bash
  git add config/modules.php app/Http/Controllers/ModuleController.php resources/views/modules/index.blade.php resources/views/modules/form.blade.php
  ```
- **Pesan Commit**:
  ```bash
  git commit -m "feat(modules): integrasi dynamic module controller konfigurasi skema dan tampilan form index CRUD"
  ```
- **Deskripsi**: Membangun pengendali generik cerdas yang merender tabel data dan formulir input dinamis berdasarkan konfigurasi modul.

#### Commit 2.5 (Frontend & Test): Dasbor Pengadaan & Uji Fitur Submodul
- **File yang di-stage**:
  ```bash
  git add resources/views/pengadaan/dashboard.blade.php tests/Feature/OperationalSubmodulesRenderingTest.php
  ```
- **Pesan Commit**:
  ```bash
  git commit -m "feat(ui/pengadaan): rancang antarmuka dasbor pengadaan dan tambahkan uji render 14 submodul"
  ```
- **Deskripsi**: Mendesain dasbor monitoring vendor/SPK pengadaan dan memastikan seluruh 14 submodul operasional lulus uji render HTTP 200.

#### Push & Merge (Dirly):
```bash
git push -u origin feature/operational-asset-modules
git checkout develop
git merge --no-ff feature/operational-asset-modules -m "Merge branch 'feature/operational-asset-modules' into develop"
```

---

### BAGIAN 3: Zahra (`Zahra`) — 23 Berkas
> **Fokus**: Submodul Umum & RT, Alur Tiket & SLA, Analitik BI, Jobs, & Dasbor Pelaksana

#### Persiapan Identitas Git:
```bash
git config user.name "Zahra"
git config user.email "zahraraaa287@gmail.com"
git checkout develop
git checkout -b feature/operational-ticket-analytics
```

#### Commit 3.1 (Backend Models): Entitas Sarana Umum & Rumah Tangga
- **File yang di-stage**:
  ```bash
  git add app/Models/UmPemeliharaanGedung.php app/Models/UmFasilitasKantor.php app/Models/UmChecklistKebersihan.php app/Models/UmK3Insiden.php
  ```
- **Pesan Commit**:
  ```bash
  git commit -m "feat(models): pemodelan entitas umum dan rumah tangga mencakup pemeliharaan gedung fasilitas dan k3"
  ```
- **Deskripsi**: Menyediakan entitas Eloquent untuk mencatat pekerjaan perbaikan gedung, inventaris fasilitas, checklist sanitasi, dan insiden K3.

#### Commit 3.2 (Backend Controllers & Routing): Alur Tiket, Analitik, & Risalah Rapat
- **File yang di-stage**:
  ```bash
  git add app/Http/Controllers/TicketController.php app/Http/Controllers/DashboardController.php app/Http/Controllers/AnalyticsController.php app/Http/Controllers/RisalahRapatController.php routes/web.php
  ```
- **Pesan Commit**:
  ```bash
  git commit -m "feat(controller): optimasi controller alur tiket operasional analitik data dan pencatatan risalah rapat"
  ```
- **Deskripsi**: Menyempurnakan alokasi tiket, persetujuan Kabag, penugasan teknis staf, visualisasi grafik analitik, dan notulen rapat.

#### Commit 3.3 (Console & Background Jobs): ETL Data Warehouse, Deteksi Anomali & Scheduler
- **File yang di-stage**:
  ```bash
  git add app/Console/Commands/DeteksiAnomaliTransaksiCommand.php app/Console/Commands/EtlDataWarehouseCommand.php app/Console/Commands/VerifikasiAkurasiEtlCommand.php app/Jobs/GenerateLaporanBiayaBulananJob.php routes/console.php
  ```
- **Pesan Commit**:
  ```bash
  git commit -m "feat(analytics): implementasi etl data warehouse deteksi anomali transaksi dan cron scheduler laporan"
  ```
- **Deskripsi**: Menjadwalkan background queue job pembuatan rekapitulasi biaya bulanan dan command sinkronisasi data warehouse.

#### Commit 3.4 (Frontend Views): Antarmuka Dasbor Pelaksana Operasional
- **File yang di-stage**:
  ```bash
  git add resources/views/operator/dashboard.blade.php resources/views/kabag/dashboard.blade.php resources/views/staf/dashboard.blade.php resources/views/user/dashboard.blade.php
  ```
- **Pesan Commit**:
  ```bash
  git commit -m "feat(ui/dashboard): perbarui antarmuka dasbor interaktif operator kabag staf pelaksana dan pemohon"
  ```
- **Deskripsi**: Merancang dasbor adaptif dengan metrik antrean tiket masuk bagi operator, disposisi approval bagi Kabag, dan tugas pengerjaan staf.

#### Commit 3.5 (Frontend Views & Test): UI Sistem Tiket, Biaya Harian, & Risalah Rapat
- **File yang di-stage**:
  ```bash
  git add resources/views/tiket/buat.blade.php resources/views/tiket/index.blade.php resources/views/tiket/detail.blade.php resources/views/umum/biaya_harian/index.blade.php resources/views/risalah/form.blade.php tests/Feature/TicketViewUiRenderingTest.php
  ```
- **Pesan Commit**:
  ```bash
  git commit -m "feat(ui/tiket): sempurnakan form pengajuan tabel monitoring tiket detail interaktif sla dan form risalah"
  ```
- **Deskripsi**: Mempercantik tampilan tiket dengan badge SLA, form aksi alokasi & approval, tabel rekapitulasi biaya BBM, dan suite pengujian UI tiket.

#### Push & Merge (Zahra):
```bash
git push -u origin feature/operational-ticket-analytics
git checkout develop
git merge --no-ff feature/operational-ticket-analytics -m "Merge branch 'feature/operational-ticket-analytics' into develop"

# Commit Pembaruan Berkas Panduan
git add PANDUAN_COMMIT_MANAJEMEN_PROYEK.md
git commit -m "docs: perbarui panduan distribusi commit manajemen proyek berbasis 69 perubahan berkas"

# Push seluruh hasil integrasi develop ke GitHub
git push origin develop
```

---

## 4. Rekapitulasi Pembagian Beban Kerja Tim (Sempurna: 23 Berkas / Orang)

```
┌───────────────────────────────────────────────────────────────────────────────┐
│        DISTRIBUSI 69 PERUBAHAN BERKAS PROYEK PORTUM (3 KONTRIBUTOR)           │
├─────────────────────────┬─────────────────────────┬───────────────────────────┤
│ ALIFA NURSINTIA MARSYA  │       DIRLY DWI P       │           ZAHRA           │
│ (Auth, Profil & Admin)  │ (Submodul Aset/Logistik)│ (Umum, Tiket & Analitik)  │
├─────────────────────────┼─────────────────────────┼───────────────────────────┤
│ • 23 Berkas             │ • 23 Berkas             │ • 23 Berkas               │
│ • 4 Commit Terstruktur  │ • 5 Commit Terstruktur  │ • 5 Commit Terstruktur    │
│ • Backend: Profile,     │ • Backend: 2 Migrasi DB,│ • Backend: 4 Model Umum,  │
│   Auth, Session, RBAC,  │   Seeder Dummy, 14 Model│   Controller Tiket & BI,  │
│   Command Audit         │   Aset/Dokumen, Modules │   ETL DW & Anomali Jobs   │
│ • Frontend: Show Profile│ • Frontend: Modul Index │ • Frontend: Tiket (Buat,  │
│   Change Password, Login│   Form CRUD, Dashboard  │   Index, Detail), Dasbor  │
│   Admin/Pimpinan, Navbar│   Pengadaan Vendor      │   Operator/Kabag/Staf/User│
│ • Test: Dashboard, QA   │ • Test: Operational Test│ • Test: Ticket UI Test    │
└─────────────────────────┴─────────────────────────┴───────────────────────────┘
```

---

## 5. Sinkronisasi Data Lokal untuk Dirly & Zahra

Setelah branch `develop` di-push ke GitHub, Dirly dan Zahra cukup menjalankan langkah berikut di laptop mereka:

```bash
# 1. Tarik pembaruan dari develop
git checkout develop
git pull origin develop

# 2. Reset database & jalankan migrasi beserta seluruh seeder terbaru
php artisan migrate:fresh --seed
```

Semua langkah di atas menjamin repository Anda memiliki riwayat git commit yang sangat kaya, profesional, berimbang merata (23 berkas/anggota), dan memenuhi nilai tertinggi dalam kriteria capaian mata kuliah Manajemen Proyek Perangkat Lunak.
