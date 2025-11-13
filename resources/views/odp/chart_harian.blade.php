@extends('layouts.app')
@section('title','Chart Deteksi Per Hari')

@section('content')
<div class="bg-white p-6 rounded-xl shadow-md mb-8">
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-xl font-semibold text-gray-800">📈 Tren Deteksi Per Hari</h2>
        <div class="text-right">
            <span class="text-gray-500 text-sm">Total Deteksi Hari Ini:</span><br>
            <span id="totalDeteksi" class="text-2xl font-bold text-blue-600">
                {{ $totalDeteksi ?? 0 }}
            </span>
        </div>
    </div>
    <canvas id="detectionTrendChart" class="h-80"></canvas>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const ctx = document.getElementById('detectionTrendChart').getContext('2d');
    const detectionChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($chartLabels ?? []) !!},
            datasets: [{
                label: 'Jumlah Deteksi',
                data: {!! json_encode($chartData ?? []) !!},
                borderColor: 'rgb(59,130,246)',
                backgroundColor: 'rgba(59,130,246,0.3)',
                borderWidth: 2,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: { beginAtZero: true }
            },
            plugins: {
                legend: { position: 'bottom' },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return ` ${context.dataset.label}: ${context.parsed.y}`;
                        }
                    }
                }
            }
        }
    });

    async function refreshChart() {
        try {
            const res = await fetch("{{ route('odp.realtime') }}");
            const json = await res.json();

            // Update chart
            detectionChart.data.labels = json.chartLabels;
            detectionChart.data.datasets[0].data = json.chartData;
            detectionChart.update('none');

            // Update total deteksi harian
            if (json.totalDeteksi !== undefined) {
                document.getElementById('totalDeteksi').textContent = json.totalDeteksi;
            }

        } catch (err) {
            console.warn("Gagal update chart:", err);
        }
    }

    setInterval(refreshChart, 5000);
});
</script>
@endsection
