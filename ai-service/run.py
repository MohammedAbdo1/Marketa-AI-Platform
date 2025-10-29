#!/usr/bin/env python3
"""
Marketa AI Service Runner
"""
import uvicorn
from config import settings

if __name__ == "__main__":
    print("🚀 Starting Marketa AI Service...")
    print(f"📍 Host: {settings.HOST}")
    print(f"🔌 Port: {settings.PORT}")
    print(f"🐛 Debug: {settings.DEBUG}")
    
    uvicorn.run(
        "app.main:app",
        host=settings.HOST,
        port=settings.PORT,
        reload=settings.DEBUG,
        log_level="info"
    )
