<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Team;
use App\Models\Service;
use App\Models\Work;
use App\Models\PhoneRecord;
use App\Models\WhatsAppRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class AnalyticsController extends Controller
{
    public function overview(Request $request)
    {
        $total_brands = Brand::count();
        $total_team = Team::count();
        $total_services = Service::count();
        $total_works = Work::count();
        // For bar chart: get records by day for last 7 days
        $labels = [];
        $phoneRecords = [];
        $whatsappRecords = [];
        $start = Carbon::now()->subDays(6)->startOfDay();
        for ($i = 0; $i < 7; $i++) {
            $day = $start->copy()->addDays($i);
            $labels[] = $day->format('Y-m-d');
            $phoneRecords[] = PhoneRecord::whereDate('created_at', $day)->count();
            $whatsappRecords[] = WhatsAppRecord::whereDate('created_at', $day)->count();
        }
        return response()->json([
            'brands' => $total_brands,
            'team' => $total_team,
            'services' => $total_services,
            'works' => $total_works,
            'chart' => [
                'labels' => $labels,
                'phone' => $phoneRecords,
                'whatsapp' => $whatsappRecords,
            ],
        ]);
    }
}
