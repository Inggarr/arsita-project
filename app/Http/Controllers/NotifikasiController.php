<?php

namespace App\Http\Controllers;

use App\Models\Notifikasi;
use Illuminate\Support\Facades\Auth;

class NotifikasiController extends Controller
{
    /**
     * Ambil data notifikasi user saat ini (untuk polling realtime lonceng)
     */
    public function getNotifications()
    {
        $user = Auth::user();
        if (! $user) {
            return response()->json(['unread_count' => 0, 'data' => []]);
        }

        $notifikasi = Notifikasi::with('pengirim')
            ->where('user_id', $user->id_user)
            ->latest('id_notifikasi')
            ->limit(15)
            ->get();

        $unreadCount = Notifikasi::where('user_id', $user->id_user)
            ->where('is_read', false)
            ->count();

        return response()->json([
            'unread_count' => $unreadCount,
            'data' => $notifikasi->map(function ($item) {
                return [
                    'id' => $item->id_notifikasi,
                    'judul' => $item->judul,
                    'pesan' => $item->pesan,
                    'url' => $item->url ?: '#',
                    'tipe' => $item->tipe,
                    'is_read' => (bool) $item->is_read,
                    'waktu' => $item->created_at ? $item->created_at->diffForHumans() : 'Baru saja',
                    'pengirim' => $item->pengirim ? ($item->pengirim->nama ?: $item->pengirim->name) : 'Sistem',
                ];
            }),
        ]);
    }

    /**
     * Tandai satu notifikasi sebagai sudah dibaca dan arahkan ke URL-nya
     */
    public function readAndRedirect($id)
    {
        $user = Auth::user();
        $notif = Notifikasi::where('id_notifikasi', $id)
            ->where('user_id', $user->id_user)
            ->firstOrFail();

        $notif->update(['is_read' => true]);

        return redirect($notif->url ?: route('dashboard'));
    }

    /**
     * Tandai semua notifikasi sudah dibaca via AJAX
     */
    public function markAllAsRead()
    {
        $user = Auth::user();
        Notifikasi::where('user_id', $user->id_user)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json(['success' => true]);
    }
}
