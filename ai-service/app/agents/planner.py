import google.generativeai as genai
from config import settings
from app.services.cache_service import cache_service
import asyncio

class CampaignPlannerAgent:
    def __init__(self):
        if settings.GOOGLE_API_KEY:
            genai.configure(api_key=settings.GOOGLE_API_KEY)
            self.model = genai.GenerativeModel(
                settings.TEXT_MODEL,
                generation_config=genai.types.GenerationConfig(
                    temperature=settings.PLANNER_TEMPERATURE,
                    max_output_tokens=settings.MAX_OUTPUT_TOKENS,
                    candidate_count=1
                )
            )
        else:
            self.model = None
    
    async def generate_preview(self, request):
        """Generate campaign structure preview"""
        import logging
        logger = logging.getLogger(__name__)
        
        if not self.model:
            raise Exception("Google API key not configured")
        
        # Create cache key from request
        cache_key = f"preview:{request.business_type}:{request.product_name}:{request.duration_days}"
        
        # Check cache first
        cached_result = cache_service.get_cached_ai_result(cache_key, "preview")
        if cached_result:
            logger.info("Using cached preview result")
            return cached_result
        
        prompt = f"""
        أنت خبير في التسويق الرقمي. أنشئ خطة حملة تسويقية للعمل التالي:
        
        نوع العمل: {request.business_type}
        المنتج/الخدمة: {request.product_name}
        الوصف: {request.description}
        الهدف: {request.goal}
        الجمهور المستهدف: {request.target_audience}
        المنصات: {request.platforms}
        المدة: {request.duration_days} يوم
        عدد المنشورات أسبوعياً: {request.posts_per_week}
        
        أرجو إعطاء:
        1. هيكل الحملة (عدد المنشورات لكل منصة)
        2. المواضيع المقترحة
        3. التوزيع الأسبوعي
        4. التكلفة التقديرية
        
        أجب باللغة العربية.
        """
        
        try:
            logger.info(f"Starting Google API call for preview generation (timeout: {settings.AI_REQUEST_TIMEOUT}s)")
            import time
            start_time = time.time()
            
            # Add timeout to the request
            response = await asyncio.wait_for(
                asyncio.to_thread(self.model.generate_content, prompt),
                timeout=settings.AI_REQUEST_TIMEOUT
            )
            
            elapsed_time = time.time() - start_time
            logger.info(f"Google API call completed in {elapsed_time:.2f} seconds")
            
            # Check if response is valid
            if not response or not hasattr(response, 'text') or not response.text:
                raise Exception("Invalid response from AI service - empty or missing text")
            
            result = {
                "campaign_name": f"حملة {request.product_name}",
                "structure": response.text,
                "estimated_posts": request.posts_per_week * (request.duration_days // 7),
                "platforms_breakdown": {platform: request.posts_per_week for platform in request.platforms}
            }
            
            # Cache the result
            cache_service.set_cached_ai_result(cache_key, result, "preview", settings.CACHE_TTL)
            
            return result
        except asyncio.TimeoutError:
            raise Exception("AI request timeout - service took too long to respond")
        except Exception as e:
            # Re-raise to let Celery task handle it properly
            raise Exception(f"AI generation failed: {str(e)}")
    
    async def suggest_colors(self, description):
        """Suggest color palettes based on description"""
        if not self.model:
            raise Exception("Google API key not configured")
        
        # Create cache key from description
        cache_key = f"colors:{description[:50]}"  # Use first 50 chars for key
        
        # Check cache first
        cached_result = cache_service.get_cached_ai_result(cache_key, "colors")
        if cached_result:
            return cached_result
        
        prompt = f"""
        اقترح ألوان مناسبة للعلامة التجارية التالية:
        {description}
        
        أرجو اقتراح 3 مجموعات ألوان مختلفة مع الأكواد HEX.
        """
        
        try:
            # Add timeout to the request
            response = await asyncio.wait_for(
                asyncio.to_thread(self.model.generate_content, prompt),
                timeout=settings.AI_REQUEST_TIMEOUT
            )
            
            # Check if response is valid
            if not response or not hasattr(response, 'text') or not response.text:
                raise Exception("Invalid response from AI service - empty or missing text")
            
            result = {"suggestions": response.text}
            
            # Cache the result
            cache_service.set_cached_ai_result(cache_key, result, "colors", settings.CACHE_TTL)
            
            return result
        except asyncio.TimeoutError:
            raise Exception("AI request timeout - service took too long to respond")
        except Exception as e:
            raise Exception(f"Color suggestion failed: {str(e)}")