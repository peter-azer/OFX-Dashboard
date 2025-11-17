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
use App\Models\FormSubmition;
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
        $phoneQuery = PhoneContacts::withCount(['records' => function ($q) use ($dateFilter, $name) {
            $q->when($dateFilter, fn($q) => $q->where('created_at', '>=', $dateFilter));
            if ($name) {
                $q->where('name', 'like', "%{$name}%");
            }
        }]);

        $whatsappQuery = WhatsAppContacts::withCount(['records' => function ($q) use ($dateFilter, $name) {
            $q->when($dateFilter, fn($q) => $q->where('created_at', '>=', $dateFilter));
            if ($name) {
                $q->where('name', 'like', "%{$name}%");
            }
        }]);

        $phoneContactData = $phoneQuery->get(['name']);
        $whatsappContactData = $whatsappQuery->get(['name']);

        // Get form submissions count with filters
        $formSubmitionQuery = FormSubmition::selectRaw('full_name as name, COUNT(*) as records_count')
            ->when($dateFilter, fn($q) => $q->where('created_at', '>=', $dateFilter))
            ->when($name, fn($q) => $q->where('full_name', 'like', "%{$name}%"))
            ->groupBy('full_name');

        $formSubmitionData = $formSubmitionQuery->get();

        // Period-based data
        $periodData = $this->getPeriodData($dateFilter, $period, $name);

        // Merge all unique contact names
        $allNames = $phoneContactData->pluck('name')
            ->merge($whatsappContactData->pluck('name'))
            ->merge($formSubmitionData->pluck('name'))
            ->unique()
            ->values();

        // Map counts
        $phoneCounts = $phoneContactData->mapWithKeys(fn($c) => [$c->name => $c->records_count]);
        $whatsappCounts = $whatsappContactData->mapWithKeys(fn($c) => [$c->name => $c->records_count]);
        $formSubmitionCounts = $formSubmitionData->mapWithKeys(fn($c) => [$c->name => $c->records_count]);

        return response()->json([
            'brands' => $total_brands,
            'team' => $total_team,
            'services' => $total_services,
            'works' => $total_works,
            'chart' => [
                'labels' => $allNames,
                'phone' => $allNames->map(fn($n) => $phoneCounts[$n] ?? 0),
                'whatsapp' => $allNames->map(fn($n) => $whatsappCounts[$n] ?? 0),
                'form_submission' => $allNames->map(fn($n) => $formSubmitionCounts[$n] ?? 0),
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
        // Use PHP formats that match the SQL DATE_FORMAT outputs
        $periodFormat = match ($period) {
            'week' => 'o-W', // ISO week-numbering year + week number (matches %Y-%u)
            'month' => 'Y-m',
            default => 'Y-m-d',
        };

        $data = [
            'labels' => [],
            'phone' => [],
            'whatsapp' => [],
            'form_submission' => []
        ];

        // Determine start and end using provided $dateFilter (start) and now() (end)
        $start = $dateFilter instanceof Carbon ? $dateFilter->copy()->startOfDay() : now()->subDays(30)->startOfDay();
        $end = now()->startOfDay();

        // Build period keys in chronological order (oldest -> newest)
        $periodKeys = [];
        $current = $start->copy();
        while ($current <= $end) {
            $key = $current->format($periodFormat);
            $label = $current->format($period === 'week' ? 'M d' : ($period === 'month' ? 'M Y' : 'M d'));
            $data['labels'][] = $label;
            $data['phone'][$key] = 0;
            $data['whatsapp'][$key] = 0;
            $periodKeys[] = $key;

            // move forward one unit depending on period
            if ($period === 'week') {
                $current->addWeek();
            } elseif ($period === 'month') {
                $current->addMonth();
            } else {
                $current->addDay();
            }
        }

        // Get period-based counts from DB (keys will match $periodKeys)
        // Use ISO week format in SQL to match PHP 'o-W' (ISO year + ISO week)
        $sqlFormat = $period === 'week' ? '%x-%v' : ($period === 'month' ? '%Y-%m' : '%Y-%m-%d');

        $phonePeriodData = PhoneContacts::selectRaw(
            "DATE_FORMAT(created_at, ?) as period, COUNT(*) as count",
            [$sqlFormat]
        )
            ->when($dateFilter, fn($q) => $q->where('created_at', '>=', $dateFilter))
            ->when($name, fn($q) => $q->where('name', 'like', "%{$name}%"))
            ->groupBy('period')
            ->pluck('count', 'period')
            ->toArray();

        $whatsappPeriodData = WhatsAppContacts::selectRaw(
            "DATE_FORMAT(created_at, ?) as period, COUNT(*) as count",
            [$sqlFormat]
        )
            ->when($dateFilter, fn($q) => $q->where('created_at', '>=', $dateFilter))
            ->when($name, fn($q) => $q->where('name', 'like', "%{$name}%"))
            ->groupBy('period')
            ->pluck('count', 'period')
            ->toArray();

        // Get form submissions grouped by services and period
        $formSubmitionByService = FormSubmition::selectRaw(
            "DATE_FORMAT(form_submitions.created_at, ?) as period, services.service_name as service_name, COUNT(*) as count",
            [$sqlFormat]
        )
            ->join('form_service', 'form_submitions.id', '=', 'form_service.form_id')
            ->join('services', 'form_service.service_id', '=', 'services.id')
            ->when($dateFilter, fn($q) => $q->where('form_submitions.created_at', '>=', $dateFilter))
            ->when($name, fn($q) => $q->where('form_submitions.full_name', 'like', "%{$name}%"))
            ->groupBy('period', 'service_name')
            ->get();

        // Transform form submission data by service and period
        $formSubmitionPeriodByService = [];
        foreach ($formSubmitionByService as $record) {
            $serviceName = $record->service_name;
            if (!isset($formSubmitionPeriodByService[$serviceName])) {
                $formSubmitionPeriodByService[$serviceName] = [];
            }
            $formSubmitionPeriodByService[$serviceName][$record->period] = $record->count;
        }

        // Fill period data for phone and whatsapp in chronological order
        foreach ($periodKeys as $periodKey) {
            $data['phone'][$periodKey] = $phonePeriodData[$periodKey] ?? 0;
            $data['whatsapp'][$periodKey] = $whatsappPeriodData[$periodKey] ?? 0;
        }

        // Build form_submission arrays per service using the same chronological periodKeys
        $data['form_submission'] = [];
        foreach ($formSubmitionPeriodByService as $serviceName => $periodCounts) {
            $arr = [];
            foreach ($periodKeys as $periodKey) {
                $arr[] = $periodCounts[$periodKey] ?? 0;
            }
            $data['form_submission'][$serviceName] = $arr;
        }

        // Convert phone and whatsapp to simple indexed arrays (chronological)
        $data['phone'] = array_values($data['phone']);
        $data['whatsapp'] = array_values($data['whatsapp']);

        return $data;
    }
}
