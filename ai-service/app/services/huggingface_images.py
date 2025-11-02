import aiohttp
import logging
import base64
from config import settings

logger = logging.getLogger("uvicorn.error")

class HuggingFaceImageService:
    """
    Hugging Face Inference API
    
    Free tier: 1000 requests/month (without API key - rate limited)
    With API key: Better rate limits
    
    Website: https://huggingface.co
    Models: https://huggingface.co/models?pipeline_tag=text-to-image
    """
    
    def __init__(self, api_key: str = None):
        self.api_key = api_key
        self.base_url = "https://api-inference.huggingface.co/models"
        
        # Free models available:
        # - stabilityai/stable-diffusion-xl-base-1.0 (SDXL)
        # - runwayml/stable-diffusion-v1-5 (SD 1.5)
        # - CompVis/stable-diffusion-v1-4 (SD 1.4)
        self.model = "stabilityai/stable-diffusion-xl-base-1.0"
        
        logger.info(f"HuggingFace Image Service initialized (FREE tier - Model: {self.model})")
    
    async def generate_image(self, prompt: str, size: str = "1024x1024") -> str:
        """
        Generate image using HuggingFace Inference API
        
        Args:
            prompt: Text description of the image
            size: Image size (HuggingFace uses fixed sizes per model)
            
        Returns:
            base64 data URL
        """
        headers = {
            "Content-Type": "application/json"
        }
        
        if self.api_key:
            headers["Authorization"] = f"Bearer {self.api_key}"
        
        payload = {
            "inputs": prompt[:1000],  # Limit prompt length
        }
        
        url = f"{self.base_url}/{self.model}"
        
        try:
            async with aiohttp.ClientSession() as session:
                async with session.post(url, headers=headers, json=payload, timeout=120) as response:
                    if response.status == 503:
                        # Model is loading - retry after a delay
                        error_text = await response.text()
                        raise Exception(f"Model is loading, please retry in a moment: {error_text}")
                    
                    if response.status != 200:
                        error_text = await response.text()
                        raise Exception(f"HuggingFace API error: {response.status} - {error_text}")
                    
                    # Get image bytes
                    image_bytes = await response.read()
                    
                    # Convert to base64 data URL
                    b64_image = base64.b64encode(image_bytes).decode()
                    data_url = f"data:image/png;base64,{b64_image}"
                    
                    logger.info(f"HuggingFace image generated (FREE): {prompt[:50]}...")
                    return data_url
                    
        except Exception as e:
            logger.exception(f"HuggingFace image generation failed: {str(e)}")
            raise Exception(f"HuggingFace image generation failed: {str(e)}")

