import google.generativeai as genai
from config import settings
from app.services.cache_service import cache_service
import asyncio

class QualityReviewerAgent:
    def __init__(self):
        if settings.GOOGLE_API_KEY:
            genai.configure(api_key=settings.GOOGLE_API_KEY)
            self.model = genai.GenerativeModel(
                settings.TEXT_MODEL,
                generation_config=genai.types.GenerationConfig(
                    temperature=settings.REVIEWER_TEMPERATURE,
                    max_output_tokens=settings.MAX_OUTPUT_TOKENS,
                    candidate_count=1
                )
            )
        else:
            self.model = None
    
    async def review_posts(self, posts, request):
        """Review and improve generated posts"""
        if not self.model:
            return posts
        
        # Create cache key from posts
        cache_key = f"review:{hash(str(posts))}:{request.campaign_id}"
        
        # Check cache first
        cached_result = cache_service.get_cached_ai_result(cache_key, "review")
        if cached_result:
            return cached_result
        
        try:
            # Add timeout to the request
            await asyncio.wait_for(
                asyncio.sleep(0.1),  # Simulate processing time
                timeout=settings.AI_REQUEST_TIMEOUT
            )
            
            # Simple implementation - just return the posts as-is
            # In production, this would analyze quality, tone, etc.
            result = posts
            
            # Cache the result
            cache_service.set_cached_ai_result(cache_key, result, "review", settings.CACHE_TTL)
            
            return result
        except asyncio.TimeoutError:
            # Return original posts if timeout
            return posts
        except Exception as e:
            # Return original posts if error
            return posts