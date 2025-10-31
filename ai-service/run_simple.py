#!/usr/bin/env python3
"""
Marketa AI Service Runner - Simple Mode (No Redis/Celery)
"""
import uvicorn
from config import settings

if __name__ == "__main__":
    print("\n" + "="*60)
    print("Starting Marketa AI Service - SIMPLE MODE")
    print("="*60)
    print(f"Host: {settings.HOST}")
    print(f"Port: {settings.PORT}")
    print(f"Debug: {settings.DEBUG}")
    print("="*60 + "\n")
    
    uvicorn.run(
        "app.main_simple:app",
        host=settings.HOST,
        port=settings.PORT,
        reload=settings.DEBUG,
        log_level="info"
    )

