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

class TicketViewUiRenderingTest extends TestCase
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

    public function test_user_can_view_ticket_index_create_and_detail_pages(): void
    {
        $user = User::where('username', 'user1')->first();
        $category = TicketCategory::first();

        // 1. View Index
        $response = $this->actingAs($user)->get(route('tiket.index'));
        $response->assertStatus(200);
        $response->assertSee('Daftar Tiket Layanan');
        $response->assertSee('Total Tiket');
        $response->assertSee('Buat Tiket Baru');
        $response->assertSee('Tiket Saya');

        // 2. View Create
        $response = $this->actingAs($user)->get(route('tiket.create'));
        $response->assertStatus(200);
        $response->assertSee('Buat Tiket Layanan Baru');
        $response->assertSee($user->nama_lengkap);
        $response->assertSee('Kategori Layanan');

        // Create a ticket
        $ticket = Ticket::create([
            'nomor_tiket'     => 'REQ-20260921-0001',
            'pemohon_id'      => $user->id,
            'kategori_id'     => $category->id,
            'deskripsi'       => 'Permintaan keyboard wireless baru untuk divisi keuangan.',
            'jenis_pengajuan' => 'Permintaan',
            'prioritas'       => 'Sedang',
            'status'          => Ticket::STATUS_MENUNGGU,
        ]);

        // 3. View Detail as User
        $response = $this->actingAs($user)->get(route('tiket.show', $ticket));
        $response->assertStatus(200);
        $response->assertSee($ticket->nomor_tiket);
        $response->assertSee('Permintaan keyboard wireless baru');
        $response->assertSee('SLA Response Time');
        $response->assertSee('SLA Resolution Time');
    }

    public function test_operator_views_index_and_detail_with_alokasi_panel(): void
    {
        $operator = User::where('username', 'operator')->first();
        $user = User::where('username', 'user1')->first();
        $category = TicketCategory::first();

        $ticket = Ticket::create([
            'nomor_tiket'     => 'REQ-20260921-0002',
            'pemohon_id'      => $user->id,
            'kategori_id'     => $category->id,
            'deskripsi'       => 'AC bocor menetes ke meja staf.',
            'jenis_pengajuan' => 'Permasalahan',
            'prioritas'       => 'Tinggi',
            'status'          => Ticket::STATUS_MENUNGGU,
        ]);

        $response = $this->actingAs($operator)->get(route('tiket.index'));
        $response->assertStatus(200);
        $response->assertSee('Operator Helpdesk');
        $response->assertDontSee('Buat Tiket Baru'); // Only user can create

        $response = $this->actingAs($operator)->get(route('tiket.show', $ticket));
        $response->assertStatus(200);
        $response->assertSee('Alokasikan ke Bagian');
        $response->assertSee('Penyesuaian Klasifikasi & SLA');
    }

    public function test_kabag_views_detail_with_disposition_and_reject_panel(): void
    {
        $kabag = User::where('username', 'kabag_umum')->first();
        $user = User::where('username', 'user1')->first();
        $category = TicketCategory::first();
        $dept = InternalDepartment::where('slug', 'umum')->first();

        $ticket = Ticket::create([
            'nomor_tiket'     => 'REQ-20260921-0003',
            'pemohon_id'      => $user->id,
            'kategori_id'     => $category->id,
            'department_id'   => $dept->id,
            'deskripsi'       => 'Lampu ruangan mati.',
            'jenis_pengajuan' => 'Permasalahan',
            'prioritas'       => 'Sedang',
            'status'          => Ticket::STATUS_DIALOKASIKAN,
        ]);

        $response = $this->actingAs($kabag)->get(route('tiket.index'));
        $response->assertStatus(200);
        $response->assertSee('Disposisi');

        $response = $this->actingAs($kabag)->get(route('tiket.show', $ticket));
        $response->assertStatus(200);
        $response->assertSee('Disposisi ke Staf Pelaksana');
        $response->assertSee('Tolak Permintaan Ini?');
    }

    public function test_filter_and_search_on_index(): void
    {
        $admin = User::where('username', 'admin')->first();
        $user = User::where('username', 'user1')->first();
        $category = TicketCategory::first();

        Ticket::create([
            'nomor_tiket'     => 'REQ-20260921-0010',
            'pemohon_id'      => $user->id,
            'kategori_id'     => $category->id,
            'deskripsi'       => 'Komputer lemot butuh upgrade RAM.',
            'jenis_pengajuan' => 'Permintaan',
            'prioritas'       => 'Rendah',
            'status'          => Ticket::STATUS_MENUNGGU,
        ]);

        Ticket::create([
            'nomor_tiket'     => 'REQ-20260921-0020',
            'pemohon_id'      => $user->id,
            'kategori_id'     => $category->id,
            'deskripsi'       => 'Printer kantor macet total.',
            'jenis_pengajuan' => 'Permasalahan',
            'prioritas'       => 'Tinggi',
            'status'          => Ticket::STATUS_SELESAI,
        ]);

        // Filter status
        $res = $this->actingAs($admin)->get(route('tiket.index', ['status' => Ticket::STATUS_MENUNGGU]));
        $res->assertStatus(200);
        $res->assertSee('REQ-20260921-0010');
        $res->assertDontSee('REQ-20260921-0020');

        // Filter jenis_pengajuan
        $res = $this->actingAs($admin)->get(route('tiket.index', ['jenis_pengajuan' => 'Permasalahan']));
        $res->assertStatus(200);
        $res->assertSee('REQ-20260921-0020');
        $res->assertDontSee('REQ-20260921-0010');

        // Search
        $res = $this->actingAs($admin)->get(route('tiket.index', ['search' => 'Printer']));
        $res->assertStatus(200);
        $res->assertSee('REQ-20260921-0020');
        $res->assertDontSee('REQ-20260921-0010');
    }
}
