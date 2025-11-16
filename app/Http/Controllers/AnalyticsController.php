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
use Illuminate\Routing\Controller as BaseController;

class AnalyticsController extends BaseController
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('permission:view analytics');
    }
    public function overview(Request $request)
    {
        // Get filter parameters with defaults
        $days = $request->input('days', 30);
        $name = $request->input('name');
        $period = $request->input('period', 'day');

        $dateFilter = now()->subDays($days);
        
        // Get counts with filters
        $total_brands = Brand::when($dateFilter, fn($q) => $q->where('created_at', '>=', $dateFilter))->count();
        $total_team = Team::when($dateFilter, fn($q) => $q->where('created_at', '>=', $dateFilter))->count();
        $total_services = Service::when($dateFilter, fn($q) => $q->where('created_at', '>=', $dateFilter))->count();
        $total_works = Work::when($dateFilter, fn($q) => $q->where('created_at', '>=', $dateFilter))->count();

        // Get contacts and record counts with filters
        $phoneQuery = PhoneContacts::withCount(['records' => function($q) use ($dateFilter, $name) {
            $q->when($dateFilter, fn($q) => $q->where('created_at', '>=', $dateFilter));
            if ($name) {
                $q->where('name', 'like', "%{$name}%");
            }
        }]);

        $whatsappQuery = WhatsAppContacts::withCount(['records' => function($q) use ($dateFilter, $name) {
            $q->when($dateFilter, fn($q) => $q->where('created_at', '>=', $dateFilter));
            if ($name) {
                $q->where('name', 'like', "%{$name}%");
            }
        }]);

        $phoneContactData = $phoneQuery->get(['name']);
        $whatsappContactData = $whatsappQuery->get(['name']);

        // Period-based data
        $periodData = $this->getPeriodData($dateFilter, $period, $name);

        // Merge all unique contact names
        $allNames = $phoneContactData->pluck('name')
            ->merge($whatsappContactData->pluck('name'))
            ->unique()
            ->values();

        // Map counts
        $phoneCounts = $phoneContactData->mapWithKeys(fn($c) => [$c->name => $c->records_count]);
        $whatsappCounts = $whatsappContactData->mapWithKeys(fn($c) => [$c->name => $c->records_count]);

        return response()->json([
            'brands' => $total_brands,
            'team' => $total_team,
            'services' => $total_services,
            'works' => $total_works,
            'chart' => [
                'labels' => $allNames,
                'phone' => $allNames->map(fn($n) => $phoneCounts[$n] ?? 0),
                'whatsapp' => $allNames->map(fn($n) => $whatsappCounts[$n] ?? 0),
            ],
            'periodData' => $periodData,
            'filters' => [
                'days' => $days,
                'name' => $name,
                'period' => $period
            ]
        ]);
    }

    protected function getPeriodData($dateFilter, $period, $name)
    {
        $periodFormat = match($period) {
            'week' => 'Y-W',
            'month' => 'Y-m',
            default => 'Y-m-d',
        };

        $data = [
            'labels' => [],
            'phone' => [],
            'whatsapp' => []
        ];

        // Generate period labels
        $current = now()->startOfDay();
        $endDate = now()->subDays(30)->startOfDay();
        
        while ($current >= $endDate) {
            $data['labels'][] = $current->format($period === 'week' ? 'M d' : ($period === 'month' ? 'M Y' : 'M d'));
            $data['phone'][$current->format($periodFormat)] = 0;
            $data['whatsapp'][$current->format($periodFormat)] = 0;
            $current->sub(1, $period . 's');
        }

        $data['labels'] = array_reverse($data['labels']);

        // Get period-based counts
        $phonePeriodData = PhoneContacts::selectRaw(
                "DATE_FORMAT(created_at, ?) as period, COUNT(*) as count",
                [$period === 'week' ? '%Y-%u' : ($period === 'month' ? '%Y-%m' : '%Y-%m-%d')]
            )
            ->when($dateFilter, fn($q) => $q->where('created_at', '>=', $dateFilter))
            ->when($name, fn($q) => $q->where('name', 'like', "%{$name}%"))
            ->groupBy('period')
            ->pluck('count', 'period');

        $whatsappPeriodData = WhatsAppContacts::selectRaw(
                "DATE_FORMAT(created_at, ?) as period, COUNT(*) as count",
                [$period === 'week' ? '%Y-%u' : ($period === 'month' ? '%Y-%m' : '%Y-%m-%d')]
            )
            ->when($dateFilter, fn($q) => $q->where('created_at', '>=', $dateFilter))
            ->when($name, fn($q) => $q->where('name', 'like', "%{$name}%"))
            ->groupBy('period')
            ->pluck('count', 'period');

        // Fill period data
        foreach ($data['phone'] as $periodKey => $count) {
            $data['phone'][$periodKey] = $phonePeriodData[$periodKey] ?? 0;
            $data['whatsapp'][$periodKey] = $whatsappPeriodData[$periodKey] ?? 0;
        }

        $data['phone'] = array_values($data['phone']);
        $data['whatsapp'] = array_values($data['whatsapp']);

        return $data;
    }
}
