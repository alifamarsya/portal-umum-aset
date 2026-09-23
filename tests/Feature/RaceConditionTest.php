<?php

namespace Tests\Feature;

use App\Models\InternalDepartment;
use App\Models\Role;
use App\Models\RolePermission;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\UmBiayaHarian;
use App\Models\User;
use Database\Seeders\InternalDepartmentSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RaceConditionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(InternalDepartmentSeeder::class);
        $this->seed(RolePermissionSeeder::class);
    }

    /**
     * Sesuai arsitektur baru:
     * Approval di modul operasional telah dinonaktifkan (kembalikan 404).
     */
    public function test_operational_module_approval_is_disabled(): void
    {
        $pimpinan = User::factory()->create([
            'role_id' => 2,
            'is_active' => true,
        ]);

        $resp = $this->actingAs($pimpinan)
            ->post("/modul/biaya_harian/1/setujui");

        $resp->assertStatus(404);
    }

    /**
     * Approval hanya ada di Sistem Tiket:
     * Jika tiket sudah disetujui (status berubah menjadi Dalam Proses),
     * upaya persetujuan kedua harus ditolak (422).
     */
    public function test_double_approve_ticket_should_fail(): void
    {
        $deptUmum = InternalDepartment::where('slug', 'umum')->first();
        $category = TicketCategory::create([
            'department_id' => $deptUmum->id,
            'nama'          => 'Perbaikan AC',
            'kode'          => 'PB-AC',
            'sla_jam'       => 24,
            'is_active'     => true,
        ]);

        $kabag = User::factory()->create([
            'role_id'   => 10, // kabag_umum
            'is_active' => true,
        ]);

        $staf = User::factory()->create([
            'role_id'   => 13, // staf_umum
            'is_active' => true,
        ]);

        $pemohon = User::factory()->create([
            'role_id'   => 6, // user
            'is_active' => true,
        ]);

        $tiket = Ticket::create([
            'nomor_tiket'     => 'TIK-202609-001',
            'pemohon_id'      => $pemohon->id,
            'department_id'   => $deptUmum->id,
            'kategori_id'     => $category->id,
            'judul'           => 'Permintaan perbaikan AC',
            'deskripsi'       => 'AC di ruang meeting tidak dingin',
            'status'          => Ticket::STATUS_DIALOKASIKAN,
            'prioritas'       => 'tinggi',
            'jenis_pengajuan' => 'fasilitas',
        ]);

        // 1. Approval pertama oleh Kabag — harus BERHASIL (302 redirect back)
        $resp1 = $this->actingAs($kabag)->post(route('tiket.setujui', $tiket), [
            'staf_id' => $staf->id,
            'catatan' => 'Silakan dikerjakan',
        ]);
        $resp1->assertRedirect();

        $tiket->refresh();
        $this->assertEquals(Ticket::STATUS_DALAM_PROSES, $tiket->status);

        // 2. Approval kedua untuk tiket yang SAMA — harus DITOLAK dengan status 422
        $resp2 = $this->actingAs($kabag)->post(route('tiket.setujui', $tiket), [
            'staf_id' => $staf->id,
            'catatan' => 'Approval kedua oleh kabag lain/duplikat',
        ]);
        $resp2->assertStatus(422);
    }
}
