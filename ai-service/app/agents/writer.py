import google.generativeai as genai
from config import settings
from app.services.cache_service import cache_service
import asyncio

class ContentWriterAgent:
    def __init__(self):
        if settings.GOOGLE_API_KEY:
            genai.configure(api_key=settings.GOOGLE_API_KEY)
            self.model = genai.GenerativeModel(
                settings.TEXT_MODEL,
                generation_config=genai.types.GenerationConfig(
                    temperature=settings.WRITER_TEMPERATURE,
                    max_output_tokens=settings.MAX_OUTPUT_TOKENS,
                    candidate_count=1
                )
            )
        else:
            self.model = None
    
    async def generate_posts(self, structure, request):
        """Generate marketing posts based on campaign structure"""
        if not self.model:
            return [{"error": "Google API key not configured"}]
        
        # Create cache key from request
        cache_key = f"posts:{request.business_type}:{request.product_name}:{len(request.platforms)}"
        
        # Check cache first
        cached_result = cache_service.get_cached_ai_result(cache_key, "posts")
        if cached_result:
            return cached_result
        
        # Simple implementation - generate one sample post
        prompt = f"""
        أنشئ منشور تسويقي للعمل التالي:
        
        نوع العمل: {request.business_type}
        المنتج: {request.product_name}
        الوصف: {request.description}
        المنصة: {request.platforms[0] if request.platforms else 'Instagram'}
        
        أرجو إنشاء:
        1. نص المنشور باللغة العربية
        2. نص المنشور باللغة الإنجليزية
        3. هاشتاغات مناسبة
        4. دعوة للعمل
        
        أجب بصيغة JSON.
        """
        
        try:
            # Add timeout to the request
            response = await asyncio.wait_for(
                asyncio.to_thread(self.model.generate_content, prompt),
                timeout=settings.AI_REQUEST_TIMEOUT
            )
            
            result = [{
                "content_ar": "منشور تجريبي باللغة العربية",
                "content_en": "Sample post in English",
                "hashtags": ["#تسويق", "#أعمال"],
                "call_to_action": "تواصل معنا الآن"
            }]
            
            # Cache the result
            cache_service.set_cached_ai_result(cache_key, result, "posts", settings.CACHE_TTL)
            
            return result
        except asyncio.TimeoutError:
            return [{"error": "Request timeout - AI service took too long to respond"}]
        except Exception as e:
            return [{"error": f"Post generation failed: {str(e)}"}]
    
    async def regenerate_post_text(self, request):
        """Regenerate text for a specific post"""
        if not self.model:
            return {"error": "Google API key not configured"}
        
        # Create cache key from request
        cache_key = f"regenerate_text:{request.post_id}:{hash(str(request))}"
        
        # Check cache first
        cached_result = cache_service.get_cached_ai_result(cache_key, "regenerate_text")
        if cached_result:
            return cached_result
        
        prompt = f"""
        أعد كتابة هذا المنشور بطريقة مختلفة:
        {request.content_ar if hasattr(request, 'content_ar') else 'منشور تجريبي'}
        
        أرجو إعطاء نص جديد باللغة العربية والإنجليزية.
        """
        
        try:
            # Add timeout to the request
            response = await asyncio.wait_for(
                asyncio.to_thread(self.model.generate_content, prompt),
                timeout=settings.AI_REQUEST_TIMEOUT
            )
            
            result = {
                "content_ar": "منشور جديد باللغة العربية",
                "content_en": "New post in English"
            }
            
            # Cache the result
            cache_service.set_cached_ai_result(cache_key, result, "regenerate_text", settings.CACHE_TTL)
            
            return result
        except asyncio.TimeoutError:
            return {"error": "Request timeout - AI service took too long to respond"}
        except Exception as e:
            return {"error": f"Text regeneration failed: {str(e)}"}