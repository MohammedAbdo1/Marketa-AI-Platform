#!/usr/bin/env python3
"""
Test script for Redis and Celery
This script tests the connection to Redis and Celery workers
"""

import os
import sys
import redis
from dotenv import load_dotenv

# Load environment variables
load_dotenv()

def test_redis_connection():
    """Test Redis connection"""
    print("🧪 Testing Redis Connection...")
    print("=" * 50)
    
    try:
        # Get Redis URL from environment
        redis_url = os.getenv('REDIS_URL', 'redis://localhost:6379/0')
        print(f"🔗 Redis URL: {redis_url}")
        
        # Connect to Redis
        r = redis.from_url(redis_url)
        
        # Test connection
        print("📡 Testing Redis connection...")
        r.ping()
        print("✅ Redis connection successful!")
        
        # Test basic operations
        print("📝 Testing Redis operations...")
        
        # Set a test key
        test_key = "test:ai_service:connection"
        test_value = "AI Service Test"
        r.set(test_key, test_value, ex=60)  # Expire in 60 seconds
        
        # Get the test key
        retrieved_value = r.get(test_key)
        if retrieved_value and retrieved_value.decode() == test_value:
            print("✅ Redis read/write operations successful!")
        else:
            print("❌ Redis read/write operations failed!")
            return False
        
        # Clean up
        r.delete(test_key)
        print("🧹 Test key cleaned up")
        
        # Get Redis info
        info = r.info()
        print(f"📊 Redis version: {info.get('redis_version', 'unknown')}")
        print(f"📊 Used memory: {info.get('used_memory_human', 'unknown')}")
        print(f"📊 Connected clients: {info.get('connected_clients', 'unknown')}")
        
        return True
        
    except redis.ConnectionError as e:
        print(f"❌ ERROR: Redis connection failed: {e}")
        return False
    except Exception as e:
        print(f"❌ ERROR: Redis test failed: {e}")
        print(f"🔍 Error type: {type(e).__name__}")
        return False

def test_celery_connection():
    """Test Celery connection"""
    print("\n🧪 Testing Celery Connection...")
    print("=" * 50)
    
    try:
        # Import Celery app
        from celery_app import celery_app
        
        print("📡 Testing Celery connection...")
        
        # Check Celery configuration
        broker_url = celery_app.conf.broker_url
        result_backend = celery_app.conf.result_backend
        
        print(f"🔗 Broker URL: {broker_url}")
        print(f"🔗 Result Backend: {result_backend}")
        
        # Test Celery control
        print("📊 Checking Celery workers...")
        stats = celery_app.control.stats()
        
        if stats:
            print(f"✅ Found {len(stats)} active workers:")
            for worker_name, worker_stats in stats.items():
                print(f"   - {worker_name}")
                print(f"     Pool: {worker_stats.get('pool', {}).get('max-concurrency', 'unknown')}")
                print(f"     Tasks: {len(worker_stats.get('total', {}))}")
        else:
            print("⚠️  No active workers found")
            return False
        
        # Test a simple task
        print("📝 Testing Celery task execution...")
        
        # Create a simple test task
        @celery_app.task
        def test_task():
            return "Celery test successful!"
        
        # Send the task
        result = test_task.delay()
        print(f"📤 Task sent with ID: {result.id}")
        
        # Wait for result (with timeout)
        try:
            task_result = result.get(timeout=10)
            print(f"✅ Task completed successfully: {task_result}")
            
            # Clean up
            result.forget()
            print("🧹 Task result cleaned up")
            
            return True
            
        except Exception as e:
            print(f"❌ Task execution failed: {e}")
            return False
        
    except ImportError as e:
        print(f"❌ ERROR: Failed to import Celery app: {e}")
        return False
    except Exception as e:
        print(f"❌ ERROR: Celery test failed: {e}")
        print(f"🔍 Error type: {type(e).__name__}")
        return False

def test_celery_tasks():
    """Test specific Celery tasks from the app"""
    print("\n🧪 Testing AI Service Celery Tasks...")
    print("=" * 50)
    
    try:
        from app.tasks.campaign_tasks import generate_preview_task
        
        print("📡 Testing campaign preview task...")
        
        # Test data
        test_data = {
            "business_type": "restaurant",
            "product_name": "Test Restaurant",
            "description": "A test restaurant for AI service testing",
            "campaign_goal": "increase awareness",
            "target_audience": ["young adults", "families"],
            "platforms": ["instagram", "facebook"],
            "duration_weeks": 2,
            "posts_per_week": 3,
            "mode": "quick"
        }
        
        # Send the task
        result = generate_preview_task.delay(test_data)
        print(f"📤 Campaign preview task sent with ID: {result.id}")
        
        # Wait for result (with timeout)
        try:
            task_result = result.get(timeout=30)
            print(f"✅ Campaign preview task completed successfully!")
            print(f"📄 Result type: {type(task_result)}")
            
            if isinstance(task_result, dict):
                print(f"📊 Result keys: {list(task_result.keys())}")
            
            # Clean up
            result.forget()
            print("🧹 Task result cleaned up")
            
            return True
            
        except Exception as e:
            print(f"❌ Campaign preview task failed: {e}")
            return False
        
    except ImportError as e:
        print(f"❌ ERROR: Failed to import campaign tasks: {e}")
        return False
    except Exception as e:
        print(f"❌ ERROR: Campaign task test failed: {e}")
        print(f"🔍 Error type: {type(e).__name__}")
        return False

if __name__ == "__main__":
    print("🚀 Redis & Celery Test")
    print("=" * 50)
    
    # Test Redis connection
    redis_success = test_redis_connection()
    
    # Test Celery connection
    celery_success = test_celery_connection()
    
    # Test specific tasks
    tasks_success = test_celery_tasks()
    
    print("\n" + "=" * 50)
    print("📊 TEST RESULTS:")
    print(f"🔑 Redis Connection: {'✅ PASS' if redis_success else '❌ FAIL'}")
    print(f"⚙️  Celery Connection: {'✅ PASS' if celery_success else '❌ FAIL'}")
    print(f"📝 Celery Tasks: {'✅ PASS' if tasks_success else '❌ FAIL'}")
    
    if redis_success and celery_success:
        print("\n🎉 Redis and Celery are working correctly!")
        if tasks_success:
            print("🎉 AI Service tasks are also working!")
        else:
            print("⚠️  AI Service tasks need attention")
        sys.exit(0)
    else:
        print("\n💥 Redis or Celery tests failed. Check the errors above.")
        sys.exit(1)

