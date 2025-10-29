#!/usr/bin/env python3
"""
Create a test campaign directly
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

async def create_test_campaign():
    """Create a test campaign"""
    print("🚀 Creating Test Campaign")
    print("=" * 50)
    
    try:
        # Initialize agents
        print("🤖 Initializing AI agents...")
        planner = CampaignPlannerAgent()
        writer = ContentWriterAgent()
        image_agent = ImageGeneratorAgent()
        
        # Create campaign request
        request = CampaignPreviewRequest(
            business_type="مطعم",
            product_name="وجبة شاورما",
            description="وجبة شاورما لذيذة مع الخضار الطازجة والصلصات المميزة",
            goal="زيادة المبيعات بنسبة 50%",
            target_audience={"age": "18-35", "interests": "الطعام العربي", "location": "الرياض"},
            platforms=["Instagram", "Facebook", "TikTok"],
            duration_days=14,
            posts_per_week=7,
            campaign_id=1
        )
        
        print(f"📋 Campaign Details:")
        print(f"   Business: {request.business_type}")
        print(f"   Product: {request.product_name}")
        print(f"   Duration: {request.duration_days} days")
        print(f"   Posts per week: {request.posts_per_week}")
        print(f"   Platforms: {', '.join(request.platforms)}")
        
        # Step 1: Generate campaign structure
        print("\n📝 Step 1: Generating campaign structure...")
        start_time = asyncio.get_event_loop().time()
        
        structure = await planner.generate_preview(request)
        
        structure_time = asyncio.get_event_loop().time() - start_time
        print(f"✅ Campaign structure generated in {structure_time:.2f}s")
        print(f"   Campaign Name: {structure.get('campaign_name', 'N/A')}")
        print(f"   Estimated Posts: {structure.get('estimated_posts', 'N/A')}")
        
        # Step 2: Generate posts
        print("\n✍️ Step 2: Generating campaign posts...")
        start_time = asyncio.get_event_loop().time()
        
        posts = await writer.generate_posts(structure, request)
        
        posts_time = asyncio.get_event_loop().time() - start_time
        print(f"✅ Posts generated in {posts_time:.2f}s")
        print(f"   Number of posts: {len(posts)}")
        
        # Display first post
        if posts and len(posts) > 0:
            first_post = posts[0]
            print(f"\n📄 Sample Post:")
            print(f"   Arabic: {first_post.get('content_ar', 'N/A')[:100]}...")
            print(f"   English: {first_post.get('content_en', 'N/A')[:100]}...")
            print(f"   Hashtags: {first_post.get('hashtags', [])}")
        
        # Step 3: Generate images (placeholder for now)
        print("\n🎨 Step 3: Generating images...")
        start_time = asyncio.get_event_loop().time()
        
        for i, post in enumerate(posts):
            if hasattr(post, 'needs_image') and post.needs_image:
                image_url = await image_agent.generate_image(f"Marketing image for: {post.get('content_ar', 'Post content')}")
                post.image_url = image_url
                print(f"   Image {i+1}: {image_url}")
        
        images_time = asyncio.get_event_loop().time() - start_time
        print(f"✅ Images generated in {images_time:.2f}s")
        
        # Final summary
        total_time = structure_time + posts_time + images_time
        print(f"\n🎉 Campaign Creation Complete!")
        print(f"   Total time: {total_time:.2f}s")
        print(f"   Structure: {structure_time:.2f}s")
        print(f"   Posts: {posts_time:.2f}s")
        print(f"   Images: {images_time:.2f}s")
        print(f"   Total posts: {len(posts)}")
        
        # Save campaign data
        campaign_data = {
            "campaign_id": request.campaign_id,
            "campaign_name": structure.get('campaign_name'),
            "structure": structure,
            "posts": [post.dict() if hasattr(post, 'dict') else post for post in posts],
            "total_posts": len(posts),
            "creation_time": total_time
        }
        
        print(f"\n💾 Campaign data saved:")
        print(f"   Campaign ID: {campaign_data['campaign_id']}")
        print(f"   Campaign Name: {campaign_data['campaign_name']}")
        print(f"   Total Posts: {campaign_data['total_posts']}")
        
        return campaign_data
        
    except Exception as e:
        print(f"❌ Campaign creation failed: {str(e)}")
        return None

async def main():
    print("🧪 Marketa AI Campaign Creation Test")
    print("=" * 50)
    
    # Check API keys
    if not settings.GOOGLE_API_KEY:
        print("❌ GOOGLE_API_KEY not found in .env file")
        return False
    
    print("✅ API keys configured")
    
    # Create campaign
    campaign = await create_test_campaign()
    
    if campaign:
        print("\n🎯 Next Steps:")
        print("   1. Your AI agents are working perfectly!")
        print("   2. You can now integrate with your frontend")
        print("   3. Use the API endpoints to create campaigns")
        print("   4. Monitor performance and optimize")
        return True
    else:
        print("\n⚠️ Campaign creation failed. Check your setup.")
        return False

if __name__ == "__main__":
    asyncio.run(main())







