import asyncio
import json
import logging
import re
from datetime import datetime, timedelta

import google.generativeai as genai

from config import settings
from app.services.cache_service import cache_service

logger = logging.getLogger(__name__)


def extract_json_from_text(text: str):
    """Extract JSON object from a text response even if wrapped with additional text."""
    if not text:
        raise ValueError("Empty text provided")

    stripped = text.strip()
    try:
        return json.loads(stripped)
    except json.JSONDecodeError:
        pass

    match = re.search(r"\{.*\}", stripped, re.DOTALL)
    if match:
        try:
            return json.loads(match.group(0))
        except json.JSONDecodeError:
            pass

    raise ValueError("Could not extract valid JSON from response")

class CampaignPlannerAgent:
    def __init__(self):
        if settings.GOOGLE_API_KEY:
            genai.configure(api_key=settings.GOOGLE_API_KEY)
            self.model = genai.GenerativeModel(
                settings.TEXT_MODEL,
                generation_config=genai.types.GenerationConfig(
                    temperature=settings.PLANNER_TEMPERATURE,
                    max_output_tokens=4000,  # Increased for comprehensive output
                    candidate_count=1
                )
            )
        else:
            self.model = None
    
    async def generate_campaign_intelligence(self, request):
        """
        Generate comprehensive campaign intelligence including:
        - Language analysis
        - Campaign strategy
        - Daily calendar
        - Content briefs
        - Sample posts with composition
        """
        if not self.model:
            raise Exception("Google API key not configured")
        
        # Create cache key
        cache_key = f"intelligence:{request.business_type}:{request.product_name}:{request.duration_days}"
        
        # Check cache
        cached_result = cache_service.get_cached_ai_result(cache_key, "intelligence")
        if cached_result:
            logger.info("Using cached intelligence result")
            return cached_result
        
        # Calculate dates
        start_date = datetime.now()
        total_posts = (request.duration_days // 7) * request.posts_per_week
        num_weeks = request.duration_days // 7
        
        # Build comprehensive prompt
        prompt = f"""
أنت مخطط حملات تسويقية محترف ومتقدم. قم بتحليل المعلومات التالية وإنشاء خطة حملة تسويقية شاملة ومفصلة:

📋 معلومات العمل:
- نوع العمل: {request.business_type}
- اسم المنتج/الخدمة: {request.product_name}
- الوصف التفصيلي: {request.description}

🎯 تفاصيل الحملة:
- الهدف: {request.goal}
- الجمهور المستهدف: {json.dumps(request.target_audience, ensure_ascii=False)}
- المنصات: {', '.join(request.platforms)}
- المدة: {request.duration_days} يوم ({num_weeks} أسابيع)
- عدد المنشورات: {total_posts} منشور إجمالي
- معدل النشر: {request.posts_per_week} منشورات/أسبوع

📝 المطلوب منك:

1️⃣ تحليل اللغة والجمهور:
   - استنتج من الوصف: ما هي اللغة أو اللغات المطلوبة؟
   - إذا لم يذكر المستخدم لغة محددة، استنتجها من الموقع والجمهور
   - إذا ذكر صراحة "أريد المحتوى بالفرنسية" أو لغة معينة → التزم بها
   - إذا ذكر "عرب وأجانب" أو "Arabic and English" → استخدم اللغتين
   - خلاف ذلك → لغة واحدة حسب السياق

2️⃣ الاستراتيجية الشاملة:
   - ملخص تنفيذي (Executive Summary)
   - مراحل الحملة (4 مراحل): التشويق، الإطلاق، التفاعل، التحويل
   - لكل مرحلة: الهدف، الاستراتيجية، نوع المحتوى، الرسائل الرئيسية

3️⃣ الجدول الزمني المفصل:
   - توزيع يومي لكل أسبوع
   - تحديد نوع كل منشور ووقت نشره المناسب
   - توزيع متوازن على المنصات

4️⃣ أمثلة حقيقية (3 منشورات):
   - لكل منشور: المحتوى الكامل، الهاشتاغات، image prompt، content brief
   - Content brief يشمل: إرشادات التصوير، إرشادات المونتاج، نصائح Engagement

أرجع JSON بهذا الشكل الدقيق:

{{
  "language_analysis": {{
    "detected_languages": ["ar"],
    "primary_language": "ar",
    "audience_location": "Saudi Arabia",
    "audience_age": "18-35",
    "audience_behavior": "young professionals",
    "tone": "friendly"
  }},
  "executive_summary": {{
    "campaign_name": "حملة إطلاق...",
    "objective": "...",
    "duration": "{num_weeks} أسابيع",
    "total_posts": {total_posts},
    "target_kpis": {{
      "reach": "15,000-25,000",
      "engagement_rate": "4-6%",
      "conversions": "..."
    }},
    "strategy_overview": "حملة متدرجة من 4 مراحل..."
  }},
  "campaign_phases": [
    {{
      "phase": 1,
      "name": "مرحلة التشويق",
      "duration": "أسبوع 1",
      "objective": "بناء الترقب والفضول",
      "strategy": "محتوى غامض يثير الفضول...",
      "content_mix": {{
        "stories": "40%",
        "posts": "40%",
        "reels": "20%"
      }},
      "key_messages": ["شيء جديد قادم", "استعدوا"]
    }}
  ],
  "daily_calendar": {{
    "week_1": {{
      "theme": "التشويق",
      "days": [
        {{
          "day": 1,
          "date": "{start_date.strftime('%Y-%m-%d')}",
          "day_name": "الاثنين",
          "posts": [
            {{
              "time": "12:00 PM",
              "platform": "instagram",
              "type": "story",
              "theme": "تشويق",
              "content_summary": "فيديو قصير مشوّق"
            }}
          ]
        }}
      ]
    }}
  }},
  "sample_posts": [
    {{
      "platform": "instagram",
      "type": "reel",
      "content": {{
        "ar": "🍔 أخيراً هنا! البرجر الفاخر..."
      }},
      "primary_language": "ar",
      "hashtags": {{
        "ar": ["#برجر_فاخر", "#مطعم"]
      }},
      "image_prompt": "Professional food photography of premium burger, warm colors, appetizing",
      "content_brief": {{
        "instructions": {{
          "overview": "فيديو reel قصير 30 ثانية يعرض البرجر",
          "objective": "إثارة الشهية وزيادة الوعي"
        }},
        "filming": {{
          "equipment": ["iPhone 14 Pro", "Ring light", "Tripod"],
          "angles": ["زاوية 45 درجة", "لقطة قريبة"],
          "lighting": "إضاءة طبيعية + softbox",
          "duration": "30 ثانية"
        }},
        "editing": {{
          "software": "CapCut أو InShot",
          "effects": ["Slow motion 0.5x", "Smooth transitions"],
          "music": "Upbeat background music",
          "color_grading": "Warm tones"
        }},
        "engagement_tips": [
          "انشر في وقت الذروة (7-9 مساءً)",
          "رد على التعليقات خلال أول ساعة",
          "شارك في Stories"
        ]
      }},
      "expected_results": {{
        "views": "2,000-4,000",
        "engagement": "150-250 تفاعل",
        "shares": "30-50",
        "new_followers": "20-40"
      }},
      "week": 2,
      "day": 1,
      "phase": "الإطلاق"
    }}
  ],
  "content_guidelines": {{
    "visual_style": "modern, clean, appetizing",
    "colors": ["#E85D04", "#370617", "#FFBA08"],
    "photography_tips": [
      "استخدم إضاءة طبيعية",
      "صور من زاوية 45 درجة",
      "أضف props بسيطة"
    ],
    "tone_of_voice": "ودي، شبابي، احترافي"
  }},
  "estimated_metrics": {{
    "total_reach": "15,000-25,000",
    "engagement_rate": "4-6%",
    "estimated_cost": "$4.50",
    "generation_time": "3 minutes"
  }}
}}

⚠️ قواعد مهمة:
- اجعل الخطة واقعية ومفصلة
- Content briefs يجب أن تكون عملية وقابلة للتنفيذ
- اللغة حسب ما فهمته من الوصف (لا تضيف لغات غير مطلوبة)
- Sample posts يجب أن تكون أمثلة حقيقية جاهزة للاستخدام
"""
        
        try:
            logger.info(f"Starting comprehensive intelligence generation (timeout: {settings.AI_REQUEST_TIMEOUT}s)")
            import time
            start_time = time.time()
            
            response = await asyncio.wait_for(
                asyncio.to_thread(self.model.generate_content, prompt),
                timeout=settings.AI_REQUEST_TIMEOUT
            )
            
            elapsed_time = time.time() - start_time
            logger.info(f"Intelligence generation completed in {elapsed_time:.2f} seconds")
            
            if not response or not hasattr(response, 'text'):
                raise Exception("Invalid response from AI service")

            raw_text = getattr(response, 'text', '') or ''
            mode = getattr(request, 'mode', 'advanced') or 'advanced'

            logger.warning("AI planner raw response (full): %s", raw_text)

            try:
                result = extract_json_from_text(raw_text)
            except ValueError as parse_error:
                if str(mode).lower() == 'quick':
                    logger.warning("JSON parsing failed in quick mode; letting caller fallback", extra={"error": str(parse_error)})
                    raise Exception("AI returned invalid JSON") from parse_error

                logger.warning(
                    "Primary JSON parsing failed, attempting automatic repair",
                    extra={
                        "error": str(parse_error),
                        "snippet": raw_text[:400]
                    }
                )

                repair_prompt = f"""
النص التالي يفترض أن يكون JSON صالح لكنه يحتوي على أخطاء تنسيق.
قم بإصلاحه وأعده كـ JSON صالح فقط بدون أي نص إضافي.

النص:
```
{raw_text}
```
"""

                repair_response = await asyncio.wait_for(
                    asyncio.to_thread(self.model.generate_content, repair_prompt),
                    timeout=60
                )

                repaired_text = getattr(repair_response, 'text', '').strip()
                if not repaired_text:
                    raise Exception("Repair attempt returned empty response") from parse_error

                logger.warning("AI planner repair response (full): %s", repaired_text)

                result = extract_json_from_text(repaired_text)
                raw_text = repaired_text

            # Cache the result
            cache_service.set_cached_ai_result(cache_key, result, "intelligence", settings.CACHE_TTL)

            return result

        except asyncio.TimeoutError:
            raise Exception("AI request timeout - service took too long to respond")
        except Exception as e:
            snippet = raw_text[:400] if 'raw_text' in locals() else ''
            logger.error(f"Intelligence generation failed: {e}. Raw snippet: {snippet}")
            raise Exception(f"AI generation failed: {str(e)}")
    
    async def generate_preview(self, request):
        """Generate campaign structure preview - legacy method for compatibility"""
        # Call the new comprehensive method
        intelligence = await self.generate_campaign_intelligence(request)
        
        # Return in old format for backward compatibility
        return {
            "campaign_name": intelligence.get("executive_summary", {}).get("campaign_name", f"حملة {request.product_name}"),
            "structure": json.dumps(intelligence, ensure_ascii=False, indent=2),
            "estimated_posts": intelligence.get("executive_summary", {}).get("total_posts", 0),
            "platforms_breakdown": {platform: request.posts_per_week for platform in request.platforms},
            # New comprehensive data
            "intelligence": intelligence
        }
    
    async def suggest_colors(self, description):
        """Suggest color palettes based on description"""
        if not self.model:
            raise Exception("Google API key not configured")
        
        cache_key = f"colors:{description[:50]}"
        
        # Check cache
        cached_result = cache_service.get_cached_ai_result(cache_key, "colors")
        if cached_result:
            logger.info("Using cached color suggestion")
            return cached_result
        
        prompt = f"""
أنت مصمم جرافيك محترف. بناءً على الوصف التالي، اقترح 3 لوحات ألوان مناسبة:

الوصف: {description}

أرجع JSON بهذا الشكل:

{{
  "color_palettes": [
    {{
      "name": "اسم اللوحة",
      "primary_color": "#HEXCODE",
      "secondary_color": "#HEXCODE",
      "accent_color": "#HEXCODE",
      "reasoning": "السبب"
    }}
  ]
}}
"""
        
        try:
            response = await asyncio.wait_for(
                asyncio.to_thread(self.model.generate_content, prompt),
                timeout=30
            )
            
            if not response or not hasattr(response, 'text'):
                raise Exception("Invalid response from AI service")

            raw_text = getattr(response, 'text', '') or ''
            result = extract_json_from_text(raw_text)

            # Cache the result
            cache_service.set_cached_ai_result(cache_key, result, "colors", settings.CACHE_TTL)
            
            return result
            
        except Exception as e:
            logger.error("Color suggestion failed", extra={"error": str(e)})
            # Return default palettes
            return {
                "color_palettes": [
                    {
                        "name": "Professional Blue",
                        "primary_color": "#2563eb",
                        "secondary_color": "#64748b",
                        "accent_color": "#f59e0b",
                        "reasoning": "Professional and trustworthy"
                    },
                    {
                        "name": "Warm Orange",
                        "primary_color": "#ea580c",
                        "secondary_color": "#dc2626",
                        "accent_color": "#fbbf24",
                        "reasoning": "Energetic and engaging"
                    },
                    {
                        "name": "Nature Green",
                        "primary_color": "#16a34a",
                        "secondary_color": "#059669",
                        "accent_color": "#84cc16",
                        "reasoning": "Fresh and natural"
                    }
                ]
            }
