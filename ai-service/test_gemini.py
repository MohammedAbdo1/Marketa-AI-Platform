#!/usr/bin/env python3
"""
Test script for Google Gemini API
This script tests the connection to Google Gemini API using the API key from .env
"""

import os
import sys
from dotenv import load_dotenv

# Load environment variables
load_dotenv()

def test_gemini_api():
    """Test Google Gemini API connection"""
    print("🧪 Testing Google Gemini API...")
    print("=" * 50)
    
    # Check if API key exists
    api_key = os.getenv('GOOGLE_API_KEY')
    if not api_key:
        print("❌ ERROR: GOOGLE_API_KEY not found in environment variables")
        return False
    
    print(f"✅ API Key found: {api_key[:10]}...{api_key[-4:]}")
    
    try:
        # Import Google Generative AI
        import google.generativeai as genai
        
        # Configure the API
        genai.configure(api_key=api_key)
        
        # Test with a simple model
        print("📡 Testing API connection...")
        model = genai.GenerativeModel('gemini-2.0-flash')
        
        # Send a simple test prompt
        test_prompt = "Hello! Please respond with 'API connection successful' in Arabic."
        print(f"📝 Sending test prompt: {test_prompt}")
        
        response = model.generate_content(test_prompt)
        
        print("✅ SUCCESS! API Response:")
        print(f"📄 Response: {response.text}")
        print(f"📊 Response length: {len(response.text)} characters")
        
        return True
        
    except ImportError as e:
        print(f"❌ ERROR: Failed to import google.generativeai: {e}")
        print("💡 Try: pip install google-generativeai")
        return False
        
    except Exception as e:
        print(f"❌ ERROR: API call failed: {e}")
        print(f"🔍 Error type: {type(e).__name__}")
        return False

def test_model_availability():
    """Test if the specific model is available"""
    print("\n🔍 Testing model availability...")
    
    try:
        import google.generativeai as genai
        
        api_key = os.getenv('GOOGLE_API_KEY')
        genai.configure(api_key=api_key)
        
        # List available models
        models = genai.list_models()
        print(f"📋 Available models: {len(models)}")
        
        # Check for our specific model
        target_model = 'gemini-2.0-flash'
        model_found = False
        
        for model in models:
            if target_model in model.name:
                model_found = True
                print(f"✅ Found target model: {model.name}")
                break
        
        if not model_found:
            print(f"⚠️  Target model '{target_model}' not found")
            print("📋 Available model names:")
            for model in models:
                print(f"   - {model.name}")
        
        return model_found
        
    except Exception as e:
        print(f"❌ ERROR testing models: {e}")
        return False

if __name__ == "__main__":
    print("🚀 Google Gemini API Test")
    print("=" * 50)
    
    # Test API connection
    api_success = test_gemini_api()
    
    # Test model availability
    model_success = test_model_availability()
    
    print("\n" + "=" * 50)
    print("📊 TEST RESULTS:")
    print(f"🔑 API Connection: {'✅ PASS' if api_success else '❌ FAIL'}")
    print(f"🤖 Model Availability: {'✅ PASS' if model_success else '❌ FAIL'}")
    
    if api_success and model_success:
        print("\n🎉 All tests passed! Google Gemini API is working correctly.")
        sys.exit(0)
    else:
        print("\n💥 Some tests failed. Check the errors above.")
        sys.exit(1)

