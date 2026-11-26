<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

// Diadaptasi dari NotificationController portum (method dan route sama).
// Menampilkan daftar notifikasi user, menandai satu notifikasi sebagai dibaca
// (lalu mengarahkan ke halaman terkait), dan menandai semua sebagai dibaca.
class NotificationController extends Controller
{
    /**
     * Menampilkan daftar notifikasi user (15 per halaman).
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $notifications = $user->notifications()->paginate(15);

        return view('notifications.index', compact('notifications'));
    }

    /**
     * Menandai satu notifikasi sebagai dibaca, lalu mengarahkan ke URL tujuan
     * yang tersimpan di data notifikasi (misalnya halaman detail tiket).
     */
    public function read(Request $request, string $id)
    {
        $user = auth()->user();
        $notification = $user->notifications()->where('id', $id)->firstOrFail();

        if (is_null($notification->read_at)) {
            $notification->markAsRead();
        }

        if ($request->wantsJson()) {
            return response()->json(['status' => 'success']);
        }

        $targetUrl = $notification->data['action_url'] ?? route('dashboard');

        return redirect($targetUrl);
    }

    /**
     * Menandai semua notifikasi yang belum dibaca sebagai sudah dibaca.
     */
    public function markAllAsRead(Request $request)
    {
        $user = auth()->user();
        $user->unreadNotifications->markAsRead();

        if ($request->wantsJson()) {
            return response()->json(['status' => 'success']);
        }

        return back()->with('status', 'Semua notifikasi telah ditandai sudah dibaca.');
    }
}
