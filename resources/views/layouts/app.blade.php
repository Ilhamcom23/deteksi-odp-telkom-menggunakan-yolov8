<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard ODP')</title>

    <!-- Tailwind CSS & Chart.js & Font Awesome -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Inter', sans-serif; }
        #live-clock { min-width: 250px; text-align: center; }
    </style>
</head>
<body class="bg-gray-100 text-gray-800">

    <!-- NAVBAR -->
    <nav class="bg-white shadow-md p-4 flex justify-between items-center">
        <a href="{{ route('dashboard') }}" class="font-bold text-xl text-gray-800">ODP Dashboard</a>
        <ul class="flex gap-4">
            <li><a href="{{ route('dashboard') }}" class="hover:text-blue-600">Home</a></li>
            <li><a href="{{ route('chart.harian') }}" class="hover:text-blue-600">Chart Harian</a></li>
            <li><a href="{{ route('chart.mingguan') }}" class="hover:text-blue-600">Chart Mingguan</a></li>
            <li><a href="{{ route('odp.create') }}" class="bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700">Tambah Deteksi</a></li>
        </ul>
    </nav>

    <!-- CONTENT -->
    <div class="max-w-7xl mx-auto p-4 sm:p-6 lg:p-8">
        @yield('content')
    </div>

    @yield('scripts')
</body>
</html>
