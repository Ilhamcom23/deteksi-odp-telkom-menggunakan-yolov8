# 🚀 Deteksi ODP Telkom Menggunakan YOLOv8

<p align="center">
  <b>Sistem Deteksi Otomatis untuk ODP (Optical Distribution Point) Telkom</b><br>
  Dibangun menggunakan <b>Laravel</b> dan <b>YOLOv8</b> untuk proses deteksi berbasis AI.
</p>

---

## 📸 Tampilan Dashboard

<p align="center">
  <img src="https://raw.githubusercontent.com/Ilhamcom23/deteksi-odp-telkom-menggunakan-yolov8/main/6100481349090217301.jpg" width="800" alt="Dashboard Deteksi ODP">
</p>

---

## 🖼️ Galeri Gambar Deteksi

<p align="center">
  <img src="https://raw.githubusercontent.com/Ilhamcom23/deteksi-odp-telkom-menggunakan-yolov8/main/6100235483687357495.jpg" width="400" alt="Gambar Deteksi 1">
  <img src="https://raw.githubusercontent.com/Ilhamcom23/deteksi-odp-telkom-menggunakan-yolov8/main/6100235483687357496.jpg" width="400" alt="Gambar Deteksi 2">
</p>

---

## 🧠 Tentang Proyek

Proyek ini bertujuan untuk **mendeteksi kondisi ODP (Optical Distribution Point)** menggunakan model **YOLOv8** dan menampilkan hasil deteksi pada dashboard berbasis web Laravel.

Selain deteksi objek ODP menggunakan YOLOv8, proyek ini juga mengintegrasikan **PaddleOCR** untuk melakukan **pengenalan teks (OCR)** pada label ODP yang terdeteksi, sehingga dapat membaca ID dan informasi penting dari label secara otomatis.  
Kombinasi AI detection dan OCR ini memungkinkan sistem tidak hanya mendeteksi posisi ODP, tapi juga membaca dan memvalidasi labelnya secara akurat.

Fitur utama:

- 🔍 Deteksi otomatis objek ODP dari gambar atau video menggunakan YOLOv8.
- 🔤 Pembacaan label ODP (ID, kode, dsb) secara otomatis menggunakan PaddleOCR.
- 💾 Menyimpan hasil deteksi dan teks OCR ke database.
- 📊 Tampilan dashboard interaktif lengkap dengan bounding box dan informasi label.
- ⚙️ Integrasi model YOLOv8 dan PaddleOCR dengan backend Laravel (via API Flask).

---

## 🧩 Teknologi yang Digunakan

| Komponen | Teknologi       |
| -------- | --------------- |
| Backend  | Laravel 10      |
| AI Model | YOLOv8 (Ultralytics) |
| OCR      | PaddleOCR       |
| Frontend | Blade + Bootstrap |
| Database | MySQL           |
| Server   | Laragon / XAMPP |

---

## ⚙️ Instalasi

```bash
# 1. Clone repository
git clone https://github.com/Ilhamcom23/deteksi-odp-telkom-menggunakan-yolov8.git

# 2. Masuk ke folder project
cd deteksi-odp-telkom-menggunakan-yolov8

# 3. Install dependency Laravel dan frontend
composer install
npm install

# 4. Salin file konfigurasi .env
cp .env.example .env

# 5. Generate key Laravel
php artisan key:generate

# 6. Jalankan server Laravel
php artisan serve
