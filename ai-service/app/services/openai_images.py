import os
import aiohttp
import logging
from config import settings

logger = logging.getLogger("uvicorn.error")

class OpenAIImageService:
    """OpenAI DALL-E Image Generation Service"""
    
    def __init__(self):
        self.api_key = settings.OPENAI_API_KEY
        if not self.api_key:
            raise ValueError("OPENAI_API_KEY is required")
        self.base_url = "https://api.openai.com/v1/images/generations"
        logger.info("OpenAI Image Service initialized")
    
    async def generate_image(self, prompt: str, size: str = "1024x1024") -> str:
        """
        Generate image using DALL-E 3
        Returns: image URL (temporary URL from OpenAI)
        """
        # Map our sizes to OpenAI supported sizes
        size_map = {
            "1024x1024": "1024x1024",
            "1216x640": "1792x1024",  # Closest to 1.9:1 ratio
            "640x1216": "1024x1792",
        }
        openai_size = size_map.get(size, "1024x1024")
        
        headers = {
            "Authorization": f"Bearer {self.api_key}",
            "Content-Type": "application/json"
        }
        
        payload = {
            "model": "dall-e-3",
            "prompt": prompt[:4000],  # DALL-E 3 max prompt length
            "n": 1,
            "size": openai_size,
            "quality": "standard",  # or "hd" for higher quality
            "response_format": "url"  # Get URL instead of base64
        }
        
        try:
            async with aiohttp.ClientSession() as session:
                async with session.post(self.base_url, headers=headers, json=payload, timeout=60) as response:
                    if response.status != 200:
                        error_text = await response.text()
                        raise Exception(f"OpenAI API error: {response.status} - {error_text}")
                    
                    data = await response.json()
                    image_url = data["data"][0]["url"]
                    logger.info(f"OpenAI image generated: {image_url[:100]}...")
                    return image_url
                    
        except Exception as e:
            logger.exception(f"OpenAI image generation failed: {str(e)}")
            raise Exception(f"OpenAI image generation failed: {str(e)}")

