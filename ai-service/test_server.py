"""Quick test to start simple server"""
import sys
import os
sys.path.append(os.path.dirname(os.path.abspath(__file__)))

print("\n[TEST] Importing modules...")
try:
    from app.main_simple import app
    print("[TEST] Import successful!")
    
    import uvicorn
    print("[TEST] Starting uvicorn...")
    
    uvicorn.run(app, host="0.0.0.0", port=8001, log_level="info")
    
except Exception as e:
    print(f"[ERROR] {e}")
    import traceback
    traceback.print_exc()

