#!/usr/bin/env python3
"""
Test script for AI integrations (Gemini and Stability AI)
"""

import asyncio
import aiohttp
import json
import time
from typing import Dict, Any

class AITester:
    def __init__(self, base_url: str = "http://localhost:8001"):
        self.base_url = base_url
        self.session = None
    
    async def __aenter__(self):
        self.session = aiohttp.ClientSession()
        return self
    
    async def __aexit__(self, exc_type, exc_val, exc_tb):
        if self.session:
            await self.session.close()
    
    async def test_health(self) -> bool:
        """Test if the service is running"""
        try:
            async with self.session.get(f"{self.base_url}/health") as response:
                return response.status == 200
        except:
            return False
    
    async def test_gemini_integration(self) -> Dict[str, Any]:
        """Test Gemini AI integration"""
        print("🧠 Testing Gemini AI integration...")
        
        test_data = {
            "business_type": "مطعم",
            "product_name": "وجبة شاورما",
            "description": "وجبة شاورما لذيذة ومشبعة مع الخضار الطازجة",
            "goal": "زيادة المبيعات",
            "target_audience": "الشباب والطلاب",
            "platforms": ["Instagram", "Facebook"],
            "duration_days": 7,
            "posts_per_week": 5,
            "campaign_id": "test_gemini_campaign"
        }
        
        start_time = time.time()
        
        try:
            # Start campaign preview
            async with self.session.post(
                f"{self.base_url}/api/campaign/preview",
                json=test_data
            ) as response:
                if response.status != 200:
                    return {
                        "success": False,
                        "error": f"Preview request failed: {response.status}",
                        "duration": time.time() - start_time
                    }
                
                result = await response.json()
                task_id = result.get("task_id")
                
                if not task_id:
                    return {
                        "success": False,
                        "error": "No task ID returned",
                        "duration": time.time() - start_time
                    }
                
                # Wait for task completion
                max_wait = 60  # 60 seconds timeout
                wait_time = 0
                
                while wait_time < max_wait:
                    await asyncio.sleep(2)
                    wait_time += 2
                    
                    # Check task status
                    async with self.session.get(f"{self.base_url}/api/task/status/{task_id}") as status_response:
                        if status_response.status == 200:
                            status_data = await status_response.json()
                            
                            if status_data.get("status") == "completed":
                                # Get result
                                async with self.session.get(f"{self.base_url}/api/task/result/{task_id}") as result_response:
                                    if result_response.status == 200:
                                        result_data = await result_response.json()
                                        return {
                                            "success": True,
                                            "duration": time.time() - start_time,
                                            "task_id": task_id,
                                            "result": result_data.get("result", {}),
                                            "message": "Gemini AI integration working!"
                                        }
                            
                            elif status_data.get("status") == "failed":
                                return {
                                    "success": False,
                                    "error": f"Task failed: {status_data.get('message', 'Unknown error')}",
                                    "duration": time.time() - start_time
                                }
                
                return {
                    "success": False,
                    "error": "Task timeout - Gemini may not be configured properly",
                    "duration": time.time() - start_time
                }
                
        except Exception as e:
            return {
                "success": False,
                "error": f"Exception: {str(e)}",
                "duration": time.time() - start_time
            }
    
    async def test_stability_integration(self) -> Dict[str, Any]:
        """Test Stability AI integration"""
        print("🎨 Testing Stability AI integration...")
        
        test_data = {
            "post_id": "test_post_123",
            "image_prompt": "A delicious shawarma meal with fresh vegetables, professional food photography, high quality",
            "content_ar": "وجبة شاورما لذيذة",
            "content_en": "Delicious shawarma meal"
        }
        
        start_time = time.time()
        
        try:
            # Test image regeneration
            async with self.session.post(
                f"{self.base_url}/api/post/regenerate-image",
                json=test_data
            ) as response:
                if response.status != 200:
                    return {
                        "success": False,
                        "error": f"Image request failed: {response.status}",
                        "duration": time.time() - start_time
                    }
                
                result = await response.json()
                task_id = result.get("task_id")
                
                if not task_id:
                    return {
                        "success": False,
                        "error": "No task ID returned for image generation",
                        "duration": time.time() - start_time
                    }
                
                # Wait for task completion
                max_wait = 30  # 30 seconds timeout for image generation
                wait_time = 0
                
                while wait_time < max_wait:
                    await asyncio.sleep(2)
                    wait_time += 2
                    
                    # Check task status
                    async with self.session.get(f"{self.base_url}/api/task/status/{task_id}") as status_response:
                        if status_response.status == 200:
                            status_data = await status_response.json()
                            
                            if status_data.get("status") == "completed":
                                # Get result
                                async with self.session.get(f"{self.base_url}/api/task/result/{task_id}") as result_response:
                                    if result_response.status == 200:
                                        result_data = await result_response.json()
                                        return {
                                            "success": True,
                                            "duration": time.time() - start_time,
                                            "task_id": task_id,
                                            "image_url": result_data.get("result", {}).get("image_url"),
                                            "message": "Stability AI integration working!"
                                        }
                            
                            elif status_data.get("status") == "failed":
                                return {
                                    "success": False,
                                    "error": f"Image generation failed: {status_data.get('message', 'Unknown error')}",
                                    "duration": time.time() - start_time
                                }
                
                return {
                    "success": False,
                    "error": "Image generation timeout - Stability AI may not be configured properly",
                    "duration": time.time() - start_time
                }
                
        except Exception as e:
            return {
                "success": False,
                "error": f"Exception: {str(e)}",
                "duration": time.time() - start_time
            }
    
    async def test_color_suggestion(self) -> Dict[str, Any]:
        """Test color suggestion feature"""
        print("🎨 Testing color suggestion...")
        
        test_data = {
            "description": "مطعم شاورما عربي مع ألوان دافئة ومريحة للعين"
        }
        
        start_time = time.time()
        
        try:
            async with self.session.post(
                f"{self.base_url}/api/brand/suggest-colors",
                json=test_data
            ) as response:
                if response.status != 200:
                    return {
                        "success": False,
                        "error": f"Color suggestion failed: {response.status}",
                        "duration": time.time() - start_time
                    }
                
                result = await response.json()
                task_id = result.get("task_id")
                
                if not task_id:
                    return {
                        "success": False,
                        "error": "No task ID returned for color suggestion",
                        "duration": time.time() - start_time
                    }
                
                # Wait for task completion
                max_wait = 20  # 20 seconds timeout
                wait_time = 0
                
                while wait_time < max_wait:
                    await asyncio.sleep(2)
                    wait_time += 2
                    
                    # Check task status
                    async with self.session.get(f"{self.base_url}/api/task/status/{task_id}") as status_response:
                        if status_response.status == 200:
                            status_data = await status_response.json()
                            
                            if status_data.get("status") == "completed":
                                # Get result
                                async with self.session.get(f"{self.base_url}/api/task/result/{task_id}") as result_response:
                                    if result_response.status == 200:
                                        result_data = await result_response.json()
                                        return {
                                            "success": True,
                                            "duration": time.time() - start_time,
                                            "task_id": task_id,
                                            "colors": result_data.get("result", {}),
                                            "message": "Color suggestion working!"
                                        }
                            
                            elif status_data.get("status") == "failed":
                                return {
                                    "success": False,
                                    "error": f"Color suggestion failed: {status_data.get('message', 'Unknown error')}",
                                    "duration": time.time() - start_time
                                }
                
                return {
                    "success": False,
                    "error": "Color suggestion timeout",
                    "duration": time.time() - start_time
                }
                
        except Exception as e:
            return {
                "success": False,
                "error": f"Exception: {str(e)}",
                "duration": time.time() - start_time
            }

async def main():
    print("🧪 Marketa AI Integration Test")
    print("=" * 50)
    
    async with AITester() as tester:
        # Check if service is running
        if not await tester.test_health():
            print("❌ Service is not running. Please start the services first:")
            print("   python ai-service/start_services.py")
            return False
        
        print("✅ Service is running")
        
        # Test Gemini integration
        print("\n" + "="*50)
        gemini_result = await tester.test_gemini_integration()
        if gemini_result["success"]:
            print(f"✅ Gemini AI: {gemini_result['message']}")
            print(f"   Duration: {gemini_result['duration']:.2f}s")
        else:
            print(f"❌ Gemini AI: {gemini_result['error']}")
            print("   Make sure GOOGLE_API_KEY is set in your .env file")
        
        # Test Stability AI integration
        print("\n" + "="*50)
        stability_result = await tester.test_stability_integration()
        if stability_result["success"]:
            print(f"✅ Stability AI: {stability_result['message']}")
            print(f"   Duration: {stability_result['duration']:.2f}s")
            if stability_result.get("image_url"):
                print(f"   Image URL: {stability_result['image_url']}")
        else:
            print(f"❌ Stability AI: {stability_result['error']}")
            print("   Make sure STABILITY_API_KEY is set in your .env file")
        
        # Test color suggestion
        print("\n" + "="*50)
        color_result = await tester.test_color_suggestion()
        if color_result["success"]:
            print(f"✅ Color Suggestion: {color_result['message']}")
            print(f"   Duration: {color_result['duration']:.2f}s")
        else:
            print(f"❌ Color Suggestion: {color_result['error']}")
        
        # Summary
        print("\n" + "="*50)
        print("📊 Test Summary:")
        print(f"   Gemini AI: {'✅ Working' if gemini_result['success'] else '❌ Failed'}")
        print(f"   Stability AI: {'✅ Working' if stability_result['success'] else '❌ Failed'}")
        print(f"   Color Suggestion: {'✅ Working' if color_result['success'] else '❌ Failed'}")
        
        all_working = all([
            gemini_result["success"],
            stability_result["success"],
            color_result["success"]
        ])
        
        if all_working:
            print("\n🎉 All AI integrations are working perfectly!")
            print("   You can now start creating campaigns!")
        else:
            print("\n⚠️  Some integrations failed. Check your API keys in .env file")
            print("   Required keys: GOOGLE_API_KEY, STABILITY_API_KEY")
        
        return all_working

if __name__ == "__main__":
    asyncio.run(main())
