@extends('layouts.app')
@section('title', 'Dashboard ODP')

@section('content')
<!-- STAT CARDS -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
    <x-stat-card color="blue" icon="network-wired" title="Total Deteksi" :count="$detections->count()" />
    <x-stat-card color="green" icon="check-circle" title="Status Normal" :count="$detections->where('status','Normal')->count()" />
    <x-stat-card color="red" icon="exclamation-triangle" title="Status Bermasalah" :count="$detections->where('status','Bermasalah')->count()" />
</div>

<!-- TABLE -->
<div class="bg-white rounded-2xl shadow-lg overflow-hidden">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between p-4 border-b bg-gradient-to-r from-green-50 to-green-100">
        <h2 class="text-lg font-semibold text-gray-700 flex items-center gap-2">
            <i class="fas fa-table text-green-600"></i> Data Hasil Deteksi ODP
        </h2>
        <span id="realtime-clock" class="text-sm font-medium text-gray-600 mt-2 md:mt-0"></span>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm text-left border-collapse">
            <thead class="bg-gray-100 border-b">
                <tr>
                    <th class="px-6 py-3 font-semibold text-gray-700">Foto</th>
                    <th class="px-6 py-3 font-semibold text-gray-700">Hasil Deteksi</th>
                    <th class="px-6 py-3 font-semibold text-gray-700">Klasifikasi Fisik</th>
                    <th class="px-6 py-3 font-semibold text-gray-700">Status</th>
                    <th class="px-6 py-3 font-semibold text-gray-700">Kepercayaan</th>
                    <th class="px-6 py-3 font-semibold text-gray-700">Tanggal Dibuat</th>
                </tr>
            </thead>
            <tbody id="odp-table-body" class="divide-y">
                @foreach($detections as $deteksi)
                <tr class="hover:bg-gray-50 transition-all duration-200 ease-in-out">
                    <td class="px-6 py-4">
                        <img src="{{ $deteksi->photo->file_url ?? asset('img/no-image.png') }}" alt="Foto Deteksi" class="w-16 h-16 object-cover rounded-lg shadow-sm border border-gray-200">
                    </td>
                    <td class="px-6 py-4 font-medium text-gray-800">{{ $deteksi->hasil_deteksi ?? '-' }}</td>
                    <td class="px-6 py-4 text-gray-600">{{ $deteksi->klasifikasi_fisik ?? '-' }}</td>
                    <td class="px-6 py-4">
                        @php
                            $warna = match($deteksi->status) {
                                'Normal' => 'green',
                                'Bermasalah' => 'red',
                                'Pengecekan Ulang' => 'yellow',
                                default => 'gray'
                            };
                        @endphp
                        <span class="px-3 py-1 text-xs font-semibold bg-{{ $warna }}-100 text-{{ $warna }}-700 rounded-full">
                            {{ $deteksi->status ?? 'Tidak Diketahui' }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-gray-600">{{ number_format($deteksi->classifier_conf ?? 0, 2) }}%</td>
                    <td class="px-6 py-4 text-gray-500">{{ $deteksi->created_at->format('d M Y H:i:s') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    function updateLiveClock() {
        const clock = document.getElementById('realtime-clock');
        const now = new Date();
        const hariList = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
        const bulanList = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
        clock.textContent = `${hariList[now.getDay()]}, ${now.getDate()} ${bulanList[now.getMonth()]} ${now.getFullYear()} | ${now.toLocaleTimeString('id-ID')}`;
    }
    setInterval(updateLiveClock, 1000);
    updateLiveClock();

    // Realtime update tabel & statistik
    async function refreshData() {
        try {
            const res = await fetch("{{ route('odp.realtime') }}");
            const json = await res.json();
            document.getElementById('odp-table-body').innerHTML = json.table;
            document.querySelector('[data-count-total]').textContent = json.totalCount;
            document.querySelector('[data-count-normal]').textContent = json.normalCount;
            document.querySelector('[data-count-bad]').textContent = json.bermasalahCount;
        } catch (err) {
            console.warn("Gagal memperbarui data:", err);
        }
    }
    setInterval(refreshData, 5000);
});
</script>
@endsection
