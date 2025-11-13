import sys
import cv2
import numpy as np
import json
from tensorflow.keras.models import load_model

# ====== LOAD MODEL ======
model_path = "ai_model/Model_MobileNetV2.keras"  # Sesuaikan nama model kamu
model = load_model(model_path)

# ====== CEK ARGUMEN GAMBAR ======
if len(sys.argv) < 2:
    print(json.dumps({"error": "Path gambar tidak diberikan"}))
    sys.exit()

image_path = sys.argv[1]

# ====== BACA GAMBAR ======
try:
    img = cv2.imread(image_path)
    if img is None:
        raise ValueError("Gambar tidak ditemukan atau rusak")

    # ====== PRA-PROSESING SESUAI MODEL ======
    img_resized = cv2.resize(img, (224, 224))  # Sesuaikan dengan input model
    img_array = np.expand_dims(img_resized / 255.0, axis=0)

    # ====== PREDIKSI ======
    prediction = model.predict(img_array)
    class_id = int(np.argmax(prediction, axis=1)[0])

    # ====== KONVERSI CLASS KE LABEL TEKS ======
    label_map = {
        0: "ODP Normal",
        1: "ODP Rusak",
        2: "ODP Kotor"
    }

    label_name = label_map.get(class_id, "Tidak diketahui")

    # ====== BENTUK RESPON JSON UNTUK LARAVEL ======
    result = {
        "hasil_deteksi": label_name,
        "klasifikasi_fisik": "Baik" if class_id == 0 else "Perlu perawatan",
        "label_odp": f"ODP-{class_id:03d}"
    }

    print(json.dumps(result))

except Exception as e:
    print(json.dumps({"error": str(e)}))
