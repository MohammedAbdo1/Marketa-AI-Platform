import google.generativeai as genai
from config import settings
from app.services.cache_service import cache_service
from app.services.stability import StabilityAIService
from app.services.image_compositor import ImageCompositor
from app.services.storage import storage
import asyncio
import os
import uuid
import base64
import logging
import requests

class ImageGeneratorAgent:
    def __init__(self):
        self.stability = None
        if settings.STABILITY_API_KEY:
            try:
                self.stability = StabilityAIService()
            except Exception:
                self.stability = None
        self.logger = logging.getLogger("uvicorn.error")
        self.compositor = None
        if settings.IMAGE_COMPOSITION_ENABLED:
            try:
                self.compositor = ImageCompositor()
            except Exception as e:
                self.logger.warning(f"Compositor initialization failed: {e}")
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
    
    async def generate_composed_image(self, analysis: dict, size: str = "1024x1024"):
        """
        Generate composed image with text overlays and elements
        
        Args:
            analysis: Analysis from CompositionAnalyzerAgent with:
                - scene_description: Base scene to generate
                - text_overlays: Text to add on image
                - screen_content: Content for screens
                - image_style: Style preferences
            size: Image size
            
        Returns:
            dict with:
                - final_image_url: URL to final composed image
                - layers: JSON structure for editing
                - base_image_url: URL to base image (without text)
        """
        if not self.stability:
            raise RuntimeError("Stability AI not configured for composition")
        
        if not self.compositor:
            raise RuntimeError("ImageCompositor not initialized")
        
        self.logger.info("[ImageGen] Starting composed image generation")
        
        # Step 1: Generate base scene WITHOUT text
        scene_prompt = analysis.get("scene_description", "")
        image_style = analysis.get("image_style", "professional, high quality")
        
        # Build clean prompt without text mentions
        base_prompt = f"{scene_prompt}. {image_style}. Professional photography, high quality, no text, no captions."
        
        self.logger.info(f"[ImageGen] Base prompt: {base_prompt[:100]}")
        
        # Generate base image from Stability
        data_url = await self.stability.generate_image(base_prompt, style=settings.IMAGE_GEN_STYLE, size=size)
        
        # Extract image bytes
        header, b64 = data_url.split(",", 1)
        base_image_bytes = base64.b64decode(b64)
        
        self.logger.info(f"[ImageGen] Base image generated: {len(base_image_bytes)} bytes")
        
        # Step 2: Apply composition (text overlays, etc.)
        composed_result = await self.compositor.compose_image(analysis, base_image_bytes)
        
        self.logger.info(f"[ImageGen] Composition complete")
        
        # Step 3: Save final composed image to storage
        final_filename = f"composed_{uuid.uuid4().hex}.png"
        final_url = await storage.save_image(
            composed_result.final_image,
            final_filename,
            "image/png"
        )
        
        self.logger.info(f"[ImageGen] Final image saved: {final_url}")
        
        # Return result
        return {
            "final_image_url": final_url,
            "layers": composed_result.to_json(),
            "base_image_url": composed_result.base_image_url,
            "dimensions": {
                "width": composed_result.dimensions[0],
                "height": composed_result.dimensions[1]
            }
        }