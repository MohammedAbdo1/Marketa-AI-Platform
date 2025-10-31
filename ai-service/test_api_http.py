"""
Test Composition API via HTTP
Tests the FastAPI endpoints
"""
import requests
import json

BASE_URL = "http://localhost:8001/api"

def test_api():
    print("\n" + "="*70)
    print("[TEST] Composition API Endpoints (HTTP)")
    print("="*70)
    
    # Test 1: Health check
    print("\n[TEST 1] GET /health")
    try:
        response = requests.get("http://localhost:8001/health", timeout=5)
        if response.status_code == 200:
            print(f"[PASS] Service is healthy: {response.json()}")
        else:
            print(f"[FAIL] Status: {response.status_code}")
    except Exception as e:
        print(f"[FAIL] {e}")
        print("\n[ERROR] AI Service is not running!")
        print("[INFO] Start it with: python ai-service/run.py")
        return
    
    # Test 2: Analyze description (no composition needed)
    print("\n[TEST 2] POST /api/post/analyze-description (simple)")
    try:
        payload = {
            "description": "Beautiful garden with flowers",
            "platform": "instagram"
        }
        response = requests.post(
            f"{BASE_URL}/post/analyze-description",
            json=payload,
            timeout=10
        )
        
        if response.status_code == 200:
            data = response.json()
            print(f"[PASS] Analysis complete")
            print(f"  Needs composition: {data.get('needs_composition', False)}")
            if not data.get('needs_composition'):
                print("  [CORRECT] Simple scene - no composition needed")
        else:
            print(f"[FAIL] Status: {response.status_code}, {response.text[:200]}")
    except Exception as e:
        print(f"[FAIL] {e}")
    
    # Test 3: Analyze description (with composition - English to avoid encoding issues)
    print("\n[TEST 3] POST /api/post/analyze-description (with text)")
    try:
        payload = {
            "description": "Restaurant photo, write 'Grand Opening' in red at the bottom",
            "platform": "instagram",
            "business_type": "restaurant"
        }
        response = requests.post(
            f"{BASE_URL}/post/analyze-description",
            json=payload,
            timeout=30
        )
        
        if response.status_code == 200:
            data = response.json()
            print(f"[PASS] Analysis complete")
            print(f"  Needs composition: {data.get('needs_composition', False)}")
            print(f"  Scene: {data.get('scene_description', '')[:60]}...")
            print(f"  Text overlays: {len(data.get('text_overlays', []))}")
            
            if data.get('text_overlays'):
                for overlay in data['text_overlays']:
                    print(f"    - '{overlay.get('text')}' at {overlay.get('position')}, {overlay.get('color')}")
        else:
            print(f"[FAIL] Status: {response.status_code}")
            print(f"  Error: {response.text[:300]}")
    except Exception as e:
        print(f"[FAIL] {e}")
    
    print("\n" + "="*70)
    print("[TEST] API Tests Complete!")
    print("="*70 + "\n")


if __name__ == "__main__":
    test_api()

