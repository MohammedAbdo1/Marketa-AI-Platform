#!/usr/bin/env python3
"""
Script to start all Marketa AI services
"""

import subprocess
import sys
import time
import requests
import os
from pathlib import Path

def run_command(command, description):
    """Run a command and handle errors"""
    print(f"🔄 {description}...")
    try:
        result = subprocess.run(command, shell=True, check=True, capture_output=True, text=True)
        print(f"✅ {description} completed successfully")
        return True
    except subprocess.CalledProcessError as e:
        print(f"❌ {description} failed: {e.stderr}")
        return False

def check_service_health(url, service_name, timeout=30):
    """Check if a service is healthy"""
    print(f"🔍 Checking {service_name} health...")
    start_time = time.time()
    
    while time.time() - start_time < timeout:
        try:
            response = requests.get(url, timeout=5)
            if response.status_code == 200:
                print(f"✅ {service_name} is healthy")
                return True
        except requests.exceptions.RequestException:
            pass
        
        time.sleep(2)
    
    print(f"❌ {service_name} health check failed")
    return False

def main():
    print("🚀 Starting Marketa AI Services")
    print("=" * 50)
    
    # Check if .env file exists
    env_file = Path("ai-service/.env")
    if not env_file.exists():
        print("⚠️  .env file not found. Please copy env.example to .env and configure your API keys.")
        print("   cp ai-service/env.example ai-service/.env")
        return False
    
    # Start Redis
    print("\n📦 Starting Redis...")
    if not run_command("docker run -d --name marketa_redis -p 6379:6379 redis:7-alpine", "Starting Redis"):
        print("Trying to start existing Redis container...")
        run_command("docker start marketa_redis", "Starting existing Redis")
    
    # Wait for Redis to be ready
    time.sleep(5)
    
    # Start Celery Worker
    print("\n👷 Starting Celery Worker...")
    celery_cmd = "cd ai-service && celery -A celery_app worker --loglevel=info --concurrency=4"
    if sys.platform == "win32":
        celery_cmd = f"start /B {celery_cmd}"
    else:
        celery_cmd = f"nohup {celery_cmd} > celery.log 2>&1 &"
    
    run_command(celery_cmd, "Starting Celery Worker")
    
    # Start FastAPI
    print("\n🌐 Starting FastAPI...")
    api_cmd = "cd ai-service && python -m uvicorn app.main:app --host 0.0.0.0 --port 8001 --reload"
    if sys.platform == "win32":
        api_cmd = f"start /B {api_cmd}"
    else:
        api_cmd = f"nohup {api_cmd} > api.log 2>&1 &"
    
    run_command(api_cmd, "Starting FastAPI")
    
    # Wait for services to start
    print("\n⏳ Waiting for services to start...")
    time.sleep(10)
    
    # Health checks
    print("\n🏥 Running health checks...")
    
    # Check FastAPI
    if check_service_health("http://localhost:8001/health", "FastAPI"):
        print("\n🎉 All services started successfully!")
        print("\n📊 Service URLs:")
        print("   • FastAPI: http://localhost:8001")
        print("   • API Docs: http://localhost:8001/docs")
        print("   • Health: http://localhost:8001/health")
        print("   • Redis: localhost:6379")
        
        print("\n🧪 Test your setup:")
        print("   • Run performance test: python ai-service/test_performance.py")
        print("   • Check logs: tail -f ai-service/api.log")
        print("   • Monitor Celery: tail -f ai-service/celery.log")
        
        return True
    else:
        print("\n❌ Services failed to start properly")
        print("Check the logs for more details:")
        print("   • API logs: cat ai-service/api.log")
        print("   • Celery logs: cat ai-service/celery.log")
        return False

if __name__ == "__main__":
    success = main()
    sys.exit(0 if success else 1)
