<?php

namespace App\Http\Controllers;

use App\Models\Visitors;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VisitorsController extends Controller
{
    // Total visits (all records)
    public function total()
    {
        $total = Visitors::count();
        return response()->json(['total' => $total]);
    }

    // Unique visitors today (distinct IP)
    public function uniqueToday()
    {
        $count = Visitors::whereDate('created_at', today())
            ->distinct('ip_address')
            ->count('ip_address');

        return response()->json(['unique_today' => $count]);
    }

    // Visits by country (last 30 days)
    public function byCountry(Request $request)
    {
        $days = $request->query('days', 30);

        $data = Visitors::select('country', DB::raw('count(*) as total'))
            ->where('country', '<>', '')
            ->where('is_bot', false)
            ->where('created_at', '>=', now()->subDays($days))
            ->groupBy('country')
            ->orderByDesc('total')
            ->get();

        return response()->json($data);
    }

    // Top pages (paths) in last N days
    public function topPages(Request $request)
    {
        $days = $request->query('days', 30);

        $data = Visitors::select('path', DB::raw('count(*) as total'))
            ->where('is_bot', false)
            ->where('created_at', '>=', now()->subDays($days))
            ->groupBy('path')
            ->orderByDesc('total')
            ->limit(20)
            ->get();

        return response()->json($data);
    }

    // Simple timeseries: visits per day last N days
    public function visitsPerDay(Request $request)
    {
        $days = (int) $request->query('days', 30);

        $data = Visitors::select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as total'))
            ->where('created_at', '>=', now()->subDays($days))
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date')
            ->get();

        return response()->json($data);
    }
}
