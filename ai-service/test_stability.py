#!/usr/bin/env python3
"""
Test script for Stability AI API
This script tests the connection to Stability AI API for image generation
"""

import os
import sys
import requests
from dotenv import load_dotenv

# Load environment variables
load_dotenv()

def test_stability_api():
    """Test Stability AI API connection"""
    print("🧪 Testing Stability AI API...")
    print("=" * 50)
    
    # Check if API key exists
    api_key = os.getenv('STABILITY_API_KEY')
    if not api_key:
        print("❌ ERROR: STABILITY_API_KEY not found in environment variables")
        return False
    
    print(f"✅ API Key found: {api_key[:10]}...{api_key[-4:]}")
    
    try:
        # Test API connection with a simple request
        print("📡 Testing API connection...")
        
        # Stability AI API endpoint for text-to-image
        url = "https://api.stability.ai/v1/generation/stable-diffusion-v3/generate"
        
        headers = {
            "Authorization": f"Bearer {api_key}",
            "Content-Type": "application/json"
        }
        
        # Simple test payload
        payload = {
            "text_prompts": [
                {
                    "text": "A simple test image",
                    "weight": 1.0
                }
            ],
            "cfg_scale": 7,
            "height": 512,
            "width": 512,
            "samples": 1,
            "steps": 20
        }
        
        print("📝 Sending test request to Stability AI...")
        print(f"🔗 URL: {url}")
        
        # Make the request
        response = requests.post(url, headers=headers, json=payload, timeout=30)
        
        print(f"📊 Response Status: {response.status_code}")
        
        if response.status_code == 200:
            print("✅ SUCCESS! API Response received")
            
            # Parse response
            response_data = response.json()
            
            if 'artifacts' in response_data and len(response_data['artifacts']) > 0:
                artifact = response_data['artifacts'][0]
                print(f"🖼️  Image generated successfully!")
                print(f"📏 Image size: {artifact.get('width', 'unknown')}x{artifact.get('height', 'unknown')}")
                print(f"📊 Finish reason: {artifact.get('finishReason', 'unknown')}")
                return True
            else:
                print("⚠️  Response received but no image artifacts found")
                print(f"📄 Response: {response_data}")
                return False
                
        elif response.status_code == 401:
            print("❌ ERROR: Unauthorized - Invalid API key")
            return False
        elif response.status_code == 402:
            print("❌ ERROR: Payment required - API credits exhausted")
            return False
        elif response.status_code == 403:
            print("❌ ERROR: Forbidden - API key doesn't have required permissions")
            return False
        else:
            print(f"❌ ERROR: API request failed with status {response.status_code}")
            print(f"📄 Response: {response.text}")
            return False
            
    except requests.exceptions.Timeout:
        print("❌ ERROR: Request timed out")
        return False
    except requests.exceptions.ConnectionError:
        print("❌ ERROR: Connection error - check internet connection")
        return False
    except Exception as e:
        print(f"❌ ERROR: API call failed: {e}")
        print(f"🔍 Error type: {type(e).__name__}")
        return False

def test_api_balance():
    """Test API balance/credits"""
    print("\n💰 Testing API balance...")
    
    try:
        api_key = os.getenv('STABILITY_API_KEY')
        
        # Check balance endpoint
        url = "https://api.stability.ai/v1/user/balance"
        headers = {
            "Authorization": f"Bearer {api_key}"
        }
        
        response = requests.get(url, headers=headers, timeout=10)
        
        if response.status_code == 200:
            balance_data = response.json()
            print(f"✅ Balance check successful")
            print(f"💰 Credits: {balance_data}")
            return True
        else:
            print(f"⚠️  Balance check failed: {response.status_code}")
            return False
            
    except Exception as e:
        print(f"❌ ERROR checking balance: {e}")
        return False

if __name__ == "__main__":
    print("🚀 Stability AI API Test")
    print("=" * 50)
    
    # Test API connection
    api_success = test_stability_api()
    
    # Test API balance
    balance_success = test_api_balance()
    
    print("\n" + "=" * 50)
    print("📊 TEST RESULTS:")
    print(f"🔑 API Connection: {'✅ PASS' if api_success else '❌ FAIL'}")
    print(f"💰 Balance Check: {'✅ PASS' if balance_success else '❌ FAIL'}")
    
    if api_success:
        print("\n🎉 Stability AI API is working correctly!")
        sys.exit(0)
    else:
        print("\n💥 Stability AI API test failed. Check the errors above.")
        sys.exit(1)

