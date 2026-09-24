<?php

namespace App\Http\Controllers;

use App\Concerns\LogsAudit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    use LogsAudit;

    /**
     * Tampilkan halaman profil pengguna yang sedang login.
     */
    public function show()
    {
        return view('profile.show', ['user' => auth()->user()]);
    }

    /**
     * Tampilkan form ubah password (dari dalam aplikasi, setelah login normal).
     */
    public function changePasswordForm()
    {
        return view('profile.change-password');
    }

    /**
     * Proses ubah password dari dalam aplikasi.
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'password_lama'         => 'required|string',
            'password'              => 'required|string|min:8|confirmed',
        ], [
            'password_lama.required'     => 'Password lama wajib diisi.',
            'password.required'          => 'Password baru wajib diisi.',
            'password.min'               => 'Password baru minimal 8 karakter.',
            'password.confirmed'         => 'Konfirmasi password baru tidak cocok.',
        ]);

        $user = auth()->user();

        // Cek password lama
        if (!Hash::check($request->password_lama, $user->password)) {
            return back()->withErrors(['password_lama' => 'Password lama tidak sesuai.'])->withInput();
        }

        $user->update([
            'password' => bcrypt($request->password),
        ]);

        $this->audit('UPDATE', 'Profil', 'User', $user->id, 'Mengubah password dari halaman profil');

        return redirect()->route('profile.show')->with('status', 'Password berhasil diubah.');
    }
}
