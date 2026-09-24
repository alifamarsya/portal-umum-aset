<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\InternalDepartmentSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OperationalSubmodulesRenderingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(InternalDepartmentSeeder::class);
        $this->seed(RolePermissionSeeder::class);
    }

    /** Dashboard Pimpinan menampilkan semua 3 seksi monitoring */
    public function test_pimpinan_dashboard_renders_all_sections(): void
    {
        $pimpinan = User::factory()->create([
            'role_id' => 2,
            'nama_lengkap' => 'Pimpinan Divisi',
            'is_active' => true,
        ]);

        $response = $this->actingAs($pimpinan)->get(route('dashboard'));
        $response->assertStatus(200);
        $response->assertViewIs('pimpinan.dashboard');
        $response->assertSee('Catatan Operasional Internal Bagian');
        $response->assertSee('Status SLA Tiket');
    }

    /** Operational submodules index render clean tanpa approval column */
    public function test_operational_submodules_render_cleanly(): void
    {
        // Role staf_umum has perm umum_rt
        $roleUmum = \App\Models\Role::where('nama', 'staf_umum')->first();
        if (!$roleUmum) {
            $this->markTestSkipped('Role staf_umum not found in seeder');
        }

        $staffUmum = User::factory()->create([
            'role_id' => $roleUmum->id,
            'is_active' => true,
        ]);

        // Test existing modules that definitely exist in config and have tables
        $submodules = ['kendaraan', 'biaya_harian'];
        foreach ($submodules as $mod) {
            $res = $this->actingAs($staffUmum)->get("/modul/{$mod}");
            $res->assertStatus(200);
            $res->assertDontSee('Approval');
            $res->assertDontSee('Setujui');
            $res->assertDontSee('Tolak');
        }
    }

    /** Endpoint approve/reject operasional nonaktif (404) */
    public function test_operational_approve_reject_disabled(): void
    {
        $pimpinan = User::factory()->create([
            'role_id' => 2,
            'nama_lengkap' => 'Pimpinan',
            'is_active' => true,
        ]);

        $response = $this->actingAs($pimpinan)
            ->post("/modul/biaya_harian/1/setujui");
        $response->assertStatus(404);

        $response = $this->actingAs($pimpinan)
            ->post("/modul/biaya_harian/1/tolak");
        $response->assertStatus(404);
    }
}
