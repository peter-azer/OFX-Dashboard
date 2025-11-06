<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Models\Visitors;
use Torann\GeoIP\Facades\GeoIP;
use Illuminate\Support\Str;
use App\Jobs\RecordVisitJob; // optional queued job
use Illuminate\Support\Facades\Log;

class TrackVisitor
{
    // Paths to ignore (static assets, health checks, etc.)
    protected $ignorePaths = [
        'favicon.ico', 'robots.txt',
        'sitemap.xml', 'storage/',
        'assets/', 'css/', 'js/', 'images/', 'img/',
        'admin/*',
    ];

    // User agents / simple bot patterns to ignore or mark as bot
    protected $botPatterns = [
        'Googlebot', 'bingbot', 'Baiduspider', 'Yandex', 'DuckDuckBot', 'curl', 'bot', 'spider', 'crawler',
    ];

    public function handle(Request $request, Closure $next)
    {
        // Only track GET requests for pages (skip API endpoints or assets)
        if (!$this->shouldTrack($request)) {
            return $next($request);
        }

        $ip = $request->ip();
        $path = ltrim($request->path(), '/');
        $path = $path ?: '/';
        $method = $request->method();

        // Unique per IP per day (change granularity if you want)
        $key = "visit:{$ip}:" . now()->format('Y-m-d') . ":" . md5($path);

        if (!Cache::has($key)) {
            // Mark cache to avoid duplicates for same IP+path within the day
            Cache::put($key, true, now()->endOfDay());

            // Minimal bot detection
            $ua = $request->userAgent() ?? '';
            $isBot = $this->isBot($ua);

            // GeoIP caching per IP (24 hours)
            $geoKey = "geoip:{$ip}";
            $location = Cache::remember($geoKey, 86400, function () use ($ip) {
                try {
                    return GeoIP::getLocation($ip);
                } catch (\Throwable $e) {
                    return null;
                }
            });

            $visitData = [
                'ip_address' => $ip,
                'url' => $request->fullUrl(),
                'path' => $path,
                'method' => $method,
                'user_agent' => $ua,
                'referrer' => $request->headers->get('referer'),
                'is_bot' => $isBot,
            ];

            if ($location) {
                $visitData = array_merge($visitData, [
                    'country' => $location->country ?: null,
                    'region' => $location->region ?: null,
                    'city' => $location->city ?: null,
                    'latitude' => $location->lat ?: null,
                    'longitude' => $location->lon ?: null,
                ]);
            }

            // Option A: Dispatch to queue (recommended)
            if (class_exists(RecordVisitJob::class)) {
                RecordVisitJob::dispatch($visitData);
            } else {
                // Option B: Synchronous write (fallback)
                Visitors::create($visitData);
            }
        }
        
        Log::info('Tracking visit', ['ip' => $request->ip(), 'url' => $request->fullUrl()]);
        return $next($request);
    }

    protected function shouldTrack(Request $request): bool
    {
        // Only track GET
        if (!$request->isMethod('GET')) {
            return false;
        }

        $path = $request->path();

        // Ignore AJAX/XHR requests (optionally)
        if ($request->ajax() || $request->wantsJson()) {
            return false;
        }

        // Ignore if matches ignorePaths
        foreach ($this->ignorePaths as $ignore) {
            if (Str::startsWith($path, $ignore)) {
                return false;
            }
        }

        return true;
    }

    protected function isBot(string $ua): bool
    {
        $uaLower = strtolower($ua);
        foreach ($this->botPatterns as $pattern) {
            if (stripos($uaLower, strtolower($pattern)) !== false) {
                return true;
            }
        }
        return false;
    }
}
