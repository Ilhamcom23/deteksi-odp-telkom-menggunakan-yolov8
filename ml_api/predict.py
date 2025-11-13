from flask import Flask, request, jsonify
from ultralytics import YOLO
import cv2
import os
import numpy as np
from paddleocr import PaddleOCR
import re
from difflib import SequenceMatcher

app = Flask(__name__)

# Load YOLO model dan siapkan folder upload
BASE_DIR = os.path.dirname(os.path.abspath(__file__))
MODEL_PATH = os.path.join(BASE_DIR, "../ai_model/best.pt")
UPLOAD_DIR = os.path.join(BASE_DIR, "uploads")
os.makedirs(UPLOAD_DIR, exist_ok=True)

if not os.path.exists(MODEL_PATH):
    raise FileNotFoundError(f"Model YOLO tidak ditemukan di: {MODEL_PATH}")

model = YOLO(MODEL_PATH)

# Inisialisasi PaddleOCR untuk bahasa Indonesia
ocr = PaddleOCR(
    use_angle_cls=True,
    lang='id',
    show_log=False,
    rec_batch_num=4,
    det_db_score_mode='slow',
    rec_image_shape='3, 48, 320'
)

def expand_roi_for_multiline(image, bbox):
    """Perbesar area bbox untuk teks multiline"""
    x1, y1, x2, y2 = map(int, bbox)
    img_height, img_width = image.shape[:2]

    bbox_height = y2 - y1
    line_height = bbox_height * 0.15
    total_lines_height = line_height * 4

    expand_top = min(y1, int(total_lines_height * 0.8))
    expand_bottom = min(img_height - y2, int(total_lines_height * 0.2))
    expand_horizontal = min(x1, (x2 - x1) // 3)

    new_y1 = max(0, y1 - expand_top)
    new_y2 = min(img_height, y2 + expand_bottom)
    new_x1 = max(0, x1 - expand_horizontal)
    new_x2 = min(img_width, x2 + expand_horizontal)

    return new_x1, new_y1, new_x2, new_y2

def extract_text_lines_from_ocr(ocr_result):
    """Ambil dan grup teks hasil OCR berdasarkan garis"""
    if not ocr_result or not ocr_result[0]:
        return []

    detections = []
    for line in ocr_result[0]:
        bbox = line[0]
        text = line[1][0]
        confidence = line[1][1]

        y_coords = [point[1] for point in bbox]
        avg_y = sum(y_coords) / len(y_coords)

        detections.append({
            'text': text,
            'confidence': confidence,
            'avg_y': avg_y,
            'bbox': bbox
        })

    if not detections:
        return []

    detections.sort(key=lambda x: x['avg_y'])  # Urutkan berdasar posisi vertikal

    lines = []
    current_line = [detections[0]]
    line_threshold = 20

    for i in range(1, len(detections)):
        current_det = detections[i]
        prev_det = detections[i-1]

        if abs(current_det['avg_y'] - prev_det['avg_y']) < line_threshold:
            current_line.append(current_det)
        else:
            lines.append(current_line)
            current_line = [current_det]

    lines.append(current_line)

    # Urutkan teks per garis berdasarkan posisi horizontal (X)
    for line in lines:
        line.sort(key=lambda x: min(point[0] for point in x['bbox']))

    return lines

def text_similarity(text1, text2):
    """Hitung kemiripan dua teks"""
    return SequenceMatcher(None, text1.upper(), text2.upper()).ratio()

def deduplicate_ocr_results(all_results, similarity_threshold=0.7):
    """Hapus hasil OCR yang duplikat berdasarkan kemiripan"""
    unique_results = []

    for result in all_results:
        is_duplicate = False
        for unique in unique_results:
            similarity = text_similarity(result['text'], unique['text'])
            if similarity > similarity_threshold:
                if result['confidence'] > unique['confidence']:
                    unique['text'] = result['text']
                    unique['confidence'] = result['confidence']
                is_duplicate = True
                break

        if not is_duplicate:
            unique_results.append(result)

    return unique_results

def merge_multiline_text(text_lines):
    """Gabungkan teks multi baris jadi satu string"""
    if not text_lines:
        return "", 0

    all_lines = []
    overall_confidence = 0
    total_detections = 0

    for line_detections in text_lines:
        line_text_parts = []
        line_confidence = 0

        for detection in line_detections:
            line_text_parts.append(detection['text'])
            line_confidence += detection['confidence']

        if line_text_parts:
            line_text = ' '.join(line_text_parts)
            all_lines.append(line_text)
            overall_confidence += line_confidence
            total_detections += len(line_detections)

    if total_detections > 0:
        overall_confidence /= total_detections

    final_text = ' '.join(all_lines)
    return final_text, overall_confidence

def find_best_odp_pattern(text):
    """Cari pola ODP terbaik dari teks hasil OCR"""
    text = ' '.join(text.split()).upper().strip()

    patterns = [
        r'(ODP[-\s/]?[A-Z]{2,}[-\s/]?[A-Z]{2,}[-\s/]?\d+[\s]+[A-Z]?\.[\d/]+[A-Z]?\.[\d/]+)',
        r'(ODP[-\s/]?[A-Z]{2,}[-\s/]?[A-Z]{2,}[-\s/]?\d+[\s]*[A-Z]?\.[\d/]+[A-Z]?\.[\d/]+)',
        r'(ODP[-\s/]?[A-Z]{2,}[-\s/]?[A-Z]{2,}[-\s/]?\d+[\s]*[A-Z]?\.[\d/]+)',
        r'(ODP[-\s/]?[A-Z]{2,}[-\s/]?[A-Z]{2,}[-\s/]?\d+)',
        r'(ODP[-\s/]?[A-Z]{2,}[-\s/]?\d+[\s]*[A-Z]?\.[\d/]+[A-Z]?\.[\d/]+)',
        r'(ODP[-\s/]?[A-Z0-9\-\s/\.]{8,50})',
        r'([A-Z]{2,}[-\s/]?[A-Z]{2,}[-\s/]?\d+[\s]+[A-Z]?\.[\d/]+[A-Z]?\.[\d/]+)',
    ]

    for i, pattern in enumerate(patterns):
        matches = re.findall(pattern, text)
        if matches:
            best_match = matches[0]
            cleaned = clean_odp_text(best_match)
            return cleaned

    return clean_odp_text(text)

def clean_odp_text(text):
    """Bersihkan teks ODP dan jaga formatnya"""
    text = ' '.join(text.split()).upper().strip()

    text = re.sub(r'\s*([\-/])\s*', r'\1', text)  # Hilangkan spasi di sekitar - dan /
    text = re.sub(r'\s*\.\s*', '.', text)        # Hilangkan spasi di sekitar titik

    text = re.sub(r'([A-Z])\.(\d)', r'\1.\2', text)  # Jaga format D.05/C.12
    text = re.sub(r'(\d)\.([A-Z])', r'\1.\2', text)

    text = re.sub(r'[^\w\s\-/\.]', '', text)  # Buang karakter selain huruf, angka, -, /, .

    if not text.startswith('ODP') and any(word in text for word in ['TAN', 'FEE', 'D.05', 'C.12']):
        if 'TAN-FEE' in text:
            text = 'ODP-' + text

    return text

def preprocess_for_ocr(region):
    """Preprocessing gambar untuk OCR"""
    if len(region.shape) == 3:
        gray = cv2.cvtColor(region, cv2.COLOR_BGR2GRAY)
    else:
        gray = region

    processed_variants = []

    # Resize jika gambar terlalu kecil
    if gray.shape[0] < 100:
        resized = cv2.resize(gray, None, fx=2, fy=2, interpolation=cv2.INTER_CUBIC)
        processed_variants.append(resized)
    else:
        processed_variants.append(gray)

    # Thresholding dasar (Otsu)
    _, otsu = cv2.threshold(gray, 0, 255, cv2.THRESH_BINARY + cv2.THRESH_OTSU)
    processed_variants.append(otsu)

    # Thresholding adaptif
    adaptive = cv2.adaptiveThreshold(gray, 255, cv2.ADAPTIVE_THRESH_GAUSSIAN_C,
                                     cv2.THRESH_BINARY, 11, 2)
    processed_variants.append(adaptive)

    return processed_variants

@app.route('/predict', methods=['POST'])
def predict():
    try:
        if 'image' not in request.files:
            return jsonify({"error": "Gambar tidak ditemukan"}), 400

        file = request.files['image']
        image_path = os.path.join(UPLOAD_DIR, file.filename)
        file.save(image_path)

        img = cv2.imread(image_path)
        if img is None:
            return jsonify({"error": "Gambar tidak dapat dibaca"}), 400

        # Prediksi menggunakan YOLO
        results = model.predict(source=image_path, imgsz=640, conf=0.25)
        detections = results[0].boxes

        if detections is None or len(detections) == 0:
            return jsonify({
                "hasil_deteksi": "Tidak ditemukan ODP",
                "kepercayaan": 0,
                "status": "Tidak Valid",
                "bbox": [],
                "ocr_id": "",
                "ocr_confidence": 0
            }), 200

        # Ambil deteksi dengan confidence tertinggi
        det = max(detections, key=lambda d: float(d.conf[0].item()))
        confidence = float(det.conf[0].item())
        bbox = det.xyxy[0].tolist()

        # Perbesar area bbox untuk OCR multiline
        x1, y1, x2, y2 = expand_roi_for_multiline(img, bbox)
        expanded_region = img[y1:y2, x1:x2]

        if expanded_region.size == 0:
            return jsonify({
                "hasil_deteksi": "ODP",
                "kepercayaan": round(confidence * 100, 2),
                "status": "Valid" if confidence >= 0.85 else "Normal",
                "bbox": bbox,
                "ocr_id": "",
                "ocr_confidence": 0
            }), 200

        all_text_lines = []
        best_confidence = 0

        # Preprocessing varian untuk OCR
        preprocessed_variants = preprocess_for_ocr(expanded_region)

        # OCR untuk tiap varian
        for variant_idx, processed_img in enumerate(preprocessed_variants):
            try:
                ocr_result = ocr.ocr(processed_img, cls=True)

                if ocr_result and ocr_result[0]:
                    text_lines = extract_text_lines_from_ocr(ocr_result)

                    if text_lines:
                        variant_confidence = sum(
                            det['confidence'] for line in text_lines for det in line
                        ) / sum(len(line) for line in text_lines)

                        if variant_confidence > best_confidence:
                            all_text_lines = text_lines
                            best_confidence = variant_confidence

            except Exception as e:
                continue

        final_text = ""
        final_confidence = 0

        # Gabungkan hasil OCR dan cari pola ODP
        if all_text_lines:
            merged_text, merged_confidence = merge_multiline_text(all_text_lines)
            final_text = find_best_odp_pattern(merged_text)
            final_confidence = merged_confidence

            # Coba rekonstruksi manual jika hasil kurang lengkap
            if len(final_text) < 15 and 'TAN-FEE' in merged_text:
                parts = [' '.join([det['text'] for det in line]).strip() for line in all_text_lines]

                combinations = [
                    ' '.join(parts),
                    ' '.join(parts[:2]),
                    parts[0] + ' ' + parts[1] if len(parts) >= 2 else '',
                    'ODP-' + ' '.join(parts) if 'ODP' not in ' '.join(parts) else ' '.join(parts)
                ]

                for combo in combinations:
                    temp_result = find_best_odp_pattern(combo)
                    if len(temp_result) > len(final_text):
                        final_text = temp_result

        # Tentukan status berdasarkan confidence YOLO
        conf_percent = confidence * 100
        status = "Valid" if conf_percent >= 85 else "Normal"

        response = {
            "hasil_deteksi": "ODP",
            "kepercayaan": round(conf_percent, 2),
            "status": status,
            "bbox": [int(x) for x in bbox],
            "ocr_id": final_text,
            "ocr_confidence": round(final_confidence * 100, 2),
            "lines_processed": len(all_text_lines) if all_text_lines else 0
        }

        return jsonify(response), 200

    except Exception as e:
        return jsonify({"error": str(e)}), 500

if __name__ == '__main__':
    app.run(host='127.0.0.1', port=5000, debug=True)
