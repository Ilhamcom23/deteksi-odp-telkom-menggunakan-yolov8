@foreach($detections as $deteksi)
<tr class="hover:bg-gray-50 transition">
    <td class="px-6 py-4">
        <img src="{{ $deteksi->photo->file_url ?? asset('img/no-image.png') }}"
             alt="Foto Deteksi" class="w-16 h-16 object-cover rounded-lg shadow-sm">
    </td>
    <td class="px-6 py-4 font-medium text-gray-800">
        {{ $deteksi->hasil_deteksi ?? '-' }}
    </td>
    <td class="px-6 py-4 text-gray-600">
        {{ $deteksi->klasifikasi_fisik ?? '-' }}
    </td>
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
    <td class="px-6 py-4 text-gray-600">
        {{ number_format($deteksi->classifier_conf ?? 0, 2) }}%
    </td>
    <td class="px-6 py-4 text-gray-500">
        {{ $deteksi->created_at->format('d M Y H:i') }}
    </td>
</tr>
@endforeach
