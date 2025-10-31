"""
Test Storage Layer
Quick test to verify storage layer works correctly
"""
import asyncio
import sys
import os
sys.path.append(os.path.dirname(os.path.abspath(__file__)))

from config import settings
from app.services.storage import storage


async def test_storage():
    """Test storage save/get/delete operations"""
    print("\n" + "="*50)
    print("[TEST] Testing Storage Layer")
    print("="*50)
    
    # Test data
    test_image = b"fake image data for testing"
    test_filename = "test_image_123.png"
    
    try:
        # Test 1: Save image
        print("\n[TEST] 1. Testing save_image()...")
        url = await storage.save_image(test_image, test_filename, "image/png")
        print(f"[TEST] PASS: Image saved successfully!")
        print(f"   URL: {url}")
        
        # Test 2: Get image
        print("\n[TEST] 2. Testing get_image()...")
        downloaded_data = await storage.get_image(url)
        assert downloaded_data == test_image, "Downloaded data doesn't match!"
        print(f"[TEST] PASS: Image retrieved successfully!")
        print(f"   Size: {len(downloaded_data)} bytes")
        
        # Test 3: Delete image
        print("\n[TEST] 3. Testing delete_image()...")
        deleted = await storage.delete_image(url)
        assert deleted, "Delete operation failed!"
        print(f"[TEST] PASS: Image deleted successfully!")
        
        print("\n" + "="*50)
        print("[TEST] SUCCESS: All storage tests passed!")
        print("="*50 + "\n")
        
    except Exception as e:
        print(f"\n[TEST] FAILED: {e}")
        import traceback
        traceback.print_exc()
        sys.exit(1)


if __name__ == "__main__":
    print(f"\n[TEST] Configuration:")
    print(f"   Storage Backend: {settings.STORAGE_BACKEND}")
    print(f"   Local Path: {settings.LOCAL_STORAGE_PATH}")
    print(f"   Image Base URL: {settings.IMAGE_BASE_URL}")
    
    asyncio.run(test_storage())

