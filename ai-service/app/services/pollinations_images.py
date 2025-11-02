import aiohttp
import logging
import urllib.parse

logger = logging.getLogger("uvicorn.error")

class PollinationsImageService:
    """
    Pollinations.ai - Free unlimited image generation!
    No API key needed!
    
    Website: https://pollinations.ai
    API Docs: https://github.com/pollinations/pollinations
    """
    
    def __init__(self):
        self.base_url = "https://image.pollinations.ai/prompt"
        logger.info("Pollinations Image Service initialized (FREE - No API key needed)")
    
    async def generate_image(self, prompt: str, size: str = "1024x1024") -> str:
        """
        Generate image using Pollinations.ai
        
        Args:
            prompt: Text description of the image
            size: Image size in format "WIDTHxHEIGHT"
            
        Returns:
            Direct image URL from Pollinations CDN
        """
        # Parse size
        try:
            w, h = size.split('x') if 'x' in size else ('1024', '1024')
            w, h = int(w), int(h)
        except:
            w, h = 1024, 1024
        
        # URL encode the prompt
        encoded_prompt = urllib.parse.quote(prompt)
        
        # Pollinations URL format: /prompt/{prompt}?width={w}&height={h}&nologo=true
        image_url = f"{self.base_url}/{encoded_prompt}?width={w}&height={h}&nologo=true&enhance=true"
        
        logger.info(f"Pollinations image URL generated (FREE): {prompt[:50]}...")
        
        # Return URL - Pollinations serves images directly from their CDN
        # We'll download and save it in ImageGeneratorAgent
        return image_url

