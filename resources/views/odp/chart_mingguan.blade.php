@extends('layouts.app')
@section('title','Chart Deteksi Per Minggu')

@section('content')
<div class="bg-white p-6 rounded-xl shadow-md mb-8">
    <h2 class="text-xl font-semibold mb-4 text-gray-800">📈 Tren Deteksi Per Minggu</h2>
    <canvas id="detectionTrendWeekChart" class="h-80"></canvas>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const ctxWeek = document.getElementById('detectionTrendWeekChart').getContext('2d');
    const detectionWeekChart = new Chart(ctxWeek, {
        type: 'line',
        data: {
            labels: {!! json_encode($weeklyChartLabels ?? []) !!},
            datasets: [{
                label: 'Jumlah Deteksi per Minggu',
                data: {!! json_encode($weeklyChartData ?? []) !!},
                borderColor: 'rgb(16,185,129)',
                backgroundColor: 'rgba(16,185,129,0.2)',
                fill: true,
                tension: 0.4
            }]
        },
        options: { responsive:true, maintainAspectRatio:false, scales:{y:{beginAtZero:true}}, plugins:{legend:{position:'bottom'}} }
    });

    async function refreshChart() {
        try {
            const res = await fetch("{{ route('odp.realtime') }}");
            const json = await res.json();
            detectionWeekChart.data.labels = json.weeklyChartLabels;
            detectionWeekChart.data.datasets[0].data = json.weeklyChartData;
            detectionWeekChart.update('none');
        } catch(err){ console.warn(err); }
    }
    setInterval(refreshChart,5000);
});
</script>
@endsection
