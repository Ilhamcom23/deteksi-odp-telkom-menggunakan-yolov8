from flask import Flask, request, jsonify
from ultralytics import YOLO
from PIL import Image
import numpy as np
import io
import os

app = Flask(__name__)

# Path model YOLO
MODEL_PATH = '../ai_model/model_odp_yolov8.pt'

# Cek apakah model tersedia
if not os.path.exists(MODEL_PATH):
    raise FileNotFoundError(f"Model YOLO tidak ditemukan di: {MODEL_PATH}")

# Load model YOLOv8
model = YOLO(MODEL_PATH)

@app.route('/')
def home():
    return jsonify({'message': 'API Deteksi ODP menggunakan YOLOv8 berjalan dengan baik!'})

@app.route('/predict', methods=['POST'])
def predict():
    try:
        if 'image' not in request.files:
            return jsonify({'error': 'Tidak ada file gambar dikirim!'}), 400

        file = request.files['image']
        img = Image.open(file.stream)

        # Prediksi dengan YOLOv8
        results = model.predict(source=img, save=False, imgsz=640)

        detections = []
        for r in results:
            for box in r.boxes:
                class_id = int(box.cls)
                confidence = float(box.conf)
                bbox = box.xyxy[0].tolist()  # [x1, y1, x2, y2]

                detections.append({
                    "class": model.names[class_id],
                    "confidence": round(confidence, 4),
                    "bbox": {
                        "x1": float(bbox[0]),
                        "y1": float(bbox[1]),
                        "x2": float(bbox[2]),
                        "y2": float(bbox[3]),
                    }
                })

        return jsonify({"detections": detections})

    except Exception as e:
        return jsonify({'error': str(e)}), 500

if __name__ == '__main__':
    app.run(host="0.0.0.0", port=5001, debug=True)
