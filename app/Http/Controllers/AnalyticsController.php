<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Team;
use App\Models\Service;
use App\Models\Work;
use App\Models\PhoneContacts;
use App\Models\WhatsAppContacts;
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

    // Get contacts and record counts
    $phoneContactData = PhoneContacts::withCount('records')->get(['name']);
    $whatsappContactData = WhatsAppContacts::withCount('records')->get(['name']);

    // Merge all unique contact names
    $allNames = $phoneContactData->pluck('name')
        ->merge($whatsappContactData->pluck('name'))
        ->unique()
        ->values();

    // Map counts
    $phoneCounts = $phoneContactData->mapWithKeys(fn($c) => [$c->name => $c->records_count]);
    $whatsappCounts = $whatsappContactData->mapWithKeys(fn($c) => [$c->name => $c->records_count]);

    // Build chart data arrays
    $labels = $allNames->toArray();
    $phoneArray = collect($labels)->map(fn($n) => $phoneCounts[$n] ?? 0)->toArray();
    $whatsappArray = collect($labels)->map(fn($n) => $whatsappCounts[$n] ?? 0)->toArray();
        return response()->json([
            'brands' => $total_brands,
            'team' => $total_team,
            'services' => $total_services,
            'works' => $total_works,
            'chart' => [
                'labels' => $labels,
                'phone' => $phoneArray,
                'whatsapp' => $whatsappArray,
            ],
        ]);
    }
}
