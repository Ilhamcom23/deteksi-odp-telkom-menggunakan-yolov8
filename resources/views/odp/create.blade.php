<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Upload & Analisis Gambar ODP</title>

  <meta name="csrf-token" content="{{ csrf_token() }}">
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://cdn.jsdelivr.net/npm/@tensorflow/tfjs"></script>
  <script src="https://cdn.jsdelivr.net/npm/@tensorflow-models/coco-ssd"></script>
  <style>
    .camera-container {
      position: relative;
      width: 100%;
      max-height: 480px;
    }
    #cameraPreview { width: 100%; border-radius: 0.5rem; }
    #motionCanvas {
      position: absolute;
      top: 0; left: 0;
      width: 100%; height: 100%;
      z-index: 10;
      pointer-events: none;
      border-radius: 0.5rem;
    }
  </style>
</head>
<body class="bg-gray-50 text-gray-800 min-h-screen flex flex-col">
  <div class="max-w-3xl mx-auto p-6 flex-grow">
    <div class="flex flex-col sm:flex-row justify-between items-center mb-6 gap-3">
      <h1 class="text-3xl font-bold text-blue-700 text-center sm:text-left">📷 Upload / Kamera ODP</h1>
      <a href="{{ route('odp.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600 transition">← Kembali ke Dashboard</a>
    </div>

    <div class="bg-white rounded-xl shadow-md p-6 space-y-5">
      <div class="flex flex-wrap gap-3">
        <button id="btnUploadMode" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">📁 Upload Gambar</button>
        <button id="btnCameraMode" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition">🎥 Gunakan Kamera Realtime</button>
      </div>

      <form id="uploadForm" enctype="multipart/form-data" class="space-y-5 mt-4">
        @csrf
        <div id="uploadSection">
          <label for="image" class="block text-lg font-semibold mb-2 text-gray-700">Pilih Gambar ODP:</label>
          <input type="file" name="image" id="image" accept="image/*" class="w-full border rounded-lg p-3 focus:ring-2 focus:ring-blue-500 focus:outline-none">
        </div>

        <div id="cameraSection" class="hidden flex flex-col items-center space-y-3 relative">
          <div class="camera-container w-full max-w-2xl">
            <video id="cameraPreview" autoplay muted playsinline class="rounded-lg shadow-md"></video>
            <canvas id="motionCanvas"></canvas>
          </div>

          <button id="captureBtn" type="button" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition">📸 Ambil Foto</button>
          <canvas id="cameraCanvas" class="hidden"></canvas>
        </div>

        <div class="flex justify-end">
          <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg font-medium hover:bg-blue-700 transition">🔍 Analisis Sekarang</button>
        </div>
      </form>
    </div>

    <div id="preview-container" class="mt-6 hidden">
      <p class="font-semibold text-gray-700 mb-2">Preview Gambar:</p>
      <img id="preview-image" src="#" alt="Preview Gambar" class="rounded-lg shadow-md max-h-96">
    </div>
  </div>

  <footer class="bg-white shadow-inner py-4 text-center text-gray-500 mt-10 border-t">
    © <span id="year"></span> Sistem Analisis ODP. All rights reserved.
  </footer>

<script>
  document.getElementById("year").textContent = new Date().getFullYear();

  const uploadBtn = document.getElementById("btnUploadMode");
  const cameraBtn = document.getElementById("btnCameraMode");
  const uploadSection = document.getElementById("uploadSection");
  const cameraSection = document.getElementById("cameraSection");
  const previewContainer = document.getElementById("preview-container");
  const previewImage = document.getElementById("preview-image");
  const video = document.getElementById("cameraPreview");
  const canvas = document.getElementById("cameraCanvas");
  const motionCanvas = document.getElementById("motionCanvas");
  const ctxMotion = motionCanvas.getContext("2d");
  const captureBtn = document.getElementById("captureBtn");
  const imageInput = document.getElementById("image");
  const form = document.getElementById("uploadForm");
  let stream, loopId, cameraImageBlob = null;

  uploadBtn.onclick = () => {
    uploadSection.classList.remove("hidden");
    cameraSection.classList.add("hidden");
    previewContainer.classList.add("hidden");
    stopCamera();
  };
  cameraBtn.onclick = async () => {
    uploadSection.classList.add("hidden");
    cameraSection.classList.remove("hidden");
    previewContainer.classList.add("hidden");
    startCamera();
  };

  async function startCamera() {
    try {
      stream = await navigator.mediaDevices.getUserMedia({ video: true });
      video.srcObject = stream;
      video.onloadedmetadata = () => {
        video.play();
        motionCanvas.width = video.videoWidth || 640;
        motionCanvas.height = video.videoHeight || 480;
        detectMotion();
      };
    } catch {
      Swal.fire("Gagal Akses Kamera", "Pastikan izin kamera diaktifkan.", "error");
    }
  }
  function stopCamera() {
    if (stream) stream.getTracks().forEach(t => t.stop());
    cancelAnimationFrame(loopId);
  }

  function detectMotion() {
    const tempCanvas = document.createElement("canvas");
    const tempCtx = tempCanvas.getContext("2d");
    tempCanvas.width = motionCanvas.width;
    tempCanvas.height = motionCanvas.height;
    let prevFrame = null;
    function frameLoop() {
      tempCtx.drawImage(video, 0, 0, tempCanvas.width, tempCanvas.height);
      const currFrame = tempCtx.getImageData(0, 0, tempCanvas.width, tempCanvas.height);
      if (prevFrame) {
        let diff = 0;
        for (let i = 0; i < currFrame.data.length; i += 4)
          diff += Math.abs(currFrame.data[i] - prevFrame.data[i]);
        if (diff > 5000000) {
          ctxMotion.clearRect(0, 0, motionCanvas.width, motionCanvas.height);
          ctxMotion.strokeStyle = "lime";
          ctxMotion.lineWidth = 3;
          ctxMotion.strokeRect(50, 50, motionCanvas.width - 100, motionCanvas.height - 100);
          ctxMotion.fillStyle = "lime";
          ctxMotion.font = "16px Arial";
          ctxMotion.fillText("Gerakan Terdeteksi", 60, 70);
        } else ctxMotion.clearRect(0, 0, motionCanvas.width, motionCanvas.height);
      }
      prevFrame = currFrame;
      loopId = requestAnimationFrame(frameLoop);
    }
    frameLoop();
  }

  captureBtn.onclick = () => {
    if (!stream) return Swal.fire("Kamera belum aktif", "Silakan aktifkan kamera terlebih dahulu.", "warning");
    const ctx = canvas.getContext("2d");
    canvas.width = video.videoWidth || 640;
    canvas.height = video.videoHeight || 480;
    ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
    canvas.toBlob(blob => {
      cameraImageBlob = blob;
      previewImage.src = URL.createObjectURL(blob);
      previewContainer.classList.remove("hidden");
    }, "image/jpeg");
  };

  imageInput.onchange = e => {
    const file = e.target.files[0];
    cameraImageBlob = null;
    if (file) {
      previewImage.src = URL.createObjectURL(file);
      previewContainer.classList.remove("hidden");
    } else previewContainer.classList.add("hidden");
  };

  form.onsubmit = async e => {
    e.preventDefault();
    Swal.fire({ title: "Sedang mendeteksi", text: "Mohon tunggu...", didOpen: () => Swal.showLoading() });

    let imgFile = cameraImageBlob || imageInput.files[0];
    if (!imgFile) {
      Swal.close();
      return Swal.fire("Tidak ada gambar!", "Silakan upload atau ambil foto.", "warning");
    }

    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const formData = new FormData();
    formData.append('_token', csrfToken);
    formData.append('image', imgFile, imgFile.name || `kamera_${Date.now()}.jpg`);

    try {
      const res = await fetch("{{ route('odp.store') }}", {
        method: "POST",
        headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
        body: formData
      });

      const data = await res.json().catch(() => null);
      console.log("Respons server:", data);

      if (!res.ok) {
        const msg = data?.message || 'Gagal mengirim data ke server';
        Swal.fire('Gagal!', msg, 'error');
        return;
      }

      const hasilServer = data?.hasil_deteksi ?? "-";
      const statusServer = data?.status ?? "-";
      const confidence = data?.kepercayaan ?? "-";
      const warnaStatus = data?.warna_status ?? "secondary";
      const ocrText = data?.ocr_text ?? "-";

      // Tentukan icon berdasarkan status
      let icon = "info";
      if (statusServer === "Valid") icon = "success";
      if (statusServer === "Tidak Valid") icon = "error";
      if (statusServer === "Normal") icon = "warning";

      // Tentukan warna teks status berdasarkan warna_status
      let statusColor = "text-gray-600";
      if (warnaStatus === "success") statusColor = "text-green-600";
      if (warnaStatus === "warning") statusColor = "text-yellow-600";
      if (warnaStatus === "danger") statusColor = "text-red-600";

      Swal.fire({
        title: "Analisis & Simpan Berhasil 🎉",
        html: `
          <div class="text-left space-y-2">
            <p><b>Hasil Deteksi:</b> ${hasilServer}</p>
            <p><b>Status:</b> <span class="${statusColor} font-semibold">${statusServer}</span></p>
            <p><b>Tingkat Kepercayaan:</b> ${confidence}%</p>
            ${ocrText && ocrText !== "-" ? `<p><b>OCR Text:</b> ${ocrText}</p>` : ''}
            <hr>
            <small class="text-gray-500">${data?.message ?? 'Data berhasil disimpan'}</small>
          </div>
        `,
        icon: icon,
        confirmButtonColor: "#06b6d4",
        background: "#ffffff",
        color: "#111827"
      });

      // Reset form
      cameraImageBlob = null;
      imageInput.value = '';
      previewContainer.classList.add("hidden");

    } catch (err) {
      console.error('Fetch error:', err);
      Swal.fire('Gagal!', 'Tidak dapat menghubungi server. Cek koneksi atau konfigurasi server.', 'error');
    }
  };
</script>
</body>
</html>
