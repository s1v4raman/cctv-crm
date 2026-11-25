import sys
import json
import re
import cv2
import numpy as np

def parse_device_info(raw_codes):
    serial = ""
    mac = ""
    model = ""
    ean = ""

    combined = "\n".join(raw_codes)

    # 1. MAC address detection
    mac_regex = re.compile(r'(?:MAC(?:\s*[:=-]|\s+)?)?((?:[0-9A-Fa-f]{2}[:-]){5}[0-9A-Fa-f]{2})\b', re.IGNORECASE)
    mac_match = mac_regex.search(combined)
    if mac_match and mac_match.group(1):
        mac = mac_match.group(1).upper().replace('-', ':')
    else:
        # 12 hex chars preceded by MAC
        mac_hex = re.search(r'\b(?:MAC\s*[:=-]?\s*)([0-9A-Fa-f]{12})\b', combined, re.IGNORECASE)
        if mac_hex:
            raw_hex = mac_hex.group(1).upper()
            mac = ":".join(raw_hex[i:i+2] for i in range(0, 12, 2))

    # 2. Explicit S/N regex
    sn_regex = re.compile(r'(?:S/N|SN|SERIAL(?:\s*NO)?|NO)[:\s=-]+([A-Za-z0-9\-_]+)', re.IGNORECASE)
    sn_match = sn_regex.search(combined)
    if sn_match and sn_match.group(1):
        serial = sn_match.group(1).strip()

    # 3. Explicit EAN regex
    ean_regex = re.compile(r'(?:EAN|UPC|BARCODE)[:\s=-]+(\d{12,14})\b', re.IGNORECASE)
    ean_match = ean_regex.search(combined)
    if ean_match and ean_match.group(1):
        ean = ean_match.group(1).strip()

    # 4. Explicit Model regex
    model_regex = re.compile(r'(?:MODEL|MDL|PROD|ITEM|PART)[:\s=-]+([A-Za-z0-9\-_\./]+)', re.IGNORECASE)
    model_match = model_regex.search(combined)
    if model_match and model_match.group(1):
        model = model_match.group(1).strip()

    # Fallback to inspecting individual codes
    for c in raw_codes:
        c = c.strip()
        if not c:
            continue
        # Check if code is pure EAN (12-14 digits)
        if not ean and re.match(r'^\d{12,14}$', c):
            ean = c
            continue
        # Check if code looks like MAC
        if not mac and re.match(r'^([0-9A-Fa-f]{2}[:-]){5}[0-9A-Fa-f]{2}$', c):
            mac = c.upper().replace('-', ':')
            continue
        # Check CCTV model prefix
        if not model and re.match(r'^(?:DS-|CP-|DH-|IPC-|HWI-|UNV-|NVR|DVR)', c, re.IGNORECASE):
            model = c
            continue
        # Check serial candidate (6-26 chars)
        if not serial and not re.match(r'^\d{12,14}$', c) and not re.match(r'^([0-9A-Fa-f]{2}[:-]){5}[0-9A-Fa-f]{2}$', c):
            if re.match(r'^[A-Za-z0-9\-_]{6,26}$', c):
                serial = c

    # If serial is still empty, fallback to first non-empty code
    if not serial and raw_codes:
        for c in raw_codes:
            if c != mac and c != ean:
                serial = c
                break
        if not serial:
            serial = raw_codes[0]

    return {
        "serial": serial,
        "mac": mac,
        "model": model,
        "ean": ean
    }

def rotate_image(image, angle):
    if angle == 90:
        return cv2.rotate(image, cv2.ROTATE_90_CLOCKWISE)
    elif angle == 180:
        return cv2.rotate(image, cv2.ROTATE_180)
    elif angle == 270:
        return cv2.rotate(image, cv2.ROTATE_90_COUNTERCLOCKWISE)
    return image

def detect_barcodes_in_image(image_path):
    img = cv2.imread(image_path)
    if img is None:
        return {"success": False, "error": "Could not read image"}

    h, w = img.shape[:2]
    # Resize if too large for speed while keeping crisp detail
    max_dim = 1600
    if max(h, w) > max_dim:
        scale = max_dim / float(max(h, w))
        img = cv2.resize(img, (int(w * scale), int(h * scale)), interpolation=cv2.INTER_AREA)

    bd = cv2.barcode.BarcodeDetector()
    qr = cv2.QRCodeDetector()

    detected_codes = set()

    # Pre-generate crop regions
    h_curr, w_curr = img.shape[:2]
    crops = [
        ("full", img),
        ("center_crop", img[int(h_curr*0.15):int(h_curr*0.85), int(w_curr*0.15):int(w_curr*0.85)]),
        ("top_half", img[0:int(h_curr*0.65), :]),
        ("bottom_half", img[int(h_curr*0.35):, :]),
    ]

    angles = [0, 90, 180, 270]

    for crop_name, crop_img in crops:
        if crop_img.shape[0] < 50 or crop_img.shape[1] < 50:
            continue

        gray = cv2.cvtColor(crop_img, cv2.COLOR_BGR2GRAY) if len(crop_img.shape) == 3 else crop_img
        
        # Variations: Grayscale, CLAHE, Adaptive Threshold
        clahe = cv2.createCLAHE(clipLimit=2.5, tileGridSize=(8,8))
        gray_clahe = clahe.apply(gray)
        
        variations = [gray, gray_clahe]

        for var in variations:
            for angle in angles:
                target = rotate_image(var, angle)

                # 1. Barcode detector
                try:
                    ok, info, types, pts = bd.detectAndDecodeMulti(target)
                    if ok and info:
                        for item in info:
                            val = str(item).strip()
                            if val:
                                detected_codes.add(val)
                except Exception:
                    pass

                # 2. QR code detector
                try:
                    ok_qr, info_qr, pts_qr, _ = qr.detectAndDecodeMulti(target)
                    if ok_qr and info_qr:
                        for item in info_qr:
                            val = str(item).strip()
                            if val:
                                detected_codes.add(val)
                except Exception:
                    pass

                # If we already found barcodes from this region, continue
                if len(detected_codes) >= 3:
                    break
            if len(detected_codes) >= 3:
                break
        if len(detected_codes) >= 3:
            break

    codes_list = list(detected_codes)
    if not codes_list:
        return {"success": False, "message": "No barcode detected"}

    parsed = parse_device_info(codes_list)

    # Form composite representation
    parts = []
    if parsed["serial"]:
        parts.append(f"S/N: {parsed['serial']}")
    if parsed["mac"]:
        parts.append(f"MAC: {parsed['mac']}")
    if parsed["ean"]:
        parts.append(f"EAN: {parsed['ean']}")
    if parsed["model"]:
        parts.append(f"MODEL: {parsed['model']}")

    composite_code = " | ".join(parts) if parts else codes_list[0]

    return {
        "success": True,
        "raw_codes": codes_list,
        "composite_code": composite_code,
        "parsed": parsed
    }

if __name__ == "__main__":
    if len(sys.argv) < 2:
        print(json.dumps({"success": False, "error": "No image argument provided"}))
        sys.exit(1)

    result = detect_barcodes_in_image(sys.argv[1])
    print(json.dumps(result))
