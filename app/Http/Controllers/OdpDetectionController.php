<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use GuzzleHttp\Client;
use App\Models\Photo;
use App\Models\OdpDetection;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\OdpExport;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class OdpDetectionController extends Controller
{
    public function index()
    {
        $detections = OdpDetection::with('photo')->latest()->paginate(10);

        $totalCount   = OdpDetection::count();
        $validCount   = OdpDetection::where('status', 'Valid')->count();
        $normalCount  = OdpDetection::where('status', 'Normal')->count();
        $invalidCount = OdpDetection::where('status', 'Tidak Valid')->count();

        // === Grafik Harian ===
        $daily = OdpDetection::selectRaw("
            DATE(created_at) as tanggal,
            COUNT(*) as total,
            SUM(CASE WHEN status = 'Valid' THEN 1 ELSE 0 END) as valid,
            SUM(CASE WHEN status = 'Normal' THEN 1 ELSE 0 END) as normal,
            SUM(CASE WHEN status = 'Tidak Valid' THEN 1 ELSE 0 END) as invalid
        ")
        ->groupBy('tanggal')
        ->orderBy('tanggal', 'desc')
        ->limit(30) // 30 hari terakhir
        ->get();

        $dailyLabels  = $daily->pluck('tanggal')->map(fn($d) => date('d M Y', strtotime($d)))->toArray();
        $dailyValid   = $daily->pluck('valid')->toArray();
        $dailyNormal  = $daily->pluck('normal')->toArray();
        $dailyInvalid = $daily->pluck('invalid')->toArray();
        $dailyTotal   = $daily->pluck('total')->toArray();

        // === Grafik Bulanan ===
        $monthly = OdpDetection::selectRaw("
            DATE_FORMAT(created_at, '%Y-%m') as bulan,
            COUNT(*) as total,
            SUM(CASE WHEN status = 'Valid' THEN 1 ELSE 0 END) as valid,
            SUM(CASE WHEN status = 'Normal' THEN 1 ELSE 0 END) as normal,
            SUM(CASE WHEN status = 'Tidak Valid' THEN 1 ELSE 0 END) as invalid
        ")
        ->groupBy('bulan')
        ->orderBy('bulan', 'desc')
        ->limit(12) // 12 bulan terakhir
        ->get();

        $monthlyLabels  = $monthly->pluck('bulan')->map(fn($m) => date('M Y', strtotime($m.'-01')))->toArray();
        $monthlyValid   = $monthly->pluck('valid')->toArray();
        $monthlyNormal  = $monthly->pluck('normal')->toArray();
        $monthlyInvalid = $monthly->pluck('invalid')->toArray();
        $monthlyTotal   = $monthly->pluck('total')->toArray();

        // === Statistik Tambahan ===
        $todayCount = OdpDetection::whereDate('created_at', today())->count();
        $weekCount = OdpDetection::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count();
        $monthCount = OdpDetection::whereMonth('created_at', now()->month)->count();

        // === Confidence Statistics ===
        $avgConfidence = OdpDetection::where('status', '!=', 'Tidak Valid')->avg('detector_conf');
        $highConfidenceCount = OdpDetection::where('detector_conf', '>=', 85)->count();

        return view('odp.index', compact(
            'detections',
            'totalCount',
            'validCount',
            'normalCount',
            'invalidCount',
            'todayCount',
            'weekCount',
            'monthCount',
            'avgConfidence',
            'highConfidenceCount',
            'dailyLabels',
            'dailyValid',
            'dailyNormal',
            'dailyInvalid',
            'dailyTotal',
            'monthlyLabels',
            'monthlyValid',
            'monthlyNormal',
            'monthlyInvalid',
            'monthlyTotal'
        ));
    }

    public function create()
    {
        return view('odp.create');
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'image' => 'required|image|mimes:jpg,jpeg,png|max:5120',
            ]);

            // === Simpan foto ke storage ===
            $image = $request->file('image');
            $fileName = time() . '_' . Str::random(10) . '_' . $image->getClientOriginalName();
            $path = $image->storeAs('odp_image', $fileName, 'public');
            $fileUrl = asset('storage/' . $path);

            // Sesuaikan dengan struktur tabel photos yang ada
            $photo = Photo::create([
                'photo_id'    => Str::uuid(),
                'file_url'    => $fileUrl,
                'file_name'   => $fileName,
                'file_size'   => $image->getSize() / 1024, // Convert ke KB
                'mime_type'   => $image->getMimeType(),
                'uploaded_at' => now(),
            ]);

            // === Kirim ke Flask API ===
            $client = new Client([
                'timeout' => 60,
                'connect_timeout' => 30,
                'headers' => [
                    'Accept' => 'application/json',
                ]
            ]);

            $data = null;
            $flaskApiUrl = env('FLASK_API_URL', 'http://127.0.0.1:5000');

            try {
                $response = $client->post($flaskApiUrl . '/predict', [
                    'multipart' => [[
                        'name' => 'image',
                        'contents' => fopen(storage_path('app/public/' . $path), 'r'),
                        'filename' => $fileName,
                    ]],
                ]);

                $statusCode = $response->getStatusCode();
                $data = json_decode($response->getBody(), true);

                if ($statusCode !== 200) {
                    throw new \Exception("Flask API returned status: " . $statusCode);
                }

            } catch (\GuzzleHttp\Exception\ConnectException $e) {
                Log::error('Flask API Connection Error: ' . $e->getMessage());
                return response()->json([
                    'message' => 'Tidak dapat terhubung ke server analisis. Pastikan server Flask berjalan.',
                    'status' => 'Tidak Valid',
                    'warna_status' => 'danger'
                ], 503);
            } catch (\GuzzleHttp\Exception\RequestException $e) {
                Log::error('Flask API Request Error: ' . $e->getMessage());
                return response()->json([
                    'message' => 'Gagal memproses gambar di server analisis.',
                    'status' => 'Tidak Valid',
                    'warna_status' => 'danger'
                ], 500);
            } catch (\Exception $e) {
                Log::error('Flask API General Error: ' . $e->getMessage());
                return response()->json([
                    'message' => 'Terjadi kesalahan pada server analisis.',
                    'status' => 'Tidak Valid',
                    'warna_status' => 'danger'
                ], 500);
            }

            // === Process Flask API Response ===
            $hasil_deteksi = strtoupper(trim($data['hasil_deteksi'] ?? ''));
            $confidence    = floatval($data['kepercayaan'] ?? 0);
            $bbox          = $data['bbox'] ?? null;
            $ocr_id        = $data['ocr_id'] ?? null;
            $ocr_confidence = $data['ocr_confidence'] ?? null;
            $lines_processed = $data['lines_processed'] ?? null;

            // Determine status based on confidence and detection result
            if ($hasil_deteksi === 'ODP' || $hasil_deteksi === 'ODP DETECTED') {
                if ($confidence >= 85) {
                    $status = 'Valid';
                    $warna_status = 'success';
                } else {
                    $status = 'Normal';
                    $warna_status = 'warning';
                }
            } else {
                $status = 'Tidak Valid';
                $warna_status = 'danger';
                $hasil_deteksi = 'Tidak Ada ODP';
            }

            // HANYA gunakan field yang ada di migrasi odp_detections
            $deteksi = OdpDetection::create([
                'photo_id'        => $photo->photo_id,
                'hasil_deteksi'   => $hasil_deteksi,
                'status'          => $status,
                'bbox'            => $bbox ? json_encode($bbox) : null,
                'ocr_text'        => $ocr_id,
                'ocr_conf'        => $ocr_confidence, // ✅ SESUAI MIGRASI: ocr_conf bukan ocr_confidence
                'detector_conf'   => $confidence,
                'classifier_conf' => $confidence,
                // ❌ HAPUS field yang tidak ada di migrasi:
                // 'lines_processed' => $lines_processed,
                // 'warna_status'    => $warna_status,
                // 'api_response'    => json_encode($data),
            ]);

            return response()->json([
                'message'        => 'Deteksi berhasil.',
                'photo_url'      => $fileUrl,
                'hasil_deteksi'  => $deteksi->hasil_deteksi,
                'status'         => $deteksi->status,
                'kepercayaan'    => $deteksi->detector_conf,
                'warna_status'   => $warna_status, // Kirim di response tapi jangan simpan di database
                'ocr_text'       => $deteksi->ocr_text,
                'ocr_confidence' => $deteksi->ocr_conf, // Sesuaikan dengan nama field di database
                'detection_id'   => $deteksi->id,
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validasi gagal: ' . $e->getMessage(),
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Store Error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Terjadi kesalahan server: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function show($id)
    {
        $detection = OdpDetection::with('photo')->findOrFail($id);
        return view('odp.show', compact('detection'));
    }

    public function destroy($id)
    {
        try {
            $detection = OdpDetection::with('photo')->findOrFail($id);

            // Delete associated photo file
            if ($detection->photo && $detection->photo->file_url) {
                // Extract path from URL untuk dihapus
                $path = str_replace(asset('storage/'), '', $detection->photo->file_url);
                Storage::disk('public')->delete($path);
                $detection->photo->delete();
            }

            $detection->delete();

            return redirect()->route('odp.index')
                ->with('success', 'Data deteksi berhasil dihapus.');

        } catch (\Exception $e) {
            Log::error('Delete Error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Gagal menghapus data deteksi.');
        }
    }

    public function exportExcel(Request $request)
{
    $start_date = $request->input('start_date');
    $end_date   = $request->input('end_date');
    $month      = $request->input('month');
    $year       = $request->input('year');
    $date       = now()->format('Y-m-d');

    return Excel::download(
        new OdpExport($start_date, $end_date, $month, $year),
        "data_deteksi_odp_{$date}.xlsx"
    );
}


    public function statistics()
    {
        $stats = [
            'total' => OdpDetection::count(),
            'today' => OdpDetection::whereDate('created_at', today())->count(),
            'this_week' => OdpDetection::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'this_month' => OdpDetection::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count(),
            'valid_count' => OdpDetection::where('status', 'Valid')->count(),
            'normal_count' => OdpDetection::where('status', 'Normal')->count(),
            'invalid_count' => OdpDetection::where('status', 'Tidak Valid')->count(),
            'avg_confidence' => round(OdpDetection::where('status', '!=', 'Tidak Valid')->avg('detector_conf') ?? 0, 2),
        ];

        return response()->json($stats);
    }
}
