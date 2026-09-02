<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\RolePermission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ViewRenderingTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $pimpinan;
    private User $staff;

    protected function setUp(): void
    {
        parent::setUp();

        $roleAdmin = Role::create(['nama' => 'superadmin', 'label' => 'Super Administrator']);
        $rolePimpinan = Role::create(['nama' => 'pimpinan', 'label' => 'Pimpinan Divisi']);
        $roleStaff = Role::create(['nama' => 'umum_rt', 'label' => 'Staf Umum & RT']);

        RolePermission::create(['role_id' => $roleStaff->id, 'perm_key' => 'umum_rt', 'can_write' => true]);
        RolePermission::create(['role_id' => $roleStaff->id, 'perm_key' => 'risalah', 'can_write' => true]);

        $this->admin = User::factory()->create(['role_id' => $roleAdmin->id, 'is_active' => true]);
        $this->pimpinan = User::factory()->create(['role_id' => $rolePimpinan->id, 'is_active' => true]);
        $this->staff = User::factory()->create(['role_id' => $roleStaff->id, 'is_active' => true]);
    }

    public function test_admin_can_view_all_pages_without_error(): void
    {
        $this->actingAs($this->admin)->get('/')->assertStatus(200);
        $this->actingAs($this->admin)->get('/modul/kendaraan')->assertStatus(200);
        $this->actingAs($this->admin)->get('/modul/biaya_harian')->assertStatus(200);
        $this->actingAs($this->admin)->get('/risalah')->assertStatus(200);
        $this->actingAs($this->admin)->get('/panduan')->assertStatus(200);
        $this->actingAs($this->admin)->get('/analitik')->assertStatus(200);
    }

    public function test_pimpinan_can_view_all_pages_without_error(): void
    {
        $this->actingAs($this->pimpinan)->get('/')->assertStatus(200);
        $this->actingAs($this->pimpinan)->get('/modul/kendaraan')->assertStatus(200);
        $this->actingAs($this->pimpinan)->get('/modul/biaya_harian')->assertStatus(200);
        $this->actingAs($this->pimpinan)->get('/risalah')->assertStatus(200);
        $this->actingAs($this->pimpinan)->get('/panduan')->assertStatus(200);
        $this->actingAs($this->pimpinan)->get('/analitik')->assertStatus(200);
    }

    public function test_staff_can_view_authorized_pages_without_error(): void
    {
        $this->actingAs($this->staff)->get('/')->assertStatus(200);
        $this->actingAs($this->staff)->get('/modul/kendaraan')->assertStatus(200);
        $this->actingAs($this->staff)->get('/modul/biaya_harian')->assertStatus(200);
        $this->actingAs($this->staff)->get('/risalah')->assertStatus(200);
        $this->actingAs($this->staff)->get('/panduan')->assertStatus(200);
    }
}
