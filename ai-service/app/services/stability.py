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
        self.base_url_v2 = "https://api.stability.ai/v2beta/stable-image/generate"
        self.headers = {
            "authorization": f"Bearer {self.api_key}",
            "accept": "image/*"
        }
    
    async def generate_image(self, prompt: str, style: str = "photographic", size: str = "1024x1024") -> str:
        """Generate image using Stability AI v2beta API"""
        try:
            # Stability SD3 requires English prompts only - translate if needed
            prompt = self._ensure_english_prompt(prompt)
            # Map size to aspect ratio for v2beta API
            width, height = map(int, size.split('x'))
            if width == height:
                aspect_ratio = "1:1"
            elif width > height:
                # Landscape
                ratio = round(width / height, 1)
                if ratio >= 1.8:
                    aspect_ratio = "16:9"
                else:
                    aspect_ratio = "3:2"
            else:
                # Portrait
                aspect_ratio = "9:16"
            
            # Prepare form data (v2beta uses multipart, not JSON)
            data = {
                "prompt": prompt,
                "output_format": "png",
                "aspect_ratio": aspect_ratio
            }
            
            # Add style if supported (not all v2 endpoints support style_preset)
            # For sd3, we embed style in prompt instead
            
            # Make API request to v2beta SD3 endpoint
            response = requests.post(
                f"{self.base_url_v2}/sd3",
                headers=self.headers,
                files={"none": ""},  # Required empty file for multipart
                data=data,
                timeout=60
            )
            
            if response.status_code != 200:
                raise Exception(f"Stability AI API error: {response.status_code} - {response.text}")
            
            # v2beta returns raw image bytes directly
            image_bytes = response.content
            
            if not image_bytes:
                raise Exception("No image generated")
            
            # Convert to base64 for compatibility
            image_data = base64.b64encode(image_bytes).decode('utf-8')
            
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
    
    def _ensure_english_prompt(self, prompt: str) -> str:
        """Convert Arabic/mixed prompts to English for Stability compatibility"""
        import re
        # Quick check: if prompt has Arabic chars, translate via simple rules or keep English parts
        has_arabic = bool(re.search(r'[\u0600-\u06FF]', prompt))
        if not has_arabic:
            return prompt
        
        # Simple translation mapping for common terms (fast, no API call)
        translations = {
            'رجل': 'man',
            'بنت': 'woman',
            'امرأة': 'woman',
            'شاشة': 'screen',
            'كمبيوتر': 'computer',
            'واجهه': 'interface',
            'واجهة': 'interface',
            'نظام': 'system',
            'الخياطة': 'tailoring',
            'erp': 'ERP',
            'صورة': 'image',
            'تحت': 'bottom',
            'أحمر': 'red',
            'في الوسط': 'in the center',
            'احترافي': 'professional',
            'عالي الجودة': 'high quality',
        }
        
        # Replace Arabic terms with English
        english_prompt = prompt
        for ar, en in translations.items():
            english_prompt = re.sub(ar, en, english_prompt, flags=re.IGNORECASE)
        
        # Remove remaining Arabic chars (likely connecting words)
        english_prompt = re.sub(r'[\u0600-\u06FF]+', ' ', english_prompt)
        
        # Clean up extra spaces
        english_prompt = re.sub(r'\s+', ' ', english_prompt).strip()
        
        # If result is too short, use a generic fallback
        if len(english_prompt) < 10:
            english_prompt = "Professional marketing image, high quality, commercial photography"
        
        return english_prompt
