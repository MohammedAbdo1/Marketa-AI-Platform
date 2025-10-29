#!/usr/bin/env python3
"""
Check available Gemini models
"""

import google.generativeai as genai
from config import settings

def check_models():
    """Check available Gemini models"""
    print("🔍 Checking available Gemini models...")
    
    try:
        genai.configure(api_key=settings.GOOGLE_API_KEY)
        
        # List available models
        models = genai.list_models()
        
        print("📋 Available models:")
        for model in models:
            if 'generateContent' in model.supported_generation_methods:
                print(f"   ✅ {model.name}")
            else:
                print(f"   ❌ {model.name} (no generateContent)")
        
        # Try to use a basic model
        print("\n🧪 Testing with gemini-pro...")
        model = genai.GenerativeModel('gemini-pro')
        response = model.generate_content("Hello, test message")
        print(f"✅ gemini-pro works: {response.text[:50]}...")
        
        return True
        
    except Exception as e:
        print(f"❌ Error: {str(e)}")
        return False

if __name__ == "__main__":
    check_models()







