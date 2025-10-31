#!/usr/bin/env python3
"""
Marketa AI Service Runner
"""
import uvicorn
from config import settings
import os

if __name__ == "__main__":
    print("[AI Service] Starting Marketa AI Service...")
    print(f"[AI Service] Host: {settings.HOST}")
    print(f"[AI Service] Port: {settings.PORT}")
    print(f"[AI Service] Debug: {settings.DEBUG}")
    
    # Use simple mode if Redis is not available or USE_SIMPLE_AI is set
    use_simple = os.getenv("USE_SIMPLE_AI", "true").lower() == "true"
    
    if use_simple:
        print("[AI Service] Mode: SIMPLE (no Redis/Celery required)")
        app_module = "app.main_simple:app"
    else:
        print("[AI Service] Mode: FULL (with Redis/Celery)")
        app_module = "app.main:app"
    
    uvicorn.run(
        app_module,
        host=settings.HOST,
        port=settings.PORT,
        reload=settings.DEBUG,
        log_level="info"
    )
