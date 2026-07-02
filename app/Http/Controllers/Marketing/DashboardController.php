<?php

namespace App\Http\Controllers\Marketing;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\News;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $userId = auth()->id();
        $today = Carbon::today()->format('Y-m-d');

        // 1. Variabel Statistik (Kotak-kotak atas)
        $totalLeads = Lead::assignedTo($userId)->count();
        $hotLeads = Lead::assignedTo($userId)->where('status_prospek', 'Hot')->count();
        $todaysTasks = Lead::assignedTo($userId)
            ->activeFollowUps()
            ->whereDate('tgl_next_followup', '=', $today)
            ->count();
        $overdueLeads = Lead::assignedTo($userId)
            ->activeFollowUps()
            ->whereDate('tgl_next_followup', '<', $today)
            ->count();

        // 2. Variabel Banner Notifikasi (Baris 5)
        $todayTasksCount = $todaysTasks + $overdueLeads;

        // 3. Variabel Follow-up List (Baris 121)
        $todaysFollowups = Lead::assignedTo($userId)
            ->activeFollowUps()
            ->todaysTasks()
            ->orderByRaw("CASE WHEN status_prospek = 'Hot' THEN 0 ELSE 1 END")
            ->orderBy('tgl_next_followup', 'asc')
            ->limit(5)
            ->get();

        // 4. Variabel Berita (Baris 160) - Harus 'recentNews'
        $recentNews = News::latest()->limit(3)->get();

        // 5. Leads by Status (untuk breakdown card)
        $leadsByStatus = Lead::assignedTo($userId)
            ->selectRaw('status_prospek, count(*) as total')
            ->groupBy('status_prospek')
            ->pluck('total', 'status_prospek')
            ->toArray();

        // 6. Conversion Rate
        $dealCount = $leadsByStatus['Deal'] ?? 0;
        $conversionRate = $totalLeads > 0 ? round(($dealCount / $totalLeads) * 100, 1) : 0;

        // Kirim semuanya dengan nama yang pas sesuai permintaan file Blade
        return view('marketing.dashboard', compact(
            'totalLeads',
            'hotLeads',
            'todaysTasks',
            'overdueLeads',
            'todayTasksCount',
            'todaysFollowups',
            'recentNews',
            'leadsByStatus',
            'conversionRate'
        ));
    }
}
