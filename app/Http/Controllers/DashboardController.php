<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Menampilkan halaman dashboard.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Mengambil data yang diperlukan untuk dashboard (misalnya, statistik atau informasi lain)
        // Misalnya, Anda bisa menampilkan total users, order, dsb.

        $totalUsers = \App\Models\User::count();
        // $totalOrders = \App\Models\Order::count();

        // Menyediakan data untuk view dashboard
        return view('dashboard', compact('totalUsers', 'totalOrders'));
    }

    /**
     * Metode lain jika diperlukan untuk fitur lainnya (misalnya overview).
     */
    public function overview()
    {
        // Fungsi untuk memberikan overview atau informasi tambahan
        return view('dashboard-overview');
    }
}
