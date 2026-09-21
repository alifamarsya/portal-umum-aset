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

class TicketTwoTierSlaLifecycleTest extends TestCase
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

    public function test_two_tier_sla_lifecycle_on_time(): void
    {
        $user1     = User::where('username', 'user1')->first();
        $operator  = User::where('username', 'operator')->first();
        $kabagUmum = User::where('username', 'kabag_umum')->first();
        $stafUmum  = User::where('username', 'staf_umum')->first();
        $deptUmum  = InternalDepartment::where('slug', 'umum')->first();
        $category  = TicketCategory::where('nama', 'Pemeliharaan Gedung & Fasilitas')->first();

        // 1. User membuat tiket dengan prioritas Tinggi
        $response = $this->actingAs($user1)->post(route('tiket.store'), [
            'kategori_id'     => $category->id,
            'deskripsi'       => 'Perbaikan atap ruang pertemuan bocor parah.',
            'jenis_pengajuan' => 'Permasalahan',
            'prioritas'       => 'Tinggi',
        ]);
        $response->assertRedirect();

        $ticket = Ticket::where('pemohon_id', $user1->id)->latest()->first();
        $this->assertNotNull($ticket);
        $this->assertNotNull($ticket->response_due_at);
        $this->assertNull($ticket->responded_at);
        $this->assertEquals('pending', $ticket->response_sla_status);
        $this->assertEquals('pending', $ticket->resolution_sla_status);

        // 2. Operator mengalokasikan tiket tepat waktu (Response Time terpenuhi)
        $response = $this->actingAs($operator)->post(route('tiket.alokasi', $ticket->id), [
            'department_id' => $deptUmum->id,
            'catatan'       => 'Dialokasikan ke Umum segera ditindaklanjuti',
        ]);
        $response->assertRedirect();

        $ticket->refresh();
        $this->assertEquals(Ticket::STATUS_DIALOKASIKAN, $ticket->status);
        $this->assertNotNull($ticket->responded_at);
        $this->assertEquals('met', $ticket->response_sla_status);

        // 3. Kabag menyetujui tiket (Timer Resolution Time mulai berjalan)
        $response = $this->actingAs($kabagUmum)->post(route('tiket.setujui', $ticket->id), [
            'assigned_to' => $stafUmum->id,
            'catatan'     => 'Segera lakukan perbaikan',
        ]);
        $response->assertRedirect();

        $ticket->refresh();
        $this->assertEquals(Ticket::STATUS_DALAM_PROSES, $ticket->status);
        $this->assertNotNull($ticket->resolution_started_at);
        $this->assertNotNull($ticket->resolution_due_at);
        $this->assertGreaterThan(0, $ticket->resolution_hours);
        $this->assertEquals('in_progress', $ticket->resolution_sla_status);

        // 4. Staf menyelesaikan tiket
        $response = $this->actingAs($stafUmum)->post(route('tiket.selesai', $ticket->id), [
            'catatan' => 'Atap sudah diperbaiki dan waterproofed.',
        ]);
        $response->assertRedirect();

        $ticket->refresh();
        $this->assertEquals(Ticket::STATUS_SELESAI, $ticket->status);
        $this->assertNotNull($ticket->resolved_at);
        $this->assertEquals('met', $ticket->resolution_sla_status);
        $this->assertEquals('completed', $ticket->sla_status);
    }

    public function test_overdue_response_and_resolution_detection(): void
    {
        $user1    = User::where('username', 'user1')->first();
        $category = TicketCategory::first();

        // Tiket yang batas responsnya sudah lewat
        $overdueTicket = Ticket::create([
            'nomor_tiket'           => 'REQ-TEST-OVERDUE-01',
            'judul'                 => 'Tiket Test Overdue Response',
            'deskripsi'             => 'Deskripsi tiket overdue',
            'jenis_pengajuan'       => 'Permintaan',
            'prioritas'             => 'Sedang',
            'kategori_id'           => $category->id,
            'pemohon_id'            => $user1->id,
            'status'                => Ticket::STATUS_MENUNGGU,
            'response_due_at'       => now()->subHours(5), // lewat 5 jam
            'response_sla_status'   => 'pending',
            'resolution_sla_status' => 'pending',
            'sla_status'            => 'on_track',
        ]);

        $this->assertTrue($overdueTicket->isSlaBreached());
        $this->assertTrue($overdueTicket->isResponseBreached());

        // Jalankan artisan check-sla
        $this->artisan('tickets:check-sla')->assertExitCode(0);

        $overdueTicket->refresh();
        $this->assertEquals('breached', $overdueTicket->response_sla_status);
        $this->assertEquals('overdue', $overdueTicket->sla_status);

        // Uji filter overdue di list tiket
        $response = $this->actingAs($user1)->get(route('tiket.index', ['sla_status' => 'overdue']));
        $response->assertStatus(200);
        $response->assertSee('REQ-TEST-OVERDUE-01');
    }
}