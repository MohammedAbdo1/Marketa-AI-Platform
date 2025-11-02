#!/usr/bin/env python3
"""
Test script for flexible image providers system
"""
import asyncio
import sys
import os

# Add parent directory to path
sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))

from app.agents.image_gen import ImageGeneratorAgent
from config import settings

async def test_providers():
    """Test the flexible provider system"""
    print("\n" + "="*60)
    print("🧪 Testing Flexible Image Providers System")
    print("="*60)
    
    # Initialize agent
    agent = ImageGeneratorAgent()
    
    print(f"\n📊 Configuration:")
    print(f"   Priority: {settings.IMAGE_PROVIDERS_PRIORITY}")
    print(f"   Pollinations: {'✅ Enabled' if settings.ENABLE_POLLINATIONS else '❌ Disabled'}")
    print(f"   HuggingFace: {'✅ Enabled' if settings.ENABLE_HUGGINGFACE else '❌ Disabled'}")
    print(f"   OpenAI: {'✅ Enabled' if settings.ENABLE_OPENAI else '❌ Disabled'}")
    print(f"   Stability: {'✅ Enabled' if settings.ENABLE_STABILITY else '❌ Disabled'}")
    
    print(f"\n🎨 Active Providers: {agent.provider_names}")
    
    if not agent.providers:
        print("\n❌ No providers available! Please enable at least one provider.")
        return
    
    # Test 1: Simple Arabic prompt
    print("\n" + "-"*60)
    print("Test 1: Arabic Restaurant Image")
    print("-"*60)
    
    try:
        prompt = "صورة احترافية لمطعم فاخر، أجواء راقية، إضاءة دافئة، طاولات أنيقة"
        print(f"📝 Prompt: {prompt}")
        
        url = await agent.generate_image(prompt, size="1024x1024")
        print(f"✅ Success! URL: {url}")
        
    except Exception as e:
        print(f"❌ Failed: {str(e)}")
    
    # Test 2: English prompt with specific size
    print("\n" + "-"*60)
    print("Test 2: Social Media Post Image")
    print("-"*60)
    
    try:
        prompt = "Beautiful modern coffee shop interior, warm lighting, cozy atmosphere"
        print(f"📝 Prompt: {prompt}")
        print(f"📐 Size: 1216x640 (Facebook/Instagram)")
        
        url = await agent.generate_image(prompt, size="1216x640")
        print(f"✅ Success! URL: {url}")
        
    except Exception as e:
        print(f"❌ Failed: {str(e)}")
    
    print("\n" + "="*60)
    print("✅ Testing completed!")
    print("="*60 + "\n")

if __name__ == "__main__":
    asyncio.run(test_providers())

