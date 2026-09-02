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

    // Helper: setup roles & users
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

        // Staf Umum punya permission can_write = true ke umum_rt
        RolePermission::create([
            'role_id'   => $roleStaffUmum->id,
            'perm_key'  => 'umum_rt',
            'can_write' => true,
        ]);

        // Staf Aset punya permission can_write = true ke aset_logistik
        RolePermission::create([
            'role_id'   => $roleStaffAset->id,
            'perm_key'  => 'aset_logistik',
            'can_write' => true,
        ]);

        $admin = User::factory()->create([
            'username' => 'admin',
            'role_id'  => $roleAdmin->id,
        ]);

        $checker = User::factory()->create([
            'username' => 'pimpinan',
            'role_id'  => $rolePimpinan->id,
        ]);

        $maker = User::factory()->create([
            'username' => 'adol',
            'role_id'  => $roleStaffUmum->id,
        ]);

        $otherStaff = User::factory()->create([
            'username' => 'irma',
            'role_id'  => $roleStaffAset->id,
        ]);

        return [$maker, $checker, $admin, $otherStaff];
    }

    /** Test 1: Staf Maker bisa membuat transaksi baru (status Diajukan) */
    public function test_staff_can_create_transaction_as_maker(): void
    {
        [$maker] = $this->setupUsers();

        $response = $this->actingAs($maker)->post('/modul/biaya_harian', [
            'tanggal'    => now()->toDateString(),
            'kategori'   => 'BBM',
            'nama_beban' => 'Bensin operasional',
            'jumlah'     => 150000,
            'uraian'     => 'Isi bensin dinas',
        ]);

        $response->assertRedirect('/modul/biaya_harian');
        $this->assertDatabaseHas('um_biaya_harian', [
            'jumlah'          => 150000,
            'maker_id'        => $maker->id,
            'approval_status' => 'Diajukan',
        ]);
    }

    /** Test 2: Admin TIDAK BISA membuat transaksi (hanya read-only) */
    public function test_admin_cannot_create_transaction(): void
    {
        [, , $admin] = $this->setupUsers();

        $response = $this->actingAs($admin)->post('/modul/biaya_harian', [
            'tanggal'    => now()->toDateString(),
            'kategori'   => 'BBM',
            'nama_beban' => 'Bensin operasional',
            'jumlah'     => 150000,
            'uraian'     => 'Admin mencoba input',
        ]);

        $response->assertStatus(403);
    }

    /** Test 3: Pimpinan TIDAK BISA membuat transaksi (hanya Checker) */
    public function test_pimpinan_cannot_create_transaction(): void
    {
        [, $checker] = $this->setupUsers();

        $response = $this->actingAs($checker)->post('/modul/biaya_harian', [
            'tanggal'    => now()->toDateString(),
            'kategori'   => 'BBM',
            'nama_beban' => 'Bensin operasional',
            'jumlah'     => 150000,
            'uraian'     => 'Pimpinan mencoba input',
        ]);

        $response->assertStatus(403);
    }

    /** Test 4: Maker TIDAK BISA self-approve transaksinya sendiri */
    public function test_maker_cannot_approve_own_transaction(): void
    {
        [$maker] = $this->setupUsers();

        $transaksi = UmBiayaHarian::create([
            'tanggal'         => now()->toDateString(),
            'jumlah'          => 50000,
            'kategori'        => 'BBM',
            'uraian'          => 'Test self-approve',
            'maker_id'        => $maker->id,
            'approval_status' => 'Diajukan',
        ]);

        $response = $this->actingAs($maker)
            ->post("/modul/biaya_harian/{$transaksi->id}/setujui");

        $response->assertStatus(403);
        $this->assertDatabaseHas('um_biaya_harian', [
            'id'              => $transaksi->id,
            'approval_status' => 'Diajukan',
        ]);
    }

    /** Test 5: Staf lain TIDAK BISA approve transaksi (Staf bukan Checker) */
    public function test_other_staff_cannot_approve_transaction(): void
    {
        [$maker, , , $otherStaff] = $this->setupUsers();

        $transaksi = UmBiayaHarian::create([
            'tanggal'         => now()->toDateString(),
            'jumlah'          => 60000,
            'kategori'        => 'BBM',
            'uraian'          => 'Test approve oleh staf lain',
            'maker_id'        => $maker->id,
            'approval_status' => 'Diajukan',
        ]);

        $response = $this->actingAs($otherStaff)
            ->post("/modul/biaya_harian/{$transaksi->id}/setujui");

        $response->assertStatus(403);
    }

    /** Test 6: Admin TIDAK BISA approve transaksi (Admin bukan Checker) */
    public function test_admin_cannot_approve_transaction(): void
    {
        [$maker, , $admin] = $this->setupUsers();

        $transaksi = UmBiayaHarian::create([
            'tanggal'         => now()->toDateString(),
            'jumlah'          => 70000,
            'kategori'        => 'BBM',
            'uraian'          => 'Test approve oleh admin',
            'maker_id'        => $maker->id,
            'approval_status' => 'Diajukan',
        ]);

        $response = $this->actingAs($admin)
            ->post("/modul/biaya_harian/{$transaksi->id}/setujui");

        $response->assertStatus(403);
    }

    /** Test 7: Pimpinan (Checker) BISA approve transaksi yang diajukan staf */
    public function test_pimpinan_can_approve_transaction(): void
    {
        [$maker, $checker] = $this->setupUsers();

        $transaksi = UmBiayaHarian::create([
            'tanggal'         => now()->toDateString(),
            'jumlah'          => 75000,
            'kategori'        => 'Perawatan',
            'uraian'          => 'Test normal approve',
            'maker_id'        => $maker->id,
            'approval_status' => 'Diajukan',
        ]);

        $response = $this->actingAs($checker)
            ->post("/modul/biaya_harian/{$transaksi->id}/setujui");

        $response->assertRedirect();
        $this->assertDatabaseHas('um_biaya_harian', [
            'id'              => $transaksi->id,
            'approval_status' => 'Disetujui',
            'checker_id'      => $checker->id,
        ]);
    }

    /** Test 8: Pimpinan (Checker) BISA tolak (reject) transaksi */
    public function test_pimpinan_can_reject_transaction(): void
    {
        [$maker, $checker] = $this->setupUsers();

        $transaksi = UmBiayaHarian::create([
            'tanggal'         => now()->toDateString(),
            'jumlah'          => 85000,
            'kategori'        => 'Perawatan',
            'uraian'          => 'Test tolak transaksi',
            'maker_id'        => $maker->id,
            'approval_status' => 'Diajukan',
        ]);

        $response = $this->actingAs($checker)
            ->post("/modul/biaya_harian/{$transaksi->id}/tolak", [
                'catatan' => 'Harga tidak wajar',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('um_biaya_harian', [
            'id'               => $transaksi->id,
            'approval_status'  => 'Ditolak',
            'checker_id'       => $checker->id,
            'catatan_approval' => 'Harga tidak wajar',
        ]);
    }

    /** Test 9: Transaksi yang sudah disetujui tidak bisa disetujui lagi */
    public function test_already_approved_cannot_be_approved_again(): void
    {
        [$maker, $checker] = $this->setupUsers();

        $transaksi = UmBiayaHarian::create([
            'tanggal'         => now()->toDateString(),
            'jumlah'          => 200000,
            'kategori'        => 'Rumah Tangga',
            'uraian'          => 'Sudah disetujui',
            'maker_id'        => $maker->id,
            'approval_status' => 'Disetujui',
            'checker_id'      => $checker->id,
        ]);

        $response = $this->actingAs($checker)
            ->post("/modul/biaya_harian/{$transaksi->id}/setujui");

        $response->assertStatus(403);
    }
}
