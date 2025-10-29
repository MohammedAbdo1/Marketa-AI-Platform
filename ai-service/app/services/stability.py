import requests
import base64
from config import settings
from typing import Optional, Dict, Any
import json

class StabilityAIService:
    def __init__(self):
        if not settings.STABILITY_API_KEY:
            raise ValueError("STABILITY_API_KEY not found in environment variables")
        
        self.api_key = settings.STABILITY_API_KEY
        self.base_url = "https://api.stability.ai/v1"
        self.headers = {
            "Authorization": f"Bearer {self.api_key}",
            "Content-Type": "application/json"
        }
    
    async def generate_image(self, prompt: str, style: str = "photographic", size: str = "1024x1024") -> str:
        """Generate image using Stability AI"""
        try:
            # Parse dimensions
            width, height = map(int, size.split('x'))
            
            # Prepare the request
            data = {
                "text_prompts": [
                    {
                        "text": prompt,
                        "weight": 1.0
                    }
                ],
                "cfg_scale": 7,
                "height": height,
                "width": width,
                "samples": 1,
                "steps": 30,
                "style_preset": style
            }
            
            # Make API request
            response = requests.post(
                f"{self.base_url}/generation/{settings.IMAGE_MODEL}/text-to-image",
                headers=self.headers,
                json=data,
                timeout=60
            )
            
            if response.status_code != 200:
                raise Exception(f"Stability AI API error: {response.status_code} - {response.text}")
            
            # Parse response
            result = response.json()
            
            if "artifacts" not in result or not result["artifacts"]:
                raise Exception("No image generated")
            
            # Get base64 image data
            image_data = result["artifacts"][0]["base64"]
            
            # For now, return the base64 data
            # In production, you'd save this to a file storage service
            return f"data:image/png;base64,{image_data}"
        
        except Exception as e:
            raise Exception(f"Image generation failed: {str(e)}")
    
    async def generate_marketing_image(self, product_name: str, description: str, brand_colors: Dict[str, str] = None) -> str:
        """Generate marketing-specific image"""
        try:
            # Create enhanced prompt for marketing
            color_info = ""
            if brand_colors:
                primary = brand_colors.get("primary_color", "")
                secondary = brand_colors.get("secondary_color", "")
                if primary and secondary:
                    color_info = f" in {primary} and {secondary} colors"
            
            prompt = f"""
            Professional marketing image for {product_name}.
            {description}
            {color_info}
            Clean, modern design, high quality, social media ready,
            professional lighting, commercial photography style,
            suitable for Instagram and Facebook posts
            """
            
            return await self.generate_image(prompt, style="photographic")
        
        except Exception as e:
            raise Exception(f"Marketing image generation failed: {str(e)}")
    
    async def generate_lifestyle_image(self, product_name: str, lifestyle_context: str) -> str:
        """Generate lifestyle-focused image"""
        try:
            prompt = f"""
            Lifestyle image featuring {product_name} in {lifestyle_context}.
            Natural, authentic setting, people using the product,
            warm lighting, lifestyle photography style,
            social media friendly, engaging composition
            """
            
            return await self.generate_image(prompt, style="photographic")
        
        except Exception as e:
            raise Exception(f"Lifestyle image generation failed: {str(e)}")
    
    async def generate_infographic(self, topic: str, data_points: list) -> str:
        """Generate infographic-style image"""
        try:
            data_text = ", ".join(data_points[:3])  # Limit to 3 points
            
            prompt = f"""
            Professional infographic about {topic}.
            Key points: {data_text}
            Clean, modern design, data visualization style,
            professional colors, easy to read text,
            social media infographic format
            """
            
            return await self.generate_image(prompt, style="digital-art")
        
        except Exception as e:
            raise Exception(f"Infographic generation failed: {str(e)}")
    
    async def generate_brand_image(self, brand_name: str, brand_values: list) -> str:
        """Generate brand-focused image"""
        try:
            values_text = ", ".join(brand_values[:3])
            
            prompt = f"""
            Brand image for {brand_name}.
            Brand values: {values_text}
            Professional, trustworthy, modern design,
            clean background, brand identity style,
            suitable for business social media
            """
            
            return await self.generate_image(prompt, style="photographic")
        
        except Exception as e:
            raise Exception(f"Brand image generation failed: {str(e)}")
    
    def _validate_image_prompt(self, prompt: str) -> bool:
        """Validate image prompt quality"""
        if not prompt or len(prompt.strip()) < 10:
            return False
        
        # Check for inappropriate content (basic check)
        inappropriate_keywords = ["nude", "explicit", "violence", "hate"]
        prompt_lower = prompt.lower()
        
        for keyword in inappropriate_keywords:
            if keyword in prompt_lower:
                return False
        
        return True
    
    async def generate_batch_images(self, prompts: list) -> list:
        """Generate multiple images efficiently"""
        try:
            results = []
            
            for prompt in prompts:
                if self._validate_image_prompt(prompt):
                    image_url = await self.generate_image(prompt)
                    results.append({
                        "prompt": prompt,
                        "image_url": image_url,
                        "status": "success"
                    })
                else:
                    results.append({
                        "prompt": prompt,
                        "image_url": None,
                        "status": "failed",
                        "error": "Invalid prompt"
                    })
            
            return results
        
        except Exception as e:
            raise Exception(f"Batch image generation failed: {str(e)}")
