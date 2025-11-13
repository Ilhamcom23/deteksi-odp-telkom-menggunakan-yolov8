import requests

url = "http://127.0.0.1:5001/predict"
image_path = "../ai_model/odp.jpeg"

with open(image_path, "rb") as img:
    files = {"image": img}
    response = requests.post(url, files=files)

print("Response:", response.json())
