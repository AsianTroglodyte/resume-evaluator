<!DOCTYPE html>
<html lang="en" data-theme="lofi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <title>{{$title}}</title>
</head>
<body class="min-h-screen min-w-screen bg-base-300">
    {{ $slot }}
</body>
</html>