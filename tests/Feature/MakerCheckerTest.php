<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\RolePermission;
use App\Models\UmBiayaHarian;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MakerCheckerTest extends TestCase
{
    use RefreshDatabase;

    private function setupUsers(): array
    {
        $roleAdmin = Role::create([
            'nama'  => 'superadmin',
            'label' => 'Super Administrator',
        ]);

        $rolePimpinan = Role::create([
            'nama'  => 'pimpinan',
            'label' => 'Pimpinan Divisi',
        ]);

        $roleStaffUmum = Role::create([
            'nama'  => 'umum_rt',
            'label' => 'Staf Umum & Rumah Tangga',
        ]);

        $roleStaffAset = Role::create([
            'nama'  => 'aset',
            'label' => 'Staf Aset & Logistik',
        ]);

        RolePermission::create([
            'role_id'   => $roleStaffUmum->id,
            'perm_key'  => 'umum_rt',
            'can_write' => true,
        ]);

        RolePermission::create([
            'role_id'   => $roleStaffAset->id,
            'perm_key'  => 'aset_logistik',
            'can_write' => true,
        ]);

        $admin = User::factory()->create([
            'username' => 'admin',
            'role_id'  => $roleAdmin->id,
        ]);

        $pimpinan = User::factory()->create([
            'username' => 'pimpinan',
            'role_id'  => $rolePimpinan->id,
        ]);

        $staff = User::factory()->create([
            'username' => 'adol',
            'role_id'  => $roleStaffUmum->id,
        ]);

        $otherStaff = User::factory()->create([
            'username' => 'irma',
            'role_id'  => $roleStaffAset->id,
        ]);

        return [$staff, $pimpinan, $admin, $otherStaff];
    }

    /**
     * Data lengkap untuk biaya_harian sesuai config/modules.php (semua field req=true).
     */
    private function biayaHarianData(array $overrides = []): array
    {
        return array_merge([
            'tanggal'         => now()->toDateString(),
            'kategori'        => 'BBM',
            'kendaraan'       => 'B 1234 ABC',
            'nama_beban'      => 'Bensin operasional',
            'rekening_debet'  => '5201.01',
            'rekening_kredit' => '1101.01',
            'uraian'          => 'Isi bensin dinas',
            'jumlah'          => 150000,
            'no_nota'         => 'NT-001',
            'status'          => 'Draft',
        ], $overrides);
    }

    /** Test 1: Staf bisa mencatat data operasional langsung (catatan internal murni) */
    public function test_staff_can_record_operational_data_directly(): void
    {
        [$staff] = $this->setupUsers();

        $response = $this->actingAs($staff)->post('/modul/biaya_harian', $this->biayaHarianData());

        $response->assertRedirect('/modul/biaya_harian');
        $this->assertDatabaseHas('um_biaya_harian', [
            'jumlah' => 150000,
            'uraian' => 'Isi bensin dinas',
        ]);
    }

    /** Test 2: Admin TIDAK BISA menambah data operasional jika tidak punya can_write */
    public function test_admin_cannot_create_transaction(): void
    {
        [, , $admin] = $this->setupUsers();

        $response = $this->actingAs($admin)->post('/modul/biaya_harian', $this->biayaHarianData([
            'uraian' => 'Admin mencoba input',
        ]));

        $response->assertStatus(403);
    }

    /** Test 3: Pimpinan TIDAK BISA input data operasional jika tidak punya hak write modul */
    public function test_pimpinan_cannot_create_operational_record_without_write(): void
    {
        [, $pimpinan] = $this->setupUsers();

        $response = $this->actingAs($pimpinan)->post('/modul/biaya_harian', $this->biayaHarianData([
            'uraian' => 'Pimpinan mencoba input',
        ]));

        $response->assertStatus(403);
    }

    /** Test 4: Endpoint approve operasional nonaktif (404) karena approval di sistem tiket */
    public function test_operational_approve_endpoint_is_disabled(): void
    {
        [$staff, $pimpinan] = $this->setupUsers();

        $item = UmBiayaHarian::create([
            'tanggal'  => now()->toDateString(),
            'jumlah'   => 50000,
            'kategori' => 'BBM',
            'uraian'   => 'Pencatatan internal',
        ]);

        $response = $this->actingAs($pimpinan)
            ->post("/modul/biaya_harian/{$item->id}/setujui");

        $response->assertStatus(404);
    }

    /** Test 5: Endpoint reject operasional nonaktif (404) karena approval di sistem tiket */
    public function test_operational_reject_endpoint_is_disabled(): void
    {
        [$staff, $pimpinan] = $this->setupUsers();

        $item = UmBiayaHarian::create([
            'tanggal'  => now()->toDateString(),
            'jumlah'   => 85000,
            'kategori' => 'Perawatan',
            'uraian'   => 'Pencatatan internal',
        ]);

        $response = $this->actingAs($pimpinan)
            ->post("/modul/biaya_harian/{$item->id}/tolak", [
                'catatan' => 'Ditolak',
            ]);

        $response->assertStatus(404);
    }

    /** Test 6: Staf bisa update catatan operasional langsung tanpa checker */
    public function test_staff_can_update_operational_record_directly(): void
    {
        [$staff] = $this->setupUsers();

        $item = UmBiayaHarian::create([
            'tanggal'  => now()->toDateString(),
            'jumlah'   => 50000,
            'kategori' => 'BBM',
            'uraian'   => 'Catatan awal',
        ]);

        $response = $this->actingAs($staff)->put("/modul/biaya_harian/{$item->id}", $this->biayaHarianData([
            'jumlah' => 75000,
            'uraian' => 'Catatan direvisi',
        ]));

        $response->assertRedirect('/modul/biaya_harian');
        $this->assertDatabaseHas('um_biaya_harian', [
            'id'     => $item->id,
            'jumlah' => 75000,
            'uraian' => 'Catatan direvisi',
        ]);
    }
}
