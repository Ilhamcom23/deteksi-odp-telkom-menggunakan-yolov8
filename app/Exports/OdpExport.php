<?php

namespace App\Exports;

use App\Models\OdpDetection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class OdpExport implements FromCollection, WithHeadings
{
    protected $start_date;
    protected $end_date;
    protected $month;
    protected $year;

    public function __construct($start_date = null, $end_date = null, $month = null, $year = null)
    {
        $this->start_date = $start_date;
        $this->end_date   = $end_date;
        $this->month      = $month;
        $this->year       = $year;
    }

    public function collection()
    {
        $query = OdpDetection::with('photo');

        // Filter jika pilih tanggal
        if ($this->start_date && $this->end_date) {
            $query->whereBetween('created_at', [$this->start_date, $this->end_date]);
        }

        // Filter jika pilih bulan & tahun
        elseif ($this->month && $this->year) {
            $query->whereMonth('created_at', $this->month)
                  ->whereYear('created_at', $this->year);
        }

        return $query->get()->map(function ($item) {
            return [
                'ID' => $item->id,
                'Hasil Deteksi' => $item->hasil_deteksi,
                'Status' => $item->status,
                'Tingkat Kepercayaan' => $item->detector_conf,
                'Waktu Deteksi' => $item->created_at->format('Y-m-d H:i:s'),
            ];
        });
    }

    public function headings(): array
    {
        return ['ID', 'Hasil Deteksi', 'Status', 'Tingkat Kepercayaan', 'Waktu Deteksi'];
    }
}
