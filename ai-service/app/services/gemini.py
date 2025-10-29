import google.generativeai as genai
from config import settings
from typing import Dict, Any, List
import json

class GeminiService:
    def __init__(self):
        if not settings.GOOGLE_API_KEY:
            raise ValueError("GOOGLE_API_KEY not found in environment variables")
        
        genai.configure(api_key=settings.GOOGLE_API_KEY)
        self.model = genai.GenerativeModel(settings.DEFAULT_LLM_MODEL)
    
    async def generate_text(self, prompt: str, **kwargs) -> str:
        """Generate text using Gemini"""
        try:
            response = self.model.generate_content(prompt)
            return response.text
        except Exception as e:
            raise Exception(f"Gemini generation failed: {str(e)}")
    
    async def generate_structured_response(self, prompt: str, response_format: str = "json") -> Dict[str, Any]:
        """Generate structured JSON response"""
        try:
            structured_prompt = f"""
            {prompt}
            
            Please respond in valid JSON format only. Do not include any text outside the JSON.
            """
            
            response = self.model.generate_content(structured_prompt)
            response_text = response.text.strip()
            
            # Clean response if it has markdown formatting
            if response_text.startswith("```json"):
                response_text = response_text[7:]
            if response_text.endswith("```"):
                response_text = response_text[:-3]
            
            return json.loads(response_text)
        
        except json.JSONDecodeError as e:
            raise Exception(f"Failed to parse JSON response: {str(e)}")
        except Exception as e:
            raise Exception(f"Gemini structured generation failed: {str(e)}")
    
    async def generate_arabic_text(self, prompt: str) -> str:
        """Generate Arabic text with proper formatting"""
        arabic_prompt = f"""
        {prompt}
        
        Please respond in Arabic only. Use proper Arabic formatting and grammar.
        Make the content engaging and suitable for social media marketing.
        """
        
        return await self.generate_text(arabic_prompt)
    
    async def generate_english_text(self, prompt: str) -> str:
        """Generate English text with proper formatting"""
        english_prompt = f"""
        {prompt}
        
        Please respond in English only. Use proper English formatting and grammar.
        Make the content engaging and suitable for social media marketing.
        """
        
        return await self.generate_text(english_prompt)
    
    async def suggest_colors(self, description: str) -> List[Dict[str, str]]:
        """Suggest color palettes based on product description"""
        prompt = f"""
        Based on this product/service description: "{description}"
        
        Suggest 3 different color palettes that would work well for social media marketing.
        Each palette should have:
        - primary_color: main brand color
        - secondary_color: supporting color
        - accent_color: highlight color
        
        Consider the industry, target audience, and brand personality.
        Return as JSON array with 3 objects.
        """
        
        try:
            response = await self.generate_structured_response(prompt)
            return response if isinstance(response, list) else [response]
        except Exception as e:
            # Fallback to default palettes
            return [
                {
                    "name": "Professional Blue",
                    "primary_color": "#2563eb",
                    "secondary_color": "#64748b",
                    "accent_color": "#f59e0b"
                },
                {
                    "name": "Warm Orange",
                    "primary_color": "#ea580c",
                    "secondary_color": "#dc2626",
                    "accent_color": "#fbbf24"
                },
                {
                    "name": "Nature Green",
                    "primary_color": "#16a34a",
                    "secondary_color": "#059669",
                    "accent_color": "#84cc16"
                }
            ]
