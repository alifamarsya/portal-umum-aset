<?php

namespace Tests\Feature;

use App\Models\InternalDepartment;
use App\Models\Role;
use App\Models\Ticket;
use App\Models\User;
use Database\Seeders\InternalDepartmentSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardRoleRenderingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(InternalDepartmentSeeder::class);
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_admin_dashboard_renders_properly(): void
    {
        $admin = User::factory()->create([
            'role_id' => 1,
            'nama_lengkap' => 'Administrator Utama',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.dashboard');
        $response->assertSee('Administrator Workspace');
        $response->assertSee('Total Tiket');
        $response->assertSee('Menunggu Verifikasi');
        $response->assertSee('Dalam Proses');
        $response->assertSee('Selesai Hari Ini');
        $response->assertSee('User Aktif');
        $response->assertSee('Kelola User');
        $response->assertSee('Audit Log');
        $response->assertDontSee('Unit Kerja');
    }

    public function test_pimpinan_dashboard_renders_properly(): void
    {
        $pimpinan = User::factory()->create([
            'role_id' => 2,
            'nama_lengkap' => 'Direktur Eksekutif',
            'is_active' => true,
        ]);

        $response = $this->actingAs($pimpinan)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertViewIs('pimpinan.dashboard');
        $response->assertSee('Executive Dashboard');
        $response->assertSee('Monitoring terpadu');
        $response->assertSee('Total Tiket');
        $response->assertSee('Menunggu');
        $response->assertSee('Dialokasikan');
        $response->assertSee('Dalam Proses');
        $response->assertSee('Selesai');
        $response->assertSee('Ditutup / Tolak');
        $response->assertDontSee('Unit Kerja');
    }

    public function test_operator_dashboard_renders_properly(): void
    {
        $operator = User::factory()->create([
            'role_id' => 7,
            'nama_lengkap' => 'Operator Helpdesk',
            'is_active' => true,
        ]);

        $response = $this->actingAs($operator)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertViewIs('operator.dashboard');
        $response->assertSee('Dashboard Operator Helpdesk');
        $response->assertSee('Antrean Tiket Baru');
        $response->assertSee('Antrean Tiket Menunggu Verifikasi &amp; Alokasi', false);
        $response->assertDontSee('Unit Kerja');
    }

    public function test_kabag_dashboards_render_properly(): void
    {
        $kabagRoles = [
            10 => 'Bagian Umum & Rumah Tangga',
            11 => 'Bagian Aset & Logistik',
            12 => 'Bagian Pengadaan',
        ];

        foreach ($kabagRoles as $roleId => $deptName) {
            $kabag = User::factory()->create([
                'role_id' => $roleId,
                'nama_lengkap' => 'Kepala ' . $deptName,
                'is_active' => true,
            ]);

            $response = $this->actingAs($kabag)->get(route('dashboard'));

            $response->assertStatus(200);
            $response->assertViewIs('kabag.dashboard');
            $response->assertSee('Dashboard Kepala Bagian');
            $response->assertSee('Butuh Disposisi');
            $response->assertSee('Dikerjakan Staf');
            $response->assertSee('Antrean Disposisi &amp; Persetujuan Tiket', false);
            $response->assertDontSee('Unit Kerja');
        }
    }

    public function test_staf_dashboards_render_properly_as_multi_user(): void
    {
        $stafRoles = [
            13 => 'Bagian Umum & Rumah Tangga',
            14 => 'Bagian Aset & Logistik',
            15 => 'Bagian Pengadaan',
        ];

        foreach ($stafRoles as $roleId => $deptName) {
            // Multi-user: Create 2 staff accounts in the same department
            $staf1 = User::factory()->create([
                'role_id' => $roleId,
                'nama_lengkap' => 'Staf 1 ' . $deptName,
                'is_active' => true,
            ]);
            $staf2 = User::factory()->create([
                'role_id' => $roleId,
                'nama_lengkap' => 'Staf 2 ' . $deptName,
                'is_active' => true,
            ]);

            $response1 = $this->actingAs($staf1)->get(route('dashboard'));
            $response1->assertStatus(200);
            $response1->assertViewIs('staf.dashboard');
            $response1->assertSee('Tim Staf Pelaksana');
            $response1->assertSee('Tugas Aktif Bagian');
            $response1->assertSee('Tugas Ditugaskan ke Saya');
            $response1->assertSee('Tiket Pelaksanaan Tugas Aktif');
            // Ensure no unit kerja labels/submenus exist
            $response1->assertDontSee('Unit Kerja');

            $response2 = $this->actingAs($staf2)->get(route('dashboard'));
            $response2->assertStatus(200);
            $response2->assertViewIs('staf.dashboard');
            $response2->assertDontSee('Unit Kerja');
        }
    }

    public function test_user_dashboard_renders_properly(): void
    {
        $user = User::factory()->create([
            'role_id' => 6,
            'nama_lengkap' => 'Karyawan Pemohon',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertViewIs('user.dashboard');
        $response->assertSee('Helpdesk &amp; Layanan Operasional', false);
        $response->assertSee('Buat Tiket Baru');
        $response->assertSee('Total Diajukan');
        $response->assertSee('Menunggu Verifikasi');
        $response->assertSee('Sedang Diproses');
        $response->assertSee('Siap Dikonfirmasi');
        $response->assertDontSee('Unit Kerja');
    }
}
