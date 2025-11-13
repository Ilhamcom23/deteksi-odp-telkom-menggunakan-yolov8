<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Dashboard Deteksi ODP</title>

  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            primary: {
              50: '#f0f9ff',
              100: '#e0f2fe',
              200: '#bae6fd',
              500: '#0ea5e9',
              600: '#0284c7',
              700: '#0369a1',
            },
            valid: {
              500: '#16a34a',
              100: '#dcfce7',
            },
            normal: {
              500: '#f59e0b',
              100: '#fef3c7',
            },
            invalid: {
              500: '#dc2626',
              100: '#fee2e2',
            }
          }
        }
      }
    }
  </script>
</head>

<body class="bg-gray-50 text-gray-800 font-inter">

  <!-- ✅ HEADER -->
  <header class="w-full bg-white shadow-sm py-3 px-4 md:px-6 flex justify-between items-center sticky top-0 z-50">
    <h2 class="text-lg font-bold flex items-center gap-2 text-primary-600">
      <i class="fa-solid fa-gauge text-primary-500"></i> Dashboard Deteksi ODP
    </h2>
    <span id="clockTop" class="text-sm text-gray-600 font-medium">Memuat...</span>
  </header>

  <div class="min-h-screen flex flex-col md:flex-row">

    <!-- ✅ SIDEBAR FIXED -->
    <aside class="w-full md:w-64 bg-gradient-to-b from-primary-600 to-primary-700 text-white p-6 flex flex-col justify-between">
      <div>
        <h1 class="text-xl font-bold mb-6 flex items-center gap-2">
          <i class="fa-solid fa-network-wired"></i> Panel ODP
        </h1>
        <nav class="space-y-2">
          <a href="{{ route('odp.index') }}"
             class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all hover:bg-white/10
             {{ request()->routeIs('odp.index') ? 'bg-white/10' : '' }}">
            <i class="fa-solid fa-chart-line"></i> Dashboard
          </a>
          <a href="{{ route('odp.create') }}"
             class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all hover:bg-white/10
             {{ request()->routeIs('odp.create') ? 'bg-white/10' : '' }}">
            <i class="fa-solid fa-plus"></i> Tambah Deteksi
          </a>
        </nav>
      </div>

      <div class="hidden md:flex items-center gap-2 text-sm text-primary-100 mt-8">
        <i class="fa-solid fa-clock"></i>
        <span id="clockSidebar">Memuat...</span>
      </div>
    </aside>

    <!-- ✅ MAIN CONTENT -->
    <main class="flex-1 p-4 md:p-8 space-y-8">

      <!-- ✅ STAT CARD -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <div class="bg-gradient-to-br from-blue-500 to-blue-600 text-white p-6 rounded-2xl shadow-md flex items-center justify-between">
          <div>
            <p class="text-xs opacity-90">Total Deteksi</p>
            <p class="text-2xl font-bold mt-1">{{ $totalCount }}</p>
          </div>
          <i class="fa-solid fa-diagram-project text-3xl opacity-80"></i>
        </div>

        <div class="bg-gradient-to-br from-emerald-500 to-emerald-600 text-white p-6 rounded-2xl shadow-md flex items-center justify-between">
          <div>
            <p class="text-xs opacity-90">Valid</p>
            <p class="text-2xl font-bold mt-1">{{ $validCount }}</p>
          </div>
          <i class="fa-solid fa-circle-check text-3xl opacity-80"></i>
        </div>

        <div class="bg-gradient-to-br from-amber-500 to-amber-600 text-white p-6 rounded-2xl shadow-md flex items-center justify-between">
          <div>
            <p class="text-xs opacity-90">Normal</p>
            <p class="text-2xl font-bold mt-1">{{ $normalCount }}</p>
          </div>
          <i class="fa-solid fa-clock text-3xl opacity-80"></i>
        </div>

        <div class="bg-gradient-to-br from-rose-500 to-rose-600 text-white p-6 rounded-2xl shadow-md flex items-center justify-between">
          <div>
            <p class="text-xs opacity-90">Tidak Valid</p>
            <p class="text-2xl font-bold mt-1">{{ $invalidCount }}</p>
          </div>
          <i class="fa-solid fa-triangle-exclamation text-3xl opacity-80"></i>
        </div>
      </div>

      <!-- ✅ ADDITIONAL STATS -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
        <div class="bg-white p-6 rounded-2xl shadow-md border border-gray-100 flex items-center justify-between">
          <div>
            <p class="text-sm text-gray-600">Hari Ini</p>
            <p class="text-xl font-bold mt-1">{{ $todayCount ?? 0 }}</p>
          </div>
          <i class="fa-solid fa-calendar-day text-2xl text-blue-500 opacity-80"></i>
        </div>

        <div class="bg-white p-6 rounded-2xl shadow-md border border-gray-100 flex items-center justify-between">
          <div>
            <p class="text-sm text-gray-600">Minggu Ini</p>
            <p class="text-xl font-bold mt-1">{{ $weekCount ?? 0 }}</p>
          </div>
          <i class="fa-solid fa-calendar-week text-2xl text-green-500 opacity-80"></i>
        </div>

        <div class="bg-white p-6 rounded-2xl shadow-md border border-gray-100 flex items-center justify-between">
          <div>
            <p class="text-sm text-gray-600">Rata-rata Confidence</p>
            <p class="text-xl font-bold mt-1">{{ number_format($avgConfidence ?? 0, 1) }}%</p>
          </div>
          <i class="fa-solid fa-chart-line text-2xl text-purple-500 opacity-80"></i>
        </div>
      </div>

      <!-- ✅ HARIAN -->
      <div class="bg-white p-6 rounded-2xl shadow-md border border-gray-100">
        <h3 class="text-lg font-semibold mb-4 flex items-center gap-2 text-primary-600">
          <i class="fa-solid fa-chart-line"></i> Grafik Harian
        </h3>
        <div class="h-72" id="dailyChartWrapper">
          <canvas id="dailyChart"></canvas>
        </div>
      </div>

      <!-- ✅ BULANAN -->
      <div class="bg-white p-6 rounded-2xl shadow-md border border-gray-100">
        <h3 class="text-lg font-semibold mb-4 flex items-center gap-2 text-indigo-600">
          <i class="fa-solid fa-calendar-days"></i> Grafik Per Bulan
        </h3>
        <div class="h-72" id="monthlyChartWrapper">
          <canvas id="monthlyChart"></canvas>
        </div>
      </div>

      <!-- ✅ TABLE -->
      <div class="bg-white p-6 rounded-2xl shadow-lg border border-gray-100">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
            <h3 class="text-lg font-semibold flex items-center gap-2 text-gray-800">
                <i class="fa-solid fa-clock-rotate-left text-indigo-600"></i> Riwayat Deteksi
            </h3>

           <div class="flex flex-wrap items-center gap-3">
    <form id="filterForm" class="flex flex-wrap items-center gap-3" method="GET" action="{{ request()->url() }}">
        <!-- Search Input -->
        <div class="relative">
            <input
                type="text"
                name="search"
                id="searchInput"
                placeholder="Cari ID ODP..."
                value="{{ request('search') }}"
                class="border border-gray-300 rounded-lg px-4 py-2 text-sm focus:ring-2 focus:ring-emerald-400 outline-none w-52"
            >
            <i class="fa-solid fa-search absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
        </div>

        <!-- Filter Bulan -->
        <select
            name="month"
            class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-400 outline-none"
        >
            <option value="">Semua Bulan</option>
            @for ($m = 1; $m <= 12; $m++)
                <option value="{{ $m }}" {{ request('month') == $m ? 'selected' : '' }}>
                    {{ DateTime::createFromFormat('!m', $m)->format('F') }}
                </option>
            @endfor
        </select>

        <!-- Filter Tahun -->
        <select
            name="year"
            class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-400 outline-none"
        >
            <option value="">Semua Tahun</option>
            @for ($y = date('Y'); $y >= 2023; $y--)
                <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>
                    {{ $y }}
                </option>
            @endfor
        </select>

        <!-- Tombol Export -->
        <button
            type="button"
            id="exportBtn"
            class="flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg font-medium shadow-md transition"
        >
            <i class="fa-solid fa-file-excel"></i> Export
        </button>
    </form>
</div>

        </div>

        <div class="border border-gray-200 rounded-xl overflow-hidden">
            <!-- Container untuk data -->
            <div id="dataContainer">
                <div class="max-h-96 overflow-y-auto">
                    <div class="hidden md:block">
                        <table class="min-w-full text-sm">
                            <thead class="bg-gray-50 sticky top-0 z-10">
                                <tr>
                                    <th class="px-5 py-3 text-left font-semibold text-gray-600">Foto</th>
                                    <th class="px-5 py-3 text-left font-semibold text-gray-600">Hasil Deteksi</th>
                                    <th class="px-5 py-3 text-left font-semibold text-gray-600">ID ODP</th>
                                    <th class="px-5 py-3 text-left font-semibold text-gray-600">Confidence</th>
                                    <th class="px-5 py-3 text-left font-semibold text-gray-600">Status</th>
                                    <th class="px-5 py-3 text-left font-semibold text-gray-600">Tanggal</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse($detections as $d)
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-5 py-4">
                                            @if($d->photo && $d->photo->file_url)
                                                <img src="{{ $d->photo->file_url }}" class="w-11 h-11 rounded-lg object-cover border border-gray-200">
                                            @else
                                                <div class="w-11 h-11 rounded-lg bg-gray-100 flex items-center justify-center text-gray-400">
                                                    <i class="fa-solid fa-image text-sm"></i>
                                                </div>
                                            @endif
                                        </td>
                                        <td class="px-5 py-4 font-medium">{{ $d->hasil_deteksi ?? '-' }}</td>
                                        <td class="px-5 py-4 text-sm text-gray-600 max-w-xs truncate" title="{{ $d->ocr_text ?? '' }}">
                                            {{ $d->ocr_text ? (strlen($d->ocr_text) > 30 ? substr($d->ocr_text, 0, 30) . '...' : $d->ocr_text) : '-' }}
                                        </td>
                                        <td class="px-5 py-4 font-semibold
                                            @if($d->detector_conf >= 85) text-emerald-600
                                            @elseif($d->detector_conf >= 70) text-amber-600
                                            @else text-rose-600 @endif">
                                            {{ $d->detector_conf ? number_format($d->detector_conf, 1) . '%' : '-' }}
                                        </td>
                                        <td class="px-5 py-4">
                                            <span class="px-2.5 py-1 rounded-full text-xs font-medium
                                                @if($d->status=='Valid') bg-valid-100 text-valid-600
                                                @elseif($d->status=='Normal') bg-normal-100 text-normal-600
                                                @else bg-invalid-100 text-invalid-600 @endif">
                                                {{ $d->status }}
                                            </span>
                                        </td>
                                        <td class="px-5 py-4 text-gray-500 text-sm">{{ $d->created_at?->format('d M Y H:i') ?? '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-10 text-gray-500">
                                            <i class="fas fa-inbox text-2xl mb-2"></i><br>
                                            Tidak ada data deteksi
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Tampilan Mobile -->
                    <div class="md:hidden p-4">
                        @forelse($detections as $d)
                            <div class="mobile-card bg-white border border-gray-200 rounded-lg p-4 mb-3 shadow-sm">
                                <div class="flex justify-between items-center mb-2">
                                    <div class="flex items-center gap-3">
                                        @if($d->photo && $d->photo->file_url)
                                            <img src="{{ $d->photo->file_url }}" class="w-10 h-10 rounded-lg object-cover border border-gray-200">
                                        @else
                                            <div class="w-10 h-10 rounded-lg bg-gray-100 flex items-center justify-center text-gray-400">
                                                <i class="fa-solid fa-image text-sm"></i>
                                            </div>
                                        @endif
                                        <div>
                                            <div class="font-medium text-gray-900">{{ $d->hasil_deteksi ?? '-' }}</div>
                                            <div class="text-xs text-gray-500">{{ $d->created_at?->format('d M Y H:i') ?? '-' }}</div>
                                        </div>
                                    </div>
                                    <span class="px-2.5 py-1 rounded-full text-xs font-medium
                                        @if($d->status=='Valid') bg-valid-100 text-valid-600
                                        @elseif($d->status=='Normal') bg-normal-100 text-normal-600
                                        @else bg-invalid-100 text-invalid-600 @endif">
                                        {{ $d->status }}
                                    </span>
                                </div>
                                <div class="grid grid-cols-2 gap-2 text-sm">
                                    <div>
                                        <div class="text-gray-500">ID ODP</div>
                                        <div class="truncate" title="{{ $d->ocr_text ?? '' }}">
                                            {{ $d->ocr_text ? (strlen($d->ocr_text) > 20 ? substr($d->ocr_text, 0, 20) . '...' : $d->ocr_text) : '-' }}
                                        </div>
                                    </div>
                                    <div>
                                        <div class="text-gray-500">Confidence</div>
                                        <div class="font-semibold
                                            @if($d->detector_conf >= 85) text-emerald-600
                                            @elseif($d->detector_conf >= 70) text-amber-600
                                            @else text-rose-600 @endif">
                                            {{ $d->detector_conf ? number_format($d->detector_conf, 1) . '%' : '-' }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-10 text-gray-500">
                                <i class="fas fa-inbox text-2xl mb-2"></i><br>
                                Tidak ada data deteksi
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Pagination dengan AJAX -->
            @if($detections->hasPages())
                <div class="bg-gray-50 px-5 py-3 border-t border-gray-200">
                    <div id="paginationContainer">
                        {{ $detections->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>

    </main>
  </div>

  <!-- ✅ FOOTER -->
  <footer class="bg-gray-100 py-6">
    <div class="max-w-6xl mx-auto px-4 flex flex-col md:flex-row items-center justify-between space-y-3 md:space-y-0">
      <div class="flex items-center space-x-2">
        <i class="fa-solid fa-network-wired text-blue-500"></i>
        <span class="text-sm font-medium">© <span id="year"></span> ODP Detection</span>
      </div>
      <div class="flex space-x-4 text-sm">
        <a href="#" class="hover:text-blue-500 transition">Kebijakan Privasi</a>
        <a href="#" class="hover:text-blue-500 transition">Syarat & Ketentuan</a>
        <a href="#" class="hover:text-blue-500 transition">Kontak</a>
      </div>
    </div>
  </footer>

 <script>
  // Tahun realtime otomatis
  const yearEl = document.getElementById("year");
  if (yearEl) yearEl.textContent = new Date().getFullYear();

  // ✅ REALTIME CLOCK
  function updateClock() {
    const now = new Date();
    const days = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
    const months = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
    const timeStr = `${days[now.getDay()]}, ${now.getDate()} ${months[now.getMonth()]} ${now.getFullYear()} • ${now.toLocaleTimeString('id-ID')}`;
    const top = document.getElementById('clockTop');
    const side = document.getElementById('clockSidebar');
    if (top) top.textContent = timeStr;
    if (side) side.textContent = timeStr;
  }
  updateClock();
  setInterval(updateClock, 1000);

  // ✅ CHARTS (dengan safety checks)
  const chartInstances = {};
  function safeGetCanvasContext(id) {
    const canvas = document.getElementById(id);
    if (!canvas) return null;
    return canvas.getContext('2d');
  }

  function renderChart(id, labels, valid, normal, invalid, type) {
    try {
      const ctx = safeGetCanvasContext(id);
      if (!ctx) return;

      const wrapper = document.getElementById(id + 'Wrapper') || ctx.canvas.closest('.h-72') || ctx.canvas.parentElement;
      if (!labels || labels.length === 0) {
        if (wrapper) {
          wrapper.innerHTML = `
            <div class="flex flex-col items-center justify-center h-full text-gray-500">
              <i class="fas fa-chart-line text-3xl mb-2"></i>
              <p>Tidak ada data</p>
            </div>`;
        }
        if (chartInstances[id]) {
          try { chartInstances[id].destroy(); } catch(e) {}
          delete chartInstances[id];
        }
        return;
      }

      if (wrapper && !wrapper.querySelector(`#${id}`)) {
        wrapper.innerHTML = `<canvas id="${id}"></canvas>`;
      }

      const ctx2 = safeGetCanvasContext(id);
      if (!ctx2) return;

      if (chartInstances[id]) {
        try { chartInstances[id].destroy(); } catch(e) {}
      }

      chartInstances[id] = new Chart(ctx2, {
        type: type,
        data: {
          labels: labels,
          datasets: [
            {
              label: 'Valid',
              data: valid,
              borderColor: '#16a34a',
              backgroundColor: type === 'line' ? 'rgba(22, 163, 74, 0.1)' : 'rgba(22, 163, 74, 0.8)',
              borderWidth: 2,
              tension: type === 'line' ? 0.3 : 0,
              fill: type === 'line'
            },
            {
              label: 'Normal',
              data: normal,
              borderColor: '#f59e0b',
              backgroundColor: type === 'line' ? 'rgba(245, 158, 11, 0.1)' : 'rgba(245, 158, 11, 0.8)',
              borderWidth: 2,
              tension: type === 'line' ? 0.3 : 0,
              fill: type === 'line'
            },
            {
              label: 'Tidak Valid',
              data: invalid,
              borderColor: '#dc2626',
              backgroundColor: type === 'line' ? 'rgba(220, 38, 38, 0.1)' : 'rgba(220, 38, 38, 0.8)',
              borderWidth: 2,
              tension: type === 'line' ? 0.3 : 0,
              fill: type === 'line'
            }
          ]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              position: 'top',
              labels: {
                padding: 15,
                usePointStyle: true
              }
            }
          },
          scales: {
            y: {
              beginAtZero: true,
              ticks: { precision: 0 },
              grid: { color: 'rgba(0,0,0,0.03)' }
            },
            x: {
              grid: { display: false }
            }
          },
          interaction: {
            intersect: false,
            mode: 'index'
          }
        }
      });
    } catch (err) {
      console.error('Chart render error for', id, err);
    }
  }

  // Ambil data dari server-side (Blade -> JSON)
  const dailyLabels   = {!! json_encode($dailyLabels ?? []) !!};
  const dailyValid    = {!! json_encode($dailyValid ?? []) !!};
  const dailyNormal   = {!! json_encode($dailyNormal ?? []) !!};
  const dailyInvalid  = {!! json_encode($dailyInvalid ?? []) !!};

  const monthlyLabels = {!! json_encode($monthlyLabels ?? []) !!};
  const monthlyValid  = {!! json_encode($monthlyValid ?? []) !!};
  const monthlyNormal = {!! json_encode($monthlyNormal ?? []) !!};
  const monthlyInvalid= {!! json_encode($monthlyInvalid ?? []) !!};

  renderChart('dailyChart', dailyLabels, dailyValid, dailyNormal, dailyInvalid, 'line');
  renderChart('monthlyChart', monthlyLabels, monthlyValid, monthlyNormal, monthlyInvalid, 'bar');

  // ✅ TABEL RESPONSIF DENGAN PAGINATION AJAX
  const filterForm = document.getElementById('filterForm');
  const searchInput = document.getElementById('searchInput');
  const exportBtn = document.getElementById('exportBtn');
  const dataContainer = document.getElementById('dataContainer');
  const paginationContainer = document.getElementById('paginationContainer');

  function showLoadingSkeleton() {
    if (!dataContainer) return;
    dataContainer.innerHTML = `
      <div class="p-4">
        <div class="animate-pulse space-y-4">
          <div class="h-6 bg-gray-200 rounded w-1/3"></div>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="h-24 bg-gray-200 rounded"></div>
            <div class="h-24 bg-gray-200 rounded"></div>
            <div class="h-24 bg-gray-200 rounded"></div>
          </div>
        </div>
      </div>
    `;
  }

  if (filterForm && dataContainer) {
    // Event untuk filter
    filterForm.addEventListener('change', function() {
      loadDataWithFilters();
    });

    // ✅ Search langsung di tabel (tanpa controller)
    if (searchInput) {
      searchInput.addEventListener('input', function() {
        const keyword = this.value.toLowerCase().trim();
        const rows = document.querySelectorAll('#dataContainer tbody tr');

        rows.forEach(row => {
          const idOdpCell = row.querySelector('td:nth-child(3)'); // kolom ke-3 = ID ODP
          const idOdpText = idOdpCell ? idOdpCell.textContent.toLowerCase() : '';
          row.style.display = idOdpText.includes(keyword) ? '' : 'none';
        });
      });
    }

    // ✅ Event untuk export (revisi)
    if (exportBtn && filterForm) {
      exportBtn.addEventListener('click', function() {
        const formData = new FormData(filterForm);
        const params = new URLSearchParams();

        for (const [key, value] of formData.entries()) {
          if (value.trim() !== '') params.append(key, value);
        }

        const exportUrlBase = '{{ route("odp.export") }}';
        const query = params.toString();
        window.location.href = exportUrlBase + (query ? `?${query}` : '');
      });
    }

    // Event delegation untuk pagination
    document.addEventListener('click', function(e) {
      const a = e.target.closest && e.target.closest('a');
      if (!a) return;
      if (paginationContainer && paginationContainer.contains(a)) {
        e.preventDefault();
        const url = a.href;
        if (url) loadPage(url);
      }
    });

    function loadDataWithFilters() {
      const formData = new FormData(filterForm);
      const params = new URLSearchParams();
      for (const pair of formData.entries()) {
        if (pair[1] !== '') params.append(pair[0], pair[1]);
      }

      showLoadingSkeleton();

      const fetchUrl = '{{ request()->url() }}' + (params.toString() ? ('?' + params.toString()) : '');
      fetch(fetchUrl, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      })
      .then(response => response.text())
      .then(html => {
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');

        const newDataContainer = doc.getElementById('dataContainer');
        if (newDataContainer) {
          dataContainer.innerHTML = newDataContainer.innerHTML;
        }

        if (paginationContainer) {
          const newPagination = doc.getElementById('paginationContainer');
          paginationContainer.innerHTML = newPagination ? newPagination.innerHTML : '';
        }
      })
      .catch(error => {
        console.error('Error loadDataWithFilters:', error);
        dataContainer.innerHTML = `<div class="p-6 text-center text-red-500">Gagal memuat data. Coba lagi.</div>`;
      });
    }

    function loadPage(url) {
      showLoadingSkeleton();

      fetch(url, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      })
      .then(response => response.text())
      .then(html => {
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');

        const newDataContainer = doc.getElementById('dataContainer');
        if (newDataContainer) {
          dataContainer.innerHTML = newDataContainer.innerHTML;
        }

        if (paginationContainer) {
          const newPagination = doc.getElementById('paginationContainer');
          paginationContainer.innerHTML = newPagination ? newPagination.innerHTML : '';
        }

        try { window.history.pushState({}, '', url); } catch(e) {}
      })
      .catch(error => {
        console.error('Error loadPage:', error);
        dataContainer.innerHTML = `<div class="p-6 text-center text-red-500">Gagal memuat halaman. Coba lagi.</div>`;
      });
    }

    // Handle browser back/forward buttons
    window.addEventListener('popstate', function() {
      loadPage(window.location.href);
    });
  }
</script>




  <style>
    .bg-valid-100 { background-color: #d1fae5; }
    .text-valid-600 { color: #059669; }
    .bg-normal-100 { background-color: #fef3c7; }
    .text-normal-600 { color: #d97706; }
    .bg-invalid-100 { background-color: #fee2e2; }
    .text-invalid-600 { color: #dc2626; }

    .loading {
        display: inline-block;
        width: 20px;
        height: 20px;
        border: 3px solid rgba(59, 130, 246, 0.3);
        border-radius: 50%;
        border-top-color: #3b82f6;
        animation: spin 1s ease-in-out infinite;
    }

    @keyframes spin {
        to { transform: rotate(360deg); }
    }
  </style>

</body>
</html>
