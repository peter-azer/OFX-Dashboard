<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Laravel') }}</title>
    @vite(['resources/css/app.css'])
</head>

<body>
    <div id="app">
        <v-app>
            <!-- Navigation Bar -->
            <header class="bg-white border-b shadow-sm">
                <div class="max-w-7xl mx-auto px-4">
                    <div class="h-16 flex items-center justify-between">
                        <a href="/" class="text-lg font-semibold tracking-tight text-gray-900 flex items-center">
                            <span class="mr-1">OFX</span>
                            <span class="text-blue-600">Dashboard</span>
                        </a>
                        <nav class="hidden md:flex items-center gap-3">
                            <a href="{{ route('admin.dashboard') }}" class="text-sm font-medium text-gray-600 hover:text-gray-900 px-3 py-2 rounded-md">Dashboard</a>
                            <a href="https://ofxegypt.com" target="_blank" class="text-sm font-medium text-gray-600 hover:text-gray-900 px-3 py-2 rounded-md">Website</a>
                            <a href="/admin/login" id="loginButton" class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">Sign in</a>
                            <a href="{{ route('admin.dashboard') }}" id="dashboardButton" class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700" style="display: none;">Go to Dashboard</a>
                        </nav>
                    </div>
                </div>
            </header>
</body>

</html>