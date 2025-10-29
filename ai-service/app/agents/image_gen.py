import google.generativeai as genai
from config import settings
from app.services.cache_service import cache_service
import asyncio

class ImageGeneratorAgent:
    def __init__(self):
        if settings.GOOGLE_API_KEY:
            genai.configure(api_key=settings.GOOGLE_API_KEY)
            self.model = genai.GenerativeModel(
                settings.TEXT_MODEL,
                generation_config=genai.types.GenerationConfig(
                    temperature=0.3,  # Lower temperature for image generation
                    max_output_tokens=500,  # Shorter for image prompts
                    candidate_count=1
                )
            )
        else:
            self.model = None
    
    async def generate_image(self, prompt):
        """Generate image for a post"""
        if not self.model:
            return "https://via.placeholder.com/400x400/FF6B6B/FFFFFF?text=Image+Not+Available"
        
        # Create cache key from prompt
        cache_key = f"image:{hash(prompt)}"
        
        # Check cache first
        cached_result = cache_service.get_cached_ai_result(cache_key, "image")
        if cached_result:
            return cached_result
        
        try:
            # Add timeout to the request
            await asyncio.wait_for(
                asyncio.sleep(0.1),  # Simulate processing time
                timeout=settings.AI_REQUEST_TIMEOUT
            )
            
            # For now, return a placeholder
            # In production, this would integrate with DALL-E or Stability AI
            result = "https://via.placeholder.com/400x400/4ECDC4/FFFFFF?text=AI+Generated"
            
            # Cache the result
            cache_service.set_cached_ai_result(cache_key, result, "image", settings.CACHE_TTL)
            
            return result
        except asyncio.TimeoutError:
            return "https://via.placeholder.com/400x400/FF6B6B/FFFFFF?text=Timeout+Error"
        except Exception as e:
            return f"https://via.placeholder.com/400x400/FF6B6B/FFFFFF?text=Error+{str(e)[:20]}"
    
    async def generate_image_for_post(self, post_data):
        """Generate image based on post content"""
        if not self.model:
            return {
                "image_url": "https://via.placeholder.com/400x400/FF6B6B/FFFFFF?text=Image+Not+Available",
                "image_prompt": "Placeholder image",
                "cost": 0.0,
                "tokens": 0
            }
        
        # Create cache key from post data
        cache_key = f"image_post:{hash(str(post_data))}"
        
        # Check cache first
        cached_result = cache_service.get_cached_ai_result(cache_key, "image_post")
        if cached_result:
            return cached_result
        
        try:
            # Add timeout to the request
            await asyncio.wait_for(
                asyncio.sleep(0.1),  # Simulate processing time
                timeout=settings.AI_REQUEST_TIMEOUT
            )
            
            # Generate image prompt based on post content
            image_prompt = f"Marketing image for: {post_data.get('content_ar', 'Post content')}"
            
            result = {
                "image_url": "https://via.placeholder.com/400x400/4ECDC4/FFFFFF?text=AI+Generated",
                "image_prompt": image_prompt,
                "cost": 0.1,
                "tokens": 50
            }
            
            # Cache the result
            cache_service.set_cached_ai_result(cache_key, result, "image_post", settings.CACHE_TTL)
            
            return result
        except asyncio.TimeoutError:
            return {
                "image_url": "https://via.placeholder.com/400x400/FF6B6B/FFFFFF?text=Timeout+Error",
                "image_prompt": "Timeout error",
                "cost": 0.0,
                "tokens": 0
            }
        except Exception as e:
            return {
                "image_url": f"https://via.placeholder.com/400x400/FF6B6B/FFFFFF?text=Error+{str(e)[:20]}",
                "image_prompt": f"Error: {str(e)}",
                "cost": 0.0,
                "tokens": 0
            }