@props(['title' => null])

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? $appName }}</title>
    <x-favicon />
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="theme-customer font-sans antialiased" x-data="storedAppBrand(@js($appName ?? ''))">
    <div class="guest-shell">
        <div class="guest-blob guest-blob-one"></div>
        <div class="guest-blob guest-blob-two"></div>
        <div class="guest-blob guest-blob-three"></div>
        <div class="guest-card">
            {{ $slot }}
        </div>
    </div>
</body>
</html>
