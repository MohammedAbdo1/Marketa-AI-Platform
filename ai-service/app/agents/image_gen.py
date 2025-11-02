import google.generativeai as genai
from config import settings
from app.services.cache_service import cache_service
from app.services.stability import StabilityAIService
from app.services.openai_images import OpenAIImageService
from app.services.image_compositor import ImageCompositor
from app.services.storage import storage
import asyncio
import os
import uuid
import base64
import logging
import requests
import aiohttp

class ImageGeneratorAgent:
    def __init__(self):
        self.logger = logging.getLogger("uvicorn.error")
        self.providers = {}  # Dict to store available providers
        self.provider_names = []  # Ordered list based on priority
        
        # Load providers based on priority setting
        priority_list = settings.IMAGE_PROVIDERS_PRIORITY.split(',')
        
        for provider in priority_list:
            provider = provider.strip().lower()
            
            # Pollinations (FREE - No API key needed)
            if provider == 'pollinations' and settings.ENABLE_POLLINATIONS:
                try:
                    from app.services.pollinations_images import PollinationsImageService
                    self.providers['pollinations'] = PollinationsImageService()
                    self.provider_names.append('pollinations')
                    self.logger.info("✅ Pollinations provider loaded (FREE unlimited)")
                except Exception as e:
                    self.logger.warning(f"⚠️ Pollinations init failed: {e}")
            
            # HuggingFace (FREE tier - 1000/month)
            elif provider == 'huggingface' and settings.ENABLE_HUGGINGFACE:
                try:
                    from app.services.huggingface_images import HuggingFaceImageService
                    self.providers['huggingface'] = HuggingFaceImageService(
                        api_key=settings.HUGGINGFACE_API_KEY
                    )
                    self.provider_names.append('huggingface')
                    self.logger.info("✅ HuggingFace provider loaded (FREE tier)")
                except Exception as e:
                    self.logger.warning(f"⚠️ HuggingFace init failed: {e}")
            
            # Stability AI (PAID - High quality)
            elif provider == 'stability' and settings.ENABLE_STABILITY:
                if settings.STABILITY_API_KEY and settings.STABILITY_API_KEY != 'optional':
                    try:
                        from app.services.stability import StabilityAIService
                        self.providers['stability'] = StabilityAIService()
                        self.provider_names.append('stability')
                        self.logger.info("✅ Stability AI provider loaded")
                    except Exception as e:
                        self.logger.warning(f"⚠️ Stability init failed: {e}")
            
            # OpenAI DALL-E (PAID - Very high quality)
            elif provider == 'openai' and settings.ENABLE_OPENAI:
                if settings.OPENAI_API_KEY and settings.OPENAI_API_KEY != 'optional':
                    try:
                        from app.services.openai_images import OpenAIImageService
                        self.providers['openai'] = OpenAIImageService()
                        self.provider_names.append('openai')
                        self.logger.info("✅ OpenAI DALL-E provider loaded")
                    except Exception as e:
                        self.logger.warning(f"⚠️ OpenAI init failed: {e}")
        
        # Log final status
        if not self.providers:
            self.logger.error("❌ No image providers available!")
        else:
            self.logger.info(f"🎨 Active image providers (priority order): {self.provider_names}")
        
        # Initialize compositor
        self.compositor = None
        if settings.IMAGE_COMPOSITION_ENABLED:
            try:
                self.compositor = ImageCompositor()
            except Exception as e:
                self.logger.warning(f"Compositor initialization failed: {e}")
        
        # Initialize Gemini for prompt enhancement
        if settings.GOOGLE_API_KEY:
            genai.configure(api_key=settings.GOOGLE_API_KEY)
            self.model = genai.GenerativeModel(
                settings.TEXT_MODEL,
                generation_config=genai.types.GenerationConfig(
                    temperature=0.3,
                    max_output_tokens=500,
                    candidate_count=1
                )
            )
        else:
            self.model = None
    
    async def generate_image(self, prompt, size: str = "1024x1024", preferred_provider: str = None):
        """
        Generate image using available providers in priority order
        
        Args:
            prompt: Text description of the image
            size: Image size in format "WIDTHxHEIGHT"
            preferred_provider: Optional - force specific provider (for future user selection)
            
        Returns:
            URL to saved image
        """
        # If user specified a provider, try it first
        if preferred_provider and preferred_provider in self.providers:
            try:
                return await self._generate_with_provider(preferred_provider, prompt, size)
            except Exception as e:
                self.logger.warning(f"⚠️ Preferred provider '{preferred_provider}' failed: {str(e)}")
                # Fall through to try other providers
        
        # Try providers in priority order
        last_error = None
        for provider_name in self.provider_names:
            try:
                return await self._generate_with_provider(provider_name, prompt, size)
            except Exception as e:
                last_error = e
                self.logger.warning(f"⚠️ Provider '{provider_name}' failed: {str(e)}, trying next...")
                continue
        
        # All providers failed
        available = ', '.join(self.provider_names) if self.provider_names else 'None'
        raise RuntimeError(f"❌ All image providers failed! Available: {available}. Last error: {str(last_error)}")
    
    async def _generate_with_provider(self, provider_name: str, prompt: str, size: str) -> str:
        """
        Generate image with a specific provider and save it locally
        
        Returns:
            URL to saved image in static folder
        """
        provider = self.providers[provider_name]
        
        self.logger.info({"stage": "py_image_provider_start", "provider": provider_name, "size": size, "prompt": prompt[:100]})
        
        # Generate image (returns either data URL or direct URL)
        result = await provider.generate_image(prompt, size)
        
        # Handle different result types
        image_bytes = None
        
        if result.startswith('http://') or result.startswith('https://'):
            # Direct URL (Pollinations) - download it
            self.logger.info(f"Downloading image from {provider_name} CDN...")
            async with aiohttp.ClientSession() as session:
                async with session.get(result, timeout=60) as resp:
                    if resp.status != 200:
                        raise Exception(f"Failed to download image: HTTP {resp.status}")
                    image_bytes = await resp.read()
                    
        elif result.startswith('data:image'):
            # Base64 data URL (HuggingFace, Stability, etc.)
            header, b64 = result.split(",", 1)
            image_bytes = base64.b64decode(b64)
        else:
            raise Exception(f"Unknown result format from {provider_name}: {result[:50]}")
        
        # Save image to local storage
        images_dir = os.path.join("app", "static", "images")
        os.makedirs(images_dir, exist_ok=True)
        filename = f"{uuid.uuid4().hex}.png"
        filepath = os.path.join(images_dir, filename)
        
        with open(filepath, "wb") as f:
            f.write(image_bytes)
        
        final_url = f"{settings.IMAGE_BASE_URL}/static/images/{filename}"
        
        self.logger.info({
            "stage": "py_image_provider_success", 
            "provider": provider_name, 
            "path": filepath,
            "size_bytes": len(image_bytes),
            "url": final_url
        })
        
        return final_url
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