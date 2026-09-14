<?php

namespace App\Http\Controllers\Admin;

use App\Concerns\LogsAudit;
use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class UserController extends Controller
{
    use LogsAudit;

    public function index()
    {
        $items = User::with('role')->orderBy('username')->get();
        $roles = Role::orderBy('label')->get();
        return view('admin.users.index', compact('items', 'roles'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'username' => 'required|string|max:100|unique:users,username',
            'nama_lengkap' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'jabatan' => 'nullable|string|max:255',
            'bagian' => 'nullable|string|max:255',
            'role_id' => 'required|exists:roles,id',
            'password' => 'nullable|string|min:6',
            'must_change_pwd' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        $customPassword = $request->filled('password');
        $plain = $customPassword ? $request->input('password') : Str::random(10);

        $data['password'] = bcrypt($plain);
        $data['must_change_pwd'] = $request->boolean('must_change_pwd', !$customPassword);
        $data['is_active'] = $request->boolean('is_active', true);

        $user = User::create($data);
        $this->audit('CREATE', 'Manajemen User', 'User', $user->id, "Menambah user {$user->username}");

        $msg = $customPassword
            ? "User {$user->username} berhasil dibuat dengan password yang ditentukan."
            : "User {$user->username} berhasil dibuat. Password sementara: {$plain}";

        return back()->with('status', $msg);
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'username' => 'required|string|max:100|unique:users,username,' . $user->id,
            'nama_lengkap' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'jabatan' => 'nullable|string|max:255',
            'bagian' => 'nullable|string|max:255',
            'role_id' => 'required|exists:roles,id',
            'is_active' => 'nullable|boolean',
            'password' => 'nullable|string|min:6',
            'must_change_pwd' => 'nullable|boolean',
        ]);

        // Proteksi: admin tidak boleh menonaktifkan akun sendiri
        if (auth()->id() === $user->id && !$request->boolean('is_active', true)) {
            return back()->with('error', 'Anda tidak dapat menonaktifkan akun Anda sendiri yang sedang aktif.');
        }

        // Proteksi: tidak boleh mengubah role superadmin terakhir
        if ($user->role?->nama === 'superadmin' && (int) $data['role_id'] !== $user->role_id) {
            $superadminCount = User::whereHas('role', fn ($q) => $q->where('nama', 'superadmin'))->count();
            if ($superadminCount <= 1) {
                return back()->with('error', 'Tidak dapat mengubah role satu-satunya Super Administrator.');
            }
        }

        $data['is_active'] = $request->boolean('is_active', false);

        if ($request->filled('password')) {
            $data['password'] = bcrypt($request->input('password'));
            $data['must_change_pwd'] = $request->boolean('must_change_pwd', false);
        } else {
            unset($data['password']);
            if ($request->has('must_change_pwd')) {
                $data['must_change_pwd'] = $request->boolean('must_change_pwd');
            }
        }

        $user->update($data);
        $this->audit('UPDATE', 'Manajemen User', 'User', $user->id, "Mengubah data user {$user->username}");

        return back()->with('status', "Data user {$user->username} berhasil diperbarui.");
    }

    public function resetPassword(Request $request, User $user)
    {
        $request->validate([
            'new_password' => 'nullable|string|min:6',
            'must_change_pwd' => 'nullable|boolean',
        ]);

        $custom = $request->filled('new_password');
        $plain = $custom ? $request->input('new_password') : Str::random(10);
        $mustChange = $request->boolean('must_change_pwd', !$custom);

        $user->update([
            'password' => bcrypt($plain),
            'must_change_pwd' => $mustChange,
        ]);

        $this->audit('UPDATE', 'Manajemen User', 'User', $user->id, "Reset password user {$user->username}");

        $msg = $custom
            ? "Password user {$user->username} berhasil diperbarui menjadi password baru."
            : "Password {$user->username} direset. Password sementara: {$plain}";

        return back()->with('status', $msg);
    }

    public function destroy(User $user)
    {
        // Proteksi 1: Tidak boleh menghapus akun sendiri
        if (auth()->id() === $user->id) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri yang sedang aktif.');
        }

        // Proteksi 2: Tidak boleh menghapus satu-satunya superadmin
        if ($user->role?->nama === 'superadmin') {
            $superadminCount = User::whereHas('role', fn ($q) => $q->where('nama', 'superadmin'))->count();
            if ($superadminCount <= 1) {
                return back()->with('error', 'Tidak dapat menghapus satu-satunya akun Super Administrator.');
            }
        }

        $id = $user->id;
        $username = $user->username;
        $user->delete();
        $this->audit('DELETE', 'Manajemen User', 'User', $id, "Menghapus user {$username}");

        return back()->with('status', "User {$username} berhasil dihapus dari sistem.");
    }
}
