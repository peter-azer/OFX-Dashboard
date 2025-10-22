<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Admin</title>

        @vite('resources/js/admin.js')
    </head>
    <body>
        <div id="app"></div>
    </body>
</html>
