import google.generativeai as genai
from config import settings
from app.services.cache_service import cache_service
from app.services.stability import StabilityAIService
import asyncio
import os
import uuid
import base64
import logging

class ImageGeneratorAgent:
    def __init__(self):
        self.stability = None
        if settings.STABILITY_API_KEY:
            try:
                self.stability = StabilityAIService()
            except Exception:
                self.stability = None
        self.logger = logging.getLogger("uvicorn.error")
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
    
    async def generate_image(self, prompt, size: str = "1024x1024"):
        """Generate image for a post"""
        # Prefer Stability if key is available
        if self.stability:
            try:
                self.logger.info({"stage": "py_image_provider_start", "provider": "stability", "size": size})
                data_url = await self.stability.generate_image(prompt, style=settings.IMAGE_GEN_STYLE, size=size)
                # data:image/png;base64,.... -> save to static and return URL
                header, b64 = data_url.split(",", 1)
                image_bytes = base64.b64decode(b64)
                images_dir = os.path.join("app", "static", "images")
                os.makedirs(images_dir, exist_ok=True)
                filename = f"{uuid.uuid4().hex}.png"
                filepath = os.path.join(images_dir, filename)
                with open(filepath, "wb") as f:
                    f.write(image_bytes)
                self.logger.info({"stage": "py_image_provider_saved", "provider": "stability", "path": filepath})
                return f"{settings.IMAGE_BASE_URL}/static/images/{filename}"
            except Exception as e:
                self.logger.exception({"stage": "py_image_provider_error", "provider": "stability", "error": str(e)})
                raise RuntimeError(f"Stability error: {str(e)}")

        if not self.model:
            raise RuntimeError("No image provider available")
        
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
            raise RuntimeError("Text model image generation not implemented")
        except asyncio.TimeoutError:
            raise RuntimeError("Image provider timeout")
        except Exception as e:
            raise
    
    async def generate_image_for_post(self, post_data):
        """Generate image based on post content"""
        if not self.model:
            raise RuntimeError("No image provider available for post")
        
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
            
            raise RuntimeError("Text model image generation not implemented")
        except asyncio.TimeoutError:
            raise RuntimeError("Image provider timeout")
        except Exception as e:
            raise