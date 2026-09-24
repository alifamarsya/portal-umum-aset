<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\RolePermission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminUserRoleCrudTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Role $roleAdmin;
    private Role $rolePimpinan;
    private Role $roleStaff;

    protected function setUp(): void
    {
        parent::setUp();

        $this->roleAdmin = Role::create(['id' => 1, 'nama' => 'superadmin', 'label' => 'Super Administrator']);
        $this->rolePimpinan = Role::create(['id' => 2, 'nama' => 'pimpinan', 'label' => 'Pimpinan Divisi']);
        $this->roleStaff = Role::create(['id' => 3, 'nama' => 'umum_rt', 'label' => 'Staf Umum & RT']);

        RolePermission::create(['role_id' => $this->roleAdmin->id, 'perm_key' => 'user_mgmt', 'can_write' => true]);
        RolePermission::create(['role_id' => $this->roleAdmin->id, 'perm_key' => 'role_mgmt', 'can_write' => true]);

        $this->admin = User::factory()->create([
            'username' => 'admin_test',
            'role_id' => $this->roleAdmin->id,
            'is_active' => true,
        ]);
    }

    public function test_admin_can_access_users_and_roles_index(): void
    {
        $responseUsers = $this->actingAs($this->admin)->get(route('admin.users.index'));
        $responseUsers->assertStatus(200);
        $responseUsers->assertSee('Manajemen Pengguna (User)');

        $responseRoles = $this->actingAs($this->admin)->get(route('admin.roles.index'));
        $responseRoles->assertStatus(200);
        $responseRoles->assertSee('Manajemen Role & Hak Akses');
    }

    public function test_admin_can_create_user_with_custom_password(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.users.store'), [
            'username' => 'johndoe',
            'nama_lengkap' => 'John Doe',
            'email' => 'john@banksulteng.co.id',
            'jabatan' => 'Staff Pengadaan',
            'bagian' => 'Divisi Umum',
            'role_id' => $this->roleStaff->id,
            'password' => 'rahasia123',
            'is_active' => 1,
            'must_change_pwd' => 0,
        ]);

        $response->assertSessionHas('status');
        $this->assertDatabaseHas('users', [
            'username' => 'johndoe',
            'nama_lengkap' => 'John Doe',
            'email' => 'john@banksulteng.co.id',
            'is_active' => 1,
            'must_change_pwd' => 0,
        ]);

        $created = User::where('username', 'johndoe')->first();
        $this->assertTrue(Hash::check('rahasia123', $created->password));
    }

    public function test_admin_can_update_user_and_password(): void
    {
        $targetUser = User::factory()->create([
            'username' => 'targetuser',
            'nama_lengkap' => 'Nama Awal',
            'role_id' => $this->roleStaff->id,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin)->put(route('admin.users.update', $targetUser), [
            'username' => 'targetuser_updated',
            'nama_lengkap' => 'Nama Baru',
            'email' => 'target@banksulteng.co.id',
            'jabatan' => 'Supervisor',
            'bagian' => 'Logistik',
            'role_id' => $this->roleStaff->id,
            'is_active' => 1,
            'password' => 'passwordbaru123',
        ]);

        $response->assertSessionHas('status');
        $targetUser->refresh();
        $this->assertEquals('targetuser_updated', $targetUser->username);
        $this->assertEquals('Nama Baru', $targetUser->nama_lengkap);
        $this->assertTrue(Hash::check('passwordbaru123', $targetUser->password));
    }

    public function test_admin_cannot_delete_self(): void
    {
        $response = $this->actingAs($this->admin)->delete(route('admin.users.destroy', $this->admin));
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('users', ['id' => $this->admin->id]);
    }

    public function test_admin_can_delete_other_user(): void
    {
        $userToDelete = User::factory()->create([
            'username' => 'delete_me',
            'role_id' => $this->roleStaff->id,
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.users.destroy', $userToDelete));
        $response->assertSessionHas('status');
        $this->assertDatabaseMissing('users', ['id' => $userToDelete->id]);
    }

    public function test_admin_can_create_custom_role(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.roles.store'), [
            'nama' => 'staf_audit',
            'label' => 'Staf Auditor Internal',
            'deskripsi' => 'Melakukan pemeriksaan kepatuhan',
        ]);

        $response->assertSessionHas('status');
        $this->assertDatabaseHas('roles', [
            'nama' => 'staf_audit',
            'label' => 'Staf Auditor Internal',
        ]);
    }

    public function test_admin_can_update_role_permissions(): void
    {
        $role = Role::create([
            'nama' => 'custom_role',
            'label' => 'Custom Role',
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.roles.permissions', $role), [
            'access_umum_rt' => '1',
            'write_umum_rt' => '1',
            'access_tiket' => '1',
            'write_tiket' => '0',
            // pengadaan is not checked
        ]);

        $response->assertSessionHas('status');

        $this->assertDatabaseHas('role_permissions', [
            'role_id' => $role->id,
            'perm_key' => 'umum_rt',
            'can_write' => 1,
        ]);

        $this->assertDatabaseHas('role_permissions', [
            'role_id' => $role->id,
            'perm_key' => 'tiket',
            'can_write' => 0,
        ]);

        $this->assertDatabaseMissing('role_permissions', [
            'role_id' => $role->id,
            'perm_key' => 'pengadaan',
        ]);
    }

    public function test_admin_cannot_delete_system_role(): void
    {
        $response = $this->actingAs($this->admin)->delete(route('admin.roles.destroy', $this->roleAdmin));
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('roles', ['id' => $this->roleAdmin->id]);
    }

    public function test_admin_can_delete_custom_role_without_users(): void
    {
        $customRole = Role::create([
            'nama' => 'role_to_delete',
            'label' => 'Role Hapus',
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.roles.destroy', $customRole));
        $response->assertSessionHas('status');
        $this->assertDatabaseMissing('roles', ['id' => $customRole->id]);
    }
}
