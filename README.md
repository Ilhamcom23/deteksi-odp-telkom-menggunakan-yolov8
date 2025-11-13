<h1 align="center">🚀 Deteksi ODP Telkom Menggunakan YOLOv8</h1>

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

## 🧠 Tentang Proyek

Proyek ini bertujuan untuk **mendeteksi kondisi ODP (Optical Distribution Point)** menggunakan model **YOLOv8** dan menampilkan hasil deteksi pada dashboard berbasis web Laravel.  
Fitur utama:

- 🔍 Deteksi otomatis objek ODP dari gambar atau video.
- 💾 Menyimpan hasil deteksi ke database.
- 📊 Tampilan dashboard interaktif.
- ⚙️ Integrasi model YOLOv8 dengan backend Laravel (via API Flask).

---

## 🧩 Teknologi yang Digunakan

| Komponen | Teknologi |
|-----------|------------|
| Backend | Laravel 10 |
| AI Model | YOLOv8 (Ultralytics) |
| Frontend | Blade + Bootstrap |
| Database | MySQL |
| Server | Laragon / XAMPP |

---

## ⚙️ Instalasi

```bash
# 1. Clone repository
git clone https://github.com/Ilhamcom23/deteksi-odp-telkom-menggunakan-yolov8.git

# 2. Masuk ke folder
cd deteksi-odp-telkom-menggunakan-yolov8

# 3. Install dependency Laravel
composer install
npm install

# 4. Salin file .env
cp .env.example .env

# 5. Generate key Laravel
php artisan key:generate

# 6. Jalankan server
php artisan serve
