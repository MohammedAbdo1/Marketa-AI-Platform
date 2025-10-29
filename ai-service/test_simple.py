#!/usr/bin/env python3
"""
Simple test without Redis - Direct AI testing
"""

import asyncio
import sys
import os
sys.path.append(os.path.dirname(os.path.abspath(__file__)))

from config import settings
from app.agents.planner import CampaignPlannerAgent
from app.agents.writer import ContentWriterAgent
from app.agents.image_gen import ImageGeneratorAgent
from app.models.campaign import CampaignPreviewRequest

async def test_gemini_direct():
    """Test Gemini AI directly without Redis"""
    print("🧠 Testing Gemini AI directly...")
    
    try:
        # Test planner agent
        planner = CampaignPlannerAgent()
        
        # Create test request
        request = CampaignPreviewRequest(
            business_type="مطعم",
            product_name="وجبة شاورما",
            description="وجبة شاورما لذيذة مع الخضار الطازجة",
            goal="زيادة المبيعات",
            target_audience={"age": "18-35", "interests": "الطعام", "location": "السعودية"},
            platforms=["Instagram", "Facebook"],
            duration_days=7,
            posts_per_week=5,
            campaign_id=123
        )
        
        print("📝 Generating campaign preview...")
        start_time = asyncio.get_event_loop().time()
        
        result = await planner.generate_preview(request)
        
        duration = asyncio.get_event_loop().time() - start_time
        
        if "error" in result:
            print(f"❌ Gemini AI Error: {result['error']}")
            return False
        else:
            print(f"✅ Gemini AI Success!")
            print(f"   Duration: {duration:.2f}s")
            print(f"   Campaign: {result.get('campaign_name', 'N/A')}")
            print(f"   Posts: {result.get('estimated_posts', 'N/A')}")
            return True
            
    except Exception as e:
        print(f"❌ Gemini AI Exception: {str(e)}")
        return False

async def test_stability_direct():
    """Test Stability AI directly"""
    print("\n🎨 Testing Stability AI directly...")
    
    try:
        image_agent = ImageGeneratorAgent()
        
        print("🖼️ Generating test image...")
        start_time = asyncio.get_event_loop().time()
        
        image_url = await image_agent.generate_image("A delicious shawarma meal with fresh vegetables")
        
        duration = asyncio.get_event_loop().time() - start_time
        
        if "placeholder" in image_url or "error" in image_url.lower():
            print(f"⚠️ Stability AI: Using placeholder (may not be configured)")
            print(f"   Image URL: {image_url}")
            return False
        else:
            print(f"✅ Stability AI Success!")
            print(f"   Duration: {duration:.2f}s")
            print(f"   Image URL: {image_url}")
            return True
            
    except Exception as e:
        print(f"❌ Stability AI Exception: {str(e)}")
        return False

async def test_writer_direct():
    """Test Content Writer directly"""
    print("\n✍️ Testing Content Writer directly...")
    
    try:
        writer = ContentWriterAgent()
        
        # Create test structure
        structure = {
            "campaign_name": "حملة شاورما",
            "structure": "هيكل تجريبي",
            "estimated_posts": 5
        }
        
        request = CampaignPreviewRequest(
            business_type="مطعم",
            product_name="وجبة شاورما",
            description="وجبة شاورما لذيذة",
            goal="زيادة المبيعات",
            target_audience={"age": "18-35", "interests": "الطعام"},
            platforms=["Instagram"],
            duration_days=7,
            posts_per_week=5,
            campaign_id=456
        )
        
        print("📝 Generating posts...")
        start_time = asyncio.get_event_loop().time()
        
        posts = await writer.generate_posts(structure, request)
        
        duration = asyncio.get_event_loop().time() - start_time
        
        if posts and not any("error" in str(post) for post in posts):
            print(f"✅ Content Writer Success!")
            print(f"   Duration: {duration:.2f}s")
            print(f"   Posts generated: {len(posts)}")
            return True
        else:
            print(f"❌ Content Writer Error: {posts}")
            return False
            
    except Exception as e:
        print(f"❌ Content Writer Exception: {str(e)}")
        return False

async def main():
    print("🧪 Marketa AI Direct Test (Without Redis)")
    print("=" * 50)
    
    # Check API keys
    print("🔑 Checking API keys...")
    if not settings.GOOGLE_API_KEY:
        print("❌ GOOGLE_API_KEY not found")
        return False
    else:
        print("✅ GOOGLE_API_KEY found")
    
    if not settings.STABILITY_API_KEY:
        print("⚠️ STABILITY_API_KEY not found (will use placeholders)")
    else:
        print("✅ STABILITY_API_KEY found")
    
    print("\n" + "="*50)
    
    # Test Gemini
    gemini_success = await test_gemini_direct()
    
    # Test Stability
    stability_success = await test_stability_direct()
    
    # Test Writer
    writer_success = await test_writer_direct()
    
    # Summary
    print("\n" + "="*50)
    print("📊 Test Summary:")
    print(f"   Gemini AI: {'✅ Working' if gemini_success else '❌ Failed'}")
    print(f"   Stability AI: {'✅ Working' if stability_success else '⚠️ Placeholder'}")
    print(f"   Content Writer: {'✅ Working' if writer_success else '❌ Failed'}")
    
    if gemini_success and writer_success:
        print("\n🎉 Core AI features are working!")
        print("   You can now create campaigns using the API endpoints.")
        print("\n📡 To start the full service:")
        print("   1. Install Redis server")
        print("   2. Run: python start_services.py")
        print("   3. Or use Docker: docker-compose up -d")
        return True
    else:
        print("\n⚠️ Some features failed. Check your API keys in .env file")
        return False

if __name__ == "__main__":
    asyncio.run(main())
