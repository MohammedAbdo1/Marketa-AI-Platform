import google.generativeai as genai
from config import settings
from app.services.cache_service import cache_service
import asyncio
import json
import re

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
        """Generate marketing posts based on campaign structure.
        For now, produce multiple posts synchronously without external calls when API key missing
        or as a fast fallback. Count is derived from request.posts_per_week (min 3) and limited.
        """
        if not self.model:
            # Fallback: generate N placeholder posts so UI shows multiple items
            num_posts = max(3, getattr(request, 'posts_per_week', 3) or 3)
            num_posts = min(num_posts, 12)
            platforms = getattr(request, 'platforms', []) or ['instagram']
            product = getattr(request, 'product_name', 'Product')
            posts = []
            for i in range(num_posts):
                platform = platforms[i % len(platforms)] if platforms else 'instagram'
                # Single-language fallback content (no language enforcement)
                content_ar = ""
                content_en = f"Post {i+1} about {product}"
                image_prompt = _build_image_prompt_from_request(request, platform)
                posts.append({
                    "platform": platform,
                    "post_type": "text",
                    "content_ar": content_ar,
                    "content_en": content_en,
                    "hashtags": ["#تسويق", "#أعمال"],
                    "needs_image": True,
                    "image_url": None,
                    "image_prompt": image_prompt,
                    "week": 1 + (i // 7),
                    "day": 1 + (i % 7),
                })
            return posts
        
        # Create cache key from request
        cache_key = f"posts:{request.business_type}:{request.product_name}:{len(request.platforms)}"
        
        # Check cache first
        cached_result = cache_service.get_cached_ai_result(cache_key, "posts")
        if cached_result:
            return cached_result
        
        # Ask for a strict JSON list of posts (language-agnostic: model decides language from description)
        num_posts = max(3, getattr(request, 'posts_per_week', 3) or 3)
        num_posts = min(num_posts, 12)
        platforms = getattr(request, 'platforms', []) or ['instagram']
        platform_str = ", ".join(platforms)

        prompt = f"""
        بصفتك كاتب محتوى تسويقي محترف، أنشئ قائمة JSON فقط (بدون أي نص خارجي) تحتوي على {num_posts} عناصر كمنشورات لمنصات: {platform_str}.
        الحملة:
        - نوع العمل: {request.business_type}
        - المنتج/الخدمة: {request.product_name}
        - الوصف: {request.description}
        - الهدف: {getattr(request, 'campaign_goal', getattr(request, 'goal', 'increase sales'))}
        - اللغة: استخدم نفس لغة الوصف تلقائياً (لا تُترجم)

        لكل عنصر في المصفوفة أعطِ الحقول التالية حرفياً:
        {{
          "platform": one of [{platform_str}],
          "post_type": "text",
          "content": "caption text in the same language as the input description",
          "hashtags": ["#...", "#..."],
          "image_prompt": "وصف موجز للصورة (إن لزم)",
          "week": رقم الأسبوع يبدأ من 1,
          "day": رقم اليوم 1-7
        }}

        شروط صارمة:
        - أعد فقط JSON صالح يبدأ بـ '[' وينتهي بـ ']'.
        - لا تضع كود أو شرح أو Markdown.
        - نوّع المنصة بين العناصر طبقاً للقائمة المتاحة.
        """
        
        try:
            # Add timeout to the request
            response = await asyncio.wait_for(
                asyncio.to_thread(self.model.generate_content, prompt),
                timeout=settings.AI_REQUEST_TIMEOUT
            )
            
            # Try parse strict JSON
            text = getattr(response, 'text', None) or getattr(response, 'candidates', [{}])[0].content.parts[0].text
            try:
                json_str = _extract_json(text)
                parsed = json.loads(json_str)
            except Exception:
                # Fallback: derive simple posts from raw text without failing the request
                chunks = [c.strip() for c in re.split(r"\n\n+|\r\n\r\n+|\t\t+|---+", text or "") if c.strip()]
                parsed = [{
                    "platform": platforms[i % len(platforms)],
                    "post_type": "text",
                    "content": chunks[i % len(chunks)] if chunks else f"Post {i+1} for {getattr(request,'product_name','Product')}",
                    "hashtags": ["#marketing"],
                    "week": 1 + (i // 7),
                    "day": 1 + (i % 7)
                } for i in range(num_posts)]
            # Validate and normalize
            result = []
            for i, item in enumerate(parsed):
                platform = str(item.get("platform") or platforms[i % len(platforms)]).lower()
                hashtags = item.get("hashtags")
                if isinstance(hashtags, str):
                    hashtags = [h.strip() for h in hashtags.split() if h.startswith('#')]
                if not isinstance(hashtags, list):
                    hashtags = []
                # Language-agnostic mapping: accept content_ar/content_en/content
                content_ar = item.get("content_ar") or ""
                content_en = item.get("content_en") or ""
                generic = item.get("content") or ""
                if not content_ar and not content_en and generic:
                    # Store generic content in EN field to keep pipeline unchanged
                    content_en = generic
                result.append({
                    "platform": platform,
                    "post_type": item.get("post_type") or "text",
                    "content_ar": content_ar,
                    "content_en": content_en,
                    "hashtags": hashtags,
                    "needs_image": True,
                    "image_urls": [],
                    "image_prompt": item.get("image_prompt") or _build_image_prompt_from_request(request, platform),
                    "week": int(item.get("week") or 1 + (i // 7)),
                    "day": int(item.get("day") or 1 + (i % 7)),
                })
            
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

# Helpers
def _extract_json(text: str) -> str:
    """Extract first JSON array or object from arbitrary text."""
    if not text:
        return "[]"
    # Prefer array
    m = re.search(r"\[.*\]", text, flags=re.S)
    if m:
        return m.group(0)
    m = re.search(r"\{.*\}", text, flags=re.S)
    if m:
        return m.group(0)
    return "[]"

def _detect_language(text: str) -> str:
    """Very light heuristic: Arabic chars -> 'ar', else 'en'."""
    if not text:
        return 'en'
    if re.search(r"[\u0600-\u06FF]", text):
        return 'ar'
    return 'en'

def _build_image_prompt_from_request(request, platform: str) -> str:
    """Construct a strong image prompt from user request fields and platform.
    - Uses the raw description as authoritative requirements
    - Adds platform-specific framing and quality terms
    - Keeps it deterministic and production-oriented
    """
    business = getattr(request, 'business_type', '')
    product = getattr(request, 'product_name', '') or getattr(request, 'name', '')
    description = getattr(request, 'description', '')
    goal = getattr(request, 'campaign_goal', getattr(request, 'goal', ''))
    platform = (platform or '').lower()

    # Platform style guidance
    platform_styles = {
        'instagram': 'square 1080x1080, vibrant, attention grabbing composition',
        'facebook': '1200x630 aspect, clean composition, high CTR visual',
        'linkedin': '1200x627 aspect, professional corporate aesthetic',
        'twitter': '1200x675 aspect, bold high-contrast visual',
    }
    style = platform_styles.get(platform, 'high quality social media visual')

    # Compose prompt. Put user description verbatim to enforce elements (e.g. "رجل وبنت ... شاشة ... واجهة نظام الخياطة" )
    prompt = (
        f"{description}. Visual for {platform or 'social media'} in the {business} domain. "
        f"Product/Service: {product}. Goal: {goal}. {style}. "
        f"Commercial photography, realistic lighting, crisp details, DSLR quality, no watermark. "
        f"If text is requested, include clear red caption in Arabic as specified at the bottom."
    ).strip()

    return prompt