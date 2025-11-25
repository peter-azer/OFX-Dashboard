<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Admin</title>

    @vite('resources/js/admin.js')
    <script src="https://cdn.tiny.cloud/1/k5ge8vafjefff3o9mcmxqqiu1fgvq55avp4x79w29xpwgmt7/tinymce/7/tinymce.min.js" referrerpolicy="origin"></script>
</head>

<body>
    <div id="app"></div>
</body>

</html>