<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Models\Visitors;
use Torann\GeoIP\Facades\GeoIP;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class TrackVisitor
{
    // Paths to ignore (static assets, health checks, etc.)
    protected $ignorePaths = [
        'favicon.ico', 'robots.txt',
        'sitemap.xml', 'storage/',
        'assets/', 'css/', 'js/', 'images/', 'img/',
        'admin/', 'sanctum/', 'broadcasting/', 'horizon/'
    ];
    
    // API routes that should be tracked (public routes from api.php)
    protected $trackedApiRoutes = [
        'api/home',
        'api/blogs',
        'api/next-contact',
        'api/phone/',
        'api/submit-form',
        'api/next-whatsapp-contact',
        'api/whatsapp/',
        'api/service/',
        'api/work/'
    ];
    
    // API route patterns that should be tracked (for dynamic routes like /blogs/{post})
    protected $trackedApiPatterns = [
        'api/blogs/*',
        'api/phone/*/record',
        'api/whatsapp/*/record',
        'api/service/*',
        'api/work/*'
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

            // Initialize location data with null values
            $locationData = [
                'country' => null,
                'region' => null,
                'city' => null,
                'latitude' => null,
                'longitude' => null,
            ];

            // Skip local/private IPs
            if (!in_array($ip, ['127.0.0.1', '::1']) && !filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                $geoKey = "geoip:{$ip}";
                
                try {
                    // Get location with error handling and validation
                    $location = Cache::remember($geoKey, 86400, function () use ($ip) {
                        try {
                            $location = GeoIP::getLocation($ip);
                            
                            // Validate location data
                            if ($location && $location->default === false) {
                                return $location;
                            }
                            return null;
                        } catch (\Throwable $e) {
                            Log::warning('GeoIP lookup failed', [
                                'ip' => $ip,
                                'error' => $e->getMessage()
                            ]);
                            return null;
                        }
                    });

                    if ($location) {
                        $locationData = [
                            'country' => !empty($location->country) ? $location->country : null,
                            'region' => !empty($location->region) ? $location->region : null,
                            'city' => !empty($location->city) ? $location->city : null,
                            'latitude' => !empty($location->lat) ? $location->lat : null,
                            'longitude' => !empty($location->lon) ? $location->lon : null,
                        ];
                    }
                } catch (\Throwable $e) {
                    Log::error('GeoIP processing error', [
                        'ip' => $ip,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            $visitData = [
                'ip_address' => $ip,
                'url' => $request->fullUrl(),
                'path' => $path,
                'method' => $method,
                'user_agent' => $ua,
                'referrer' => $request->headers->get('referer'),
                'is_bot' => $isBot,
            ];

            // Merge location data
            $visitData = array_merge($visitData, $locationData);

            // Direct database insertion
            Visitors::create($visitData);
        }
        
        Log::info('Tracking visit', ['ip' => $request->ip(), 'url' => $request->fullUrl()]);
        return $next($request);
    }

    protected function shouldTrack(Request $request): bool
    {
        // Track both GET and POST requests
        if (!in_array($request->method(), ['GET', 'POST'])) {
            return false;
        }

        $path = $request->path();

        // Don't track API routes that are not part of your main application
        if (str_starts_with($path, 'api/') && !$this->shouldTrackApiRoute($path)) {
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

    protected function shouldTrackApiRoute(string $path): bool
    {
        // Check exact matches or route prefixes
        foreach ($this->trackedApiRoutes as $route) {
            if ($path === $route || str_starts_with($path, $route)) {
                return true;
            }
        }
        
        // Check route patterns (for dynamic routes)
        foreach ($this->trackedApiPatterns as $pattern) {
            if (fnmatch($pattern, $path)) {
                return true;
            }
        }
        
        return false;
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
