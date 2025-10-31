#!/usr/bin/env python3
"""Direct test of Stability API to verify key and find correct endpoint/model"""
import requests
import sys

API_KEY = "sk-IgdBL3XiUD98q89HH3cjKHY9XCaKPzUTB0AJxsqPy5XdlIu3"

print("Testing Stability AI API...")
print("=" * 60)

# Test 1: V2 Beta SD3 endpoint
print("\n1. Testing v2beta/stable-image/generate/sd3...")
try:
    r = requests.post(
        'https://api.stability.ai/v2beta/stable-image/generate/sd3',
        headers={
            'authorization': f'Bearer {API_KEY}',
            'accept': 'image/*'
        },
        files={'none': ''},
        data={
            'prompt': 'A professional photo of a modern office',
            'output_format': 'png',
            'aspect_ratio': '1:1'
        },
        timeout=30
    )
    print(f"   Status: {r.status_code}")
    if r.status_code == 200:
        print(f"   ✅ SUCCESS! Image size: {len(r.content)} bytes")
        sys.exit(0)
    else:
        print(f"   ❌ Error: {r.text[:300]}")
except Exception as e:
    print(f"   ❌ Exception: {e}")

# Test 2: V1 SDXL endpoint (old style)
print("\n2. Testing v1/generation/stable-diffusion-xl-1024-v1-0/text-to-image...")
try:
    r = requests.post(
        'https://api.stability.ai/v1/generation/stable-diffusion-xl-1024-v1-0/text-to-image',
        headers={
            'Authorization': f'Bearer {API_KEY}',
            'Content-Type': 'application/json'
        },
        json={
            'text_prompts': [{'text': 'A professional photo of a modern office', 'weight': 1.0}],
            'cfg_scale': 7,
            'height': 1024,
            'width': 1024,
            'samples': 1,
            'steps': 30
        },
        timeout=30
    )
    print(f"   Status: {r.status_code}")
    if r.status_code == 200:
        result = r.json()
        if 'artifacts' in result and result['artifacts']:
            print(f"   ✅ SUCCESS! Base64 length: {len(result['artifacts'][0]['base64'])}")
            sys.exit(0)
    else:
        print(f"   ❌ Error: {r.text[:300]}")
except Exception as e:
    print(f"   ❌ Exception: {e}")

# Test 3: V2 Beta Ultra endpoint
print("\n3. Testing v2beta/stable-image/generate/ultra...")
try:
    r = requests.post(
        'https://api.stability.ai/v2beta/stable-image/generate/ultra',
        headers={
            'authorization': f'Bearer {API_KEY}',
            'accept': 'image/*'
        },
        files={'none': ''},
        data={
            'prompt': 'A professional photo of a modern office',
            'output_format': 'png',
            'aspect_ratio': '1:1'
        },
        timeout=30
    )
    print(f"   Status: {r.status_code}")
    if r.status_code == 200:
        print(f"   ✅ SUCCESS! Image size: {len(r.content)} bytes")
        sys.exit(0)
    else:
        print(f"   ❌ Error: {r.text[:300]}")
except Exception as e:
    print(f"   ❌ Exception: {e}")

print("\n" + "=" * 60)
print("All tests failed. Check your API key and Stability account status.")

