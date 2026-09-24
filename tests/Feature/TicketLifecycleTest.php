<?php

namespace Tests\Feature;

use App\Models\InternalDepartment;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\User;
use Database\Seeders\InternalDepartmentSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\TicketCategorySeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketLifecycleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            RolePermissionSeeder::class,
            InternalDepartmentSeeder::class,
            TicketCategorySeeder::class,
            UserSeeder::class,
        ]);
    }

    public function test_full_ticket_lifecycle_flow(): void
    {
        $user1 = User::where('username', 'user1')->first();
        $operator = User::where('username', 'operator')->first();
        $kabagUmum = User::where('username', 'kabag_umum')->first();
        $stafUmum = User::where('username', 'staf_umum')->first();
        $deptUmum = InternalDepartment::where('slug', 'umum')->first();
        $category = TicketCategory::first();

        // 1. User1 membuat tiket
        $response = $this->actingAs($user1)->post(route('tiket.store'), [
            'kategori_id'     => $category->id,
            'judul'           => 'Kerusakan AC Ruang Rapat',
            'deskripsi'       => 'AC di ruang rapat lantai 2 tidak dingin.',
            'jenis_pengajuan' => 'Permasalahan',
            'prioritas'       => 'Tinggi',
        ]);
        $response->assertRedirect();

        $ticket = Ticket::where('pemohon_id', $user1->id)->latest()->first();
        $this->assertNotNull($ticket);
        $this->assertMatchesRegularExpression('/^REQ-\d{8}-\d{4}$/', $ticket->nomor_tiket);
        $this->assertEquals(Ticket::STATUS_MENUNGGU, $ticket->status);
        $this->assertEquals('Permasalahan', $ticket->jenis_pengajuan);
        $this->assertEquals('Tinggi', $ticket->prioritas);
        $this->assertEquals(12, $ticket->sla_jam);
        $this->assertNotNull($ticket->sla_due_at);
        $this->assertDatabaseHas('ticket_histories', [
            'ticket_id'   => $ticket->id,
            'status_baru' => Ticket::STATUS_MENUNGGU,
            'user_id'     => $user1->id,
        ]);

        // 2. Operator mengalokasikan ke Bagian Umum
        $response = $this->actingAs($operator)->post(route('tiket.alokasi', $ticket->id), [
            'department_id' => $deptUmum->id,
            'catatan' => 'Dialokasikan ke Bagian Umum untuk penanganan AC',
        ]);
        $response->assertRedirect();
        $ticket->refresh();
        $this->assertEquals(Ticket::STATUS_DIALOKASIKAN, $ticket->status);
        $this->assertEquals($deptUmum->id, $ticket->department_id);
        $this->assertDatabaseHas('ticket_histories', [
            'ticket_id' => $ticket->id,
            'status_baru' => Ticket::STATUS_DIALOKASIKAN,
            'user_id' => $operator->id,
        ]);

        // 3. Kabag Umum menyetujui dan mendisposisikan ke Staf Umum
        $response = $this->actingAs($kabagUmum)->post(route('tiket.setujui', $ticket->id), [
            'assigned_to' => $stafUmum->id,
            'catatan' => 'Disetujui. Mohon segera dicek teknisi AC.',
        ]);
        $response->assertRedirect();
        $ticket->refresh();
        $this->assertEquals(Ticket::STATUS_DALAM_PROSES, $ticket->status);
        $this->assertEquals($stafUmum->id, $ticket->assigned_to);
        $this->assertDatabaseHas('ticket_histories', [
            'ticket_id' => $ticket->id,
            'status_baru' => Ticket::STATUS_DALAM_PROSES,
            'user_id' => $kabagUmum->id,
        ]);

        // 4. Staf Umum menyelesaikan tiket
        $response = $this->actingAs($stafUmum)->post(route('tiket.selesai', $ticket->id), [
            'catatan' => 'Freon AC sudah ditambah dan filter sudah dibersihkan. Suhu normal.',
        ]);
        $response->assertRedirect();
        $ticket->refresh();
        $this->assertEquals(Ticket::STATUS_SELESAI, $ticket->status);
        $this->assertDatabaseHas('ticket_histories', [
            'ticket_id' => $ticket->id,
            'status_baru' => Ticket::STATUS_SELESAI,
            'user_id' => $stafUmum->id,
        ]);

        // 5. Pemohon (user1) menutup tiket
        $response = $this->actingAs($user1)->post(route('tiket.tutup', $ticket->id), [
            'catatan' => 'Sudah dicek, ruangan dingin kembali. Terima kasih.',
        ]);
        $response->assertRedirect();
        $ticket->refresh();
        $this->assertEquals(Ticket::STATUS_DITUTUP, $ticket->status);
        $this->assertDatabaseHas('ticket_histories', [
            'ticket_id' => $ticket->id,
            'status_baru' => Ticket::STATUS_DITUTUP,
            'user_id' => $user1->id,
        ]);

        // Total histories harus ada 5 (Dibuat, Dialokasikan, Disetujui/Diproses, Selesai, Ditutup)
        $this->assertCount(5, $ticket->histories);
    }

    public function test_kabag_rejection_flow(): void
    {
        $user1 = User::where('username', 'user1')->first();
        $operator = User::where('username', 'operator')->first();
        $kabagAset = User::where('username', 'kabag_aset')->first();
        $deptAset = InternalDepartment::where('slug', 'aset')->first();

        // User buat tiket
        $this->actingAs($user1)->post(route('tiket.store'), [
            'kategori_id'     => TicketCategory::first()->id,
            'judul'           => 'Permintaan Laptop Baru',
            'deskripsi'       => 'Penggantian unit laptop pribadi',
            'jenis_pengajuan' => 'Permintaan',
            'prioritas'       => 'Sedang',
        ]);
        $ticket = Ticket::latest('id')->first();
        $this->assertEquals('Permintaan', $ticket->jenis_pengajuan);
        $this->assertEquals('Sedang', $ticket->prioritas);
        $this->assertEquals(48, $ticket->sla_jam);

        // Operator alokasi ke Aset
        $this->actingAs($operator)->post(route('tiket.alokasi', $ticket->id), [
            'department_id' => $deptAset->id,
        ]);
        $ticket->refresh();
        $this->assertEquals(Ticket::STATUS_DIALOKASIKAN, $ticket->status);

        // Kabag Aset tolak
        $response = $this->actingAs($kabagAset)->post(route('tiket.tolak', $ticket->id), [
            'alasan' => 'Anggaran pengadaan laptop baru belum tersedia triwulan ini.',
        ]);
        $response->assertRedirect();

        $ticket->refresh();
        $this->assertEquals(Ticket::STATUS_DITOLAK, $ticket->status);
        $this->assertEquals('Anggaran pengadaan laptop baru belum tersedia triwulan ini.', $ticket->alasan_penolakan);
        $this->assertDatabaseHas('ticket_histories', [
            'ticket_id' => $ticket->id,
            'status_baru' => Ticket::STATUS_DITOLAK,
            'user_id' => $kabagAset->id,
        ]);
    }

    public function test_visibility_scoping(): void
    {
        $user1 = User::where('username', 'user1')->first();
        $user2 = User::where('username', 'user2')->first();
        $deptUmum = InternalDepartment::where('slug', 'umum')->first();
        $deptAset = InternalDepartment::where('slug', 'aset')->first();

        $cat = TicketCategory::first();

        // User1 ticket in department Umum
        $ticket1 = Ticket::create([
            'nomor_tiket' => 'REQ-20260915-0001',
            'pemohon_id' => $user1->id,
            'kategori_id' => $cat->id,
            'department_id' => $deptUmum->id,
            'judul' => 'Tiket User 1',
            'deskripsi' => 'Deskripsi',
            'prioritas' => 'Rendah',
            'status' => Ticket::STATUS_DIALOKASIKAN,
        ]);

        // User2 ticket in department Aset
        $ticket2 = Ticket::create([
            'nomor_tiket' => 'REQ-20260915-0002',
            'pemohon_id' => $user2->id,
            'kategori_id' => $cat->id,
            'department_id' => $deptAset->id,
            'judul' => 'Tiket User 2',
            'deskripsi' => 'Deskripsi',
            'prioritas' => 'Sedang',
            'status' => Ticket::STATUS_DIALOKASIKAN,
        ]);

        // User 1 only sees ticket 1
        $user1Visible = Ticket::visibleBy($user1)->pluck('id');
        $this->assertTrue($user1Visible->contains($ticket1->id));
        $this->assertFalse($user1Visible->contains($ticket2->id));

        // Kabag Umum only sees ticket in Umum
        $kabagUmum = User::where('username', 'kabag_umum')->first();
        $kabagVisible = Ticket::visibleBy($kabagUmum)->pluck('id');
        $this->assertTrue($kabagVisible->contains($ticket1->id));
        $this->assertFalse($kabagVisible->contains($ticket2->id));

        // Admin & Operator see both
        $admin = User::where('username', 'admin')->first();
        $operator = User::where('username', 'operator')->first();
        $this->assertEquals(2, Ticket::visibleBy($admin)->count());
        $this->assertEquals(2, Ticket::visibleBy($operator)->count());
    }

    public function test_all_role_dashboards_render_successfully(): void
    {
        $usernames = [
            'admin',
            'pimpinan',
            'operator',
            'kabag_umum',
            'kabag_aset',
            'kabag_pengadaan',
            'staf_umum',
            'staf_aset',
            'staf_pengadaan',
            'user1',
            'user2',
            'user3',
        ];

        foreach ($usernames as $username) {
            $user = User::where('username', $username)->first();
            $response = $this->actingAs($user)->get(route('dashboard'));
            $response->assertStatus(200);
        }
    }

    public function test_operator_can_update_classification_and_priority_and_sla_recalculates(): void
    {
        $user1 = User::where('username', 'user1')->first();
        $operator = User::where('username', 'operator')->first();
        $cat = TicketCategory::first();

        // 1. User membuat tiket dengan Permintaan & Rendah (72 jam)
        $this->actingAs($user1)->post(route('tiket.store'), [
            'kategori_id'     => $cat->id,
            'judul'           => 'Permintaan Penggantian Lampu Ruangan',
            'deskripsi'       => 'Lampu redup di area kerja',
            'jenis_pengajuan' => 'Permintaan',
            'prioritas'       => 'Rendah',
        ]);

        $ticket = Ticket::latest('id')->first();
        $this->assertEquals('Permintaan', $ticket->jenis_pengajuan);
        $this->assertEquals('Rendah', $ticket->prioritas);
        $this->assertEquals(72, $ticket->sla_jam);

        // 2. Operator meninjau dan mengubah menjadi Permasalahan & Kritis (SLA 4 Jam)
        $response = $this->actingAs($operator)->post(route('tiket.klasifikasi', $ticket->id), [
            'jenis_pengajuan' => 'Permasalahan',
            'prioritas'       => 'Kritis',
            'catatan'         => 'Kabel terbakar dan menimbulkan asap, eskalasi darurat.',
        ]);
        $response->assertRedirect();

        $ticket->refresh();
        $this->assertEquals('Permasalahan', $ticket->jenis_pengajuan);
        $this->assertEquals('Kritis', $ticket->prioritas);
        $this->assertEquals(4, $ticket->sla_jam);

        // Target SLA harus dihitung ulang (created_at + 4 jam)
        $expectedDueAt = $ticket->created_at->copy()->addHours(4);
        $this->assertEquals($expectedDueAt->timestamp, $ticket->sla_due_at->timestamp);

        // Riwayat harus tercatat di ticket_histories
        $this->assertDatabaseHas('ticket_histories', [
            'ticket_id' => $ticket->id,
            'user_id'   => $operator->id,
            'aksi'      => 'Penyesuaian Klasifikasi Tiket',
        ]);
    }

    public function test_non_operator_cannot_update_ticket_classification(): void
    {
        $user1 = User::where('username', 'user1')->first();
        $user2 = User::where('username', 'user2')->first();
        $stafUmum = User::where('username', 'staf_umum')->first();
        $cat = TicketCategory::first();

        $ticket = Ticket::create([
            'nomor_tiket'     => 'REQ-20260915-0999',
            'pemohon_id'      => $user1->id,
            'kategori_id'     => $cat->id,
            'judul'           => 'Tiket Uji Akses',
            'deskripsi'       => 'Deskripsi uji',
            'jenis_pengajuan' => 'Permintaan',
            'prioritas'       => 'Sedang',
            'sla_jam'         => 48,
            'status'          => Ticket::STATUS_MENUNGGU,
        ]);

        // User pemohon mencoba mengubah klasifikasi -> 403 Forbidden
        $response = $this->actingAs($user1)->post(route('tiket.klasifikasi', $ticket->id), [
            'jenis_pengajuan' => 'Permasalahan',
            'prioritas'       => 'Kritis',
        ]);
        $response->assertStatus(403);

        // User lain mencoba mengubah klasifikasi -> 403 Forbidden
        $response = $this->actingAs($user2)->post(route('tiket.klasifikasi', $ticket->id), [
            'jenis_pengajuan' => 'Permasalahan',
            'prioritas'       => 'Kritis',
        ]);
        $response->assertStatus(403);

        // Staf mencoba mengubah klasifikasi -> 403 Forbidden
        $response = $this->actingAs($stafUmum)->post(route('tiket.klasifikasi', $ticket->id), [
            'jenis_pengajuan' => 'Permasalahan',
            'prioritas'       => 'Kritis',
        ]);
        $response->assertStatus(403);
    }

    public function test_user_can_create_ticket_without_title_and_auto_generates_title(): void
    {
        $user1 = User::where('username', 'user1')->first();
        $category = TicketCategory::first();

        // Submit form tanpa field 'judul'
        $response = $this->actingAs($user1)->post(route('tiket.store'), [
            'kategori_id'     => $category->id,
            'deskripsi'       => 'Pengajuan layanan baru tanpa judul manual.',
            'jenis_pengajuan' => 'Permintaan',
            'prioritas'       => 'Sedang',
        ]);
        $response->assertRedirect();

        $ticket = Ticket::where('pemohon_id', $user1->id)->latest()->first();
        $this->assertNotNull($ticket);
        // Memastikan judul terbuat otomatis dari nama kategori dan jenis pengajuan
        $this->assertEquals("{$category->nama} — Permintaan", $ticket->judul);
        $this->assertEquals('Permintaan', $ticket->jenis_pengajuan);
        $this->assertEquals('Sedang', $ticket->prioritas);
        $this->assertEquals(48, $ticket->sla_jam);
    }

    public function test_operator_allocates_ticket_without_sla_dropdown(): void
    {
        $user1 = User::where('username', 'user1')->first();
        $operator = User::where('username', 'operator')->first();
        $deptUmum = InternalDepartment::where('slug', 'umum')->first();
        $category = TicketCategory::first();

        $ticket = Ticket::create([
            'nomor_tiket'     => 'REQ-20260917-0010',
            'pemohon_id'      => $user1->id,
            'kategori_id'     => $category->id,
            'judul'           => 'Tiket Uji Alokasi',
            'deskripsi'       => 'Alokasi tanpa dropdown SLA',
            'jenis_pengajuan' => 'Permasalahan',
            'prioritas'       => 'Tinggi',
            'sla_jam'         => 12,
            'sla_due_at'      => now()->addHours(12),
            'status'          => Ticket::STATUS_MENUNGGU,
        ]);

        // Operator alokasi HANYA mengirimkan department_id dan catatan (tanpa SLA)
        $response = $this->actingAs($operator)->post(route('tiket.alokasi', $ticket->id), [
            'department_id' => $deptUmum->id,
            'catatan'       => 'Diteruskan ke Bagian Umum',
        ]);
        $response->assertRedirect();

        $ticket->refresh();
        $this->assertEquals(Ticket::STATUS_DIALOKASIKAN, $ticket->status);
        $this->assertEquals($deptUmum->id, $ticket->department_id);
        // SLA dan prioritas tetap tidak berubah
        $this->assertEquals('Tinggi', $ticket->prioritas);
        $this->assertEquals(12, $ticket->sla_jam);
    }

    public function test_kabag_can_see_all_staff_members_in_department_multi_user(): void
    {
        $kabagUmum = User::where('username', 'kabag_umum')->first();
        $deptUmum = InternalDepartment::where('slug', 'umum')->first();
        $category = TicketCategory::first();
        $user1 = User::where('username', 'user1')->first();

        $ticket = Ticket::create([
            'nomor_tiket'     => 'REQ-20260917-0020',
            'pemohon_id'      => $user1->id,
            'department_id'   => $deptUmum->id,
            'kategori_id'     => $category->id,
            'judul'           => 'Tiket Multi User Staf',
            'deskripsi'       => 'Uji daftar staf multi-user',
            'jenis_pengajuan' => 'Permintaan',
            'prioritas'       => 'Sedang',
            'sla_jam'         => 48,
            'status'          => Ticket::STATUS_DIALOKASIKAN,
        ]);

        $response = $this->actingAs($kabagUmum)->get(route('tiket.show', $ticket->id));
        $response->assertStatus(200);

        // Harus ada multi-user staf bagian umum di view data
        $response->assertViewHas('stafList', function ($stafList) {
            $usernames = $stafList->pluck('username')->toArray();
            return in_array('staf_umum', $usernames) && in_array('staf_umum2', $usernames);
        });
    }

    public function test_attachment_preview_and_download_flow(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');
        $file = \Illuminate\Http\UploadedFile::fake()->create('dokumen_bukti.pdf', 150, 'application/pdf');

        $user1 = User::where('username', 'user1')->first();
        $user2 = User::where('username', 'user2')->first();
        $category = TicketCategory::first();

        // User 1 membuat tiket dengan lampiran
        $response = $this->actingAs($user1)->post(route('tiket.store'), [
            'kategori_id'     => $category->id,
            'deskripsi'       => 'Tiket dengan file lampiran pendukung',
            'jenis_pengajuan' => 'Permintaan',
            'prioritas'       => 'Sedang',
            'attachment'      => $file,
        ]);
        $response->assertRedirect();

        $ticket = Ticket::where('pemohon_id', $user1->id)->latest()->first();
        $this->assertNotNull($ticket->attachment);
        \Illuminate\Support\Facades\Storage::disk('public')->assertExists($ticket->attachment);

        // 1. Pemohon bisa melihat (preview) dan mengunduh lampiran
        $responseView = $this->actingAs($user1)->get(route('tiket.attachment.view', $ticket->id));
        $responseView->assertStatus(200);

        $responseDownload = $this->actingAs($user1)->get(route('tiket.attachment.download', $ticket->id));
        $responseDownload->assertStatus(200);

        // 2. Operator bisa melihat dan mengunduh lampiran
        $operator = User::where('username', 'operator')->first();
        $this->actingAs($operator)->get(route('tiket.attachment.view', $ticket->id))->assertStatus(200);
        $this->actingAs($operator)->get(route('tiket.attachment.download', $ticket->id))->assertStatus(200);

        // 3. User lain yang tidak memiliki akses ditolak (403 Forbidden)
        $this->actingAs($user2)->get(route('tiket.attachment.view', $ticket->id))->assertStatus(403);
        $this->actingAs($user2)->get(route('tiket.attachment.download', $ticket->id))->assertStatus(403);
    }
}