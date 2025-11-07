from fastapi import FastAPI, HTTPException, Request
from starlette.staticfiles import StaticFiles
from fastapi.middleware.cors import CORSMiddleware
import uvicorn
import sys
import os
import time
import logging
sys.path.append(os.path.dirname(os.path.dirname(os.path.abspath(__file__))))

from config import settings
from app.agents.planner import CampaignPlannerAgent
from app.agents.writer import ContentWriterAgent
from app.agents.image_gen import ImageGeneratorAgent
from app.models.campaign import CampaignPreviewRequest, CampaignPreviewResponse
from app.models.post import PostGenerationRequest, PostGenerationResponse

# Simple FastAPI app without Redis/Celery
app = FastAPI(
    title="Marketa AI Service - Simple Mode",
    description="AI-powered campaign generation service (without Redis)",
    version="1.0.0"
)
# Serve static files (generated images)
try:
    os.makedirs(os.path.join("app", "static"), exist_ok=True)
except Exception:
    pass
app.mount("/static", StaticFiles(directory="app/static"), name="static")

# CORS middleware
app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],  # Allow all origins for static files
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
    expose_headers=["*"]
)

# Global agents (lazy loaded)
planner_agent = None
writer_agent = None
image_agent = None

logger = logging.getLogger("uvicorn.error")

def get_planner_agent():
    global planner_agent
    if planner_agent is None:
        planner_agent = CampaignPlannerAgent()
    return planner_agent

def get_writer_agent():
    global writer_agent
    if writer_agent is None:
        writer_agent = ContentWriterAgent()
    return writer_agent

def get_image_agent():
    global image_agent
    if image_agent is None:
        image_agent = ImageGeneratorAgent()
    return image_agent

@app.get("/")
async def root():
    return {"message": "Marketa AI Service (Simple Mode) is running!", "status": "healthy"}

@app.get("/health")
async def health_check():
    return {
        "status": "healthy",
        "service": "Marketa AI Service - Simple Mode",
        "version": "1.0.0",
        "mode": "simple (no Redis)"
    }

@app.post("/api/campaign/intelligence")
async def generate_campaign_intelligence(request: CampaignPreviewRequest, raw: Request):
    """Generate comprehensive campaign intelligence"""
    try:
        rid = raw.headers.get("X-Request-ID", "-")
        t0 = time.time()
        logger.info({"stage": "py_received", "rid": rid, "endpoint": "/api/campaign/intelligence"})
        
        planner = get_planner_agent()
        intelligence = await planner.generate_campaign_intelligence(request)
        
        logger.info({"stage": "py_done", "rid": rid, "elapsed": round(time.time() - t0, 3)})
        return intelligence
    
    except Exception as e:
        logger.exception({"stage": "py_error", "rid": rid, "error": str(e)})
        raise HTTPException(status_code=500, detail=f"Intelligence generation failed: {str(e)}")

@app.post("/api/campaign/preview")
async def generate_campaign_preview(request: CampaignPreviewRequest, raw: Request):
    """Generate campaign structure preview"""
    try:
        rid = raw.headers.get("X-Request-ID", "-")
        t0 = time.time()
        logger.info({"stage": "py_received", "rid": rid, "endpoint": "/api/campaign/preview", "campaign_id": getattr(request, 'campaign_id', None)})
        planner = get_planner_agent()
        preview = await planner.generate_preview(request)
        logger.info({"stage": "py_done", "rid": rid, "elapsed": round(time.time() - t0, 3)})
        return CampaignPreviewResponse(**preview)
    
    except Exception as e:
        logger.exception({"stage": "py_error", "rid": rid, "error": str(e)})
        raise HTTPException(status_code=500, detail=f"Preview generation failed: {str(e)}")

@app.post("/api/campaign/generate")
async def generate_campaign(request: CampaignPreviewRequest, raw: Request):
    """Generate complete campaign"""
    try:
        rid = raw.headers.get("X-Request-ID", "-")
        t0 = time.time()
        logger.info({"stage": "py_received", "rid": rid, "endpoint": "/api/campaign/generate", "campaign_id": getattr(request, 'campaign_id', None)})
        planner = get_planner_agent()
        writer = get_writer_agent()
        image_agent = get_image_agent()
        
        # Generate campaign structure
        structure = await planner.generate_preview(request)
        
        # Generate posts
        posts = await writer.generate_posts(structure, request)
        
        # Generate images for posts with resilient fallback when provider fails or has no credits
        for idx, post in enumerate(posts):
            # Support object-like and dict-like posts
            needs_image = False
            image_prompt = None
            platform = None
            if hasattr(post, 'needs_image'):
                needs_image = bool(getattr(post, 'needs_image'))
                image_prompt = getattr(post, 'image_prompt', None)
                platform = getattr(post, 'platform', None)
            elif isinstance(post, dict):
                needs_image = bool(post.get('needs_image'))
                image_prompt = post.get('image_prompt')
                platform = post.get('platform')

            if needs_image:
                # Decide size by platform (must be increments of 64 for Stability)
                size_map = {
                    'instagram': '1024x1024',  # Square for IG
                    'facebook': '1216x640',    # ~1.9:1 ratio for FB
                    'linkedin': '1216x640',    # ~1.9:1 ratio for LinkedIn
                    'twitter': '1216x640',     # ~1.9:1 ratio for Twitter
                }
                size = size_map.get(str(platform or '').lower(), '1024x1024')
                logger.info({"stage": "py_image_request_sent", "rid": rid, "idx": idx, "platform": platform, "size": size, "prompt": (image_prompt or '')[:120]})
                try:
                    image_url = await image_agent.generate_image(
                        image_prompt or f"High quality social media image for {getattr(request,'product_name','product')} on {platform}",
                        size
                    )
                    if not image_url:
                        raise ValueError('Empty image_url from provider')
                    logger.info({"stage": "py_image_provider_done", "rid": rid, "idx": idx, "platform": platform, "url": image_url})
                except Exception as e:
                    logger.exception({"stage": "py_image_error", "rid": rid, "idx": idx, "platform": platform, "error": str(e), "repr": repr(e)})
                    # Re-raise the exception - NO fallback to random images
                    raise HTTPException(
                        status_code=500, 
                        detail=f"Image generation failed: {str(e)}. Please check Stability AI API configuration."
                    )

                if hasattr(post, 'image_url'):
                    post.image_url = image_url
                elif isinstance(post, dict):
                    post['image_url'] = image_url
        
        resp = {
            "campaign_id": request.campaign_id,
            "structure": structure,
            "posts": [post.dict() if hasattr(post, 'dict') else post for post in posts],
            "status": "completed"
        }
        logger.info({"stage": "py_done", "rid": rid, "elapsed": round(time.time() - t0, 3)})
        return resp
    
    except Exception as e:
        import traceback
        tb = traceback.format_exc()
        logger.error({"stage": "py_error", "rid": rid, "error": str(e), "type": type(e).__name__, "traceback": tb})
        raise HTTPException(status_code=500, detail=f"Campaign generation failed: {type(e).__name__}: {str(e)}")

@app.post("/api/post/regenerate-text")
async def regenerate_post_text(request: PostGenerationRequest, raw: Request):
    """Regenerate text for a specific post"""
    try:
        rid = raw.headers.get("X-Request-ID", "-")
        t0 = time.time()
        logger.info({"stage": "py_received", "rid": rid, "endpoint": "/api/post/regenerate-text", "post_id": request.post_id})
        writer = get_writer_agent()
        new_text = await writer.regenerate_post_text(request)
        resp = PostGenerationResponse(
            post_id=request.post_id,
            content_ar=new_text.get("content_ar"),
            content_en=new_text.get("content_en"),
            status="completed"
        )
        logger.info({"stage": "py_done", "rid": rid, "elapsed": round(time.time() - t0, 3)})
        return resp
    
    except Exception as e:
        logger.exception({"stage": "py_error", "rid": rid, "error": str(e)})
        raise HTTPException(status_code=500, detail=f"Text regeneration failed: {str(e)}")

@app.post("/api/post/regenerate-image")
async def regenerate_post_image(request: PostGenerationRequest, raw: Request):
    """Regenerate image for a specific post"""
    try:
        rid = raw.headers.get("X-Request-ID", "-")
        t0 = time.time()
        logger.info({"stage": "py_received", "rid": rid, "endpoint": "/api/post/regenerate-image", "post_id": request.post_id})
        image_agent = get_image_agent()
        new_image_url = await image_agent.generate_image(request.image_prompt)
        resp = PostGenerationResponse(
            post_id=request.post_id,
            image_url=new_image_url,
            status="completed"
        )
        logger.info({"stage": "py_done", "rid": rid, "elapsed": round(time.time() - t0, 3)})
        return resp
    
    except Exception as e:
        logger.exception({"stage": "py_error", "rid": rid, "error": str(e)})
        raise HTTPException(status_code=500, detail=f"Image regeneration failed: {str(e)}")

@app.post("/api/brand/suggest-colors")
async def suggest_brand_colors(request: dict):
    """Suggest color palettes based on product description"""
    try:
        planner = get_planner_agent()
        colors = await planner.suggest_colors(request.get("description", ""))
        return {"color_palettes": colors}
    
    except Exception as e:
        raise HTTPException(status_code=500, detail=f"Color suggestion failed: {str(e)}")

# ─────────────────────────────────────────────────────────────────────────────
# Diagnostics endpoints (simple connectivity checks)
# ─────────────────────────────────────────────────────────────────────────────
@app.post("/api/test/text")
async def test_text(request: dict, raw: Request):
    """Echo/improve text to verify connectivity."""
    try:
        rid = raw.headers.get("X-Request-ID", "-")
        t0 = time.time()
        logger.info({"stage": "py_received", "rid": rid, "endpoint": "/api/test/text"})
        text = request.get("text", "").strip()
        if not text:
            raise HTTPException(status_code=422, detail="'text' is required")

        writer = get_writer_agent()
        # If writer has a simple improve method, use it; otherwise return a basic transformation
        if hasattr(writer, "improve_text"):
            improved = await writer.improve_text(text)
        else:
            improved = f"محسّن: {text}"

        resp = {"input": text, "improved": improved, "ok": True}
        logger.info({"stage": "py_done", "rid": rid, "elapsed": round(time.time() - t0, 3)})
        return resp
    except HTTPException:
        raise
    except Exception as e:
        logger.exception({"stage": "py_error", "rid": rid, "error": str(e)})
        raise HTTPException(status_code=500, detail=f"Text test failed: {str(e)}")

@app.post("/api/test/image")
async def test_image(request: dict, raw: Request):
    """Generate a test image URL to verify connectivity."""
    try:
        rid = raw.headers.get("X-Request-ID", "-")
        t0 = time.time()
        logger.info({"stage": "py_received", "rid": rid, "endpoint": "/api/test/image"})
        prompt = request.get("prompt", "").strip() or "abstract colorful gradient background"
        image_agent = get_image_agent()
        if hasattr(image_agent, "generate_image"):
            url = await image_agent.generate_image(prompt)
        else:
            raise HTTPException(
                status_code=500,
                detail="Image generation is not available. Please configure Stability AI API."
            )
        resp = {"prompt": prompt, "image_url": url, "ok": True}
        logger.info({"stage": "py_done", "rid": rid, "elapsed": round(time.time() - t0, 3)})
        return resp
    except Exception as e:
        logger.exception({"stage": "py_error", "rid": rid, "error": str(e)})
        raise HTTPException(status_code=500, detail=f"Image test failed: {str(e)}")


@app.post("/api/ai/conversation/message")
async def process_conversation_message(raw: Request):
    """
    Process user message in AI conversation and generate design images
    """
    rid = raw.headers.get("X-Request-ID", "-")
    try:
        logger.info({"stage": "ai_conversation_received", "rid": rid})
        
        request = await raw.json()
        content = request.get("content", "").strip()
        design_type = request.get("design_type", "social_post")
        conversation_id = request.get("conversation_id", "")
        
        if not content:
            raise HTTPException(status_code=400, detail="Message content is required")
        
        # Generate ONLY ONE design (as per user requirement)
        image_agent = get_image_agent()
        images = []
        
        try:
            # Generate single image
            image_url = await image_agent.generate_image(content)
            
            images.append({
                "url": image_url,
                "title": "تصميم 1",
                "provider": getattr(image_agent, 'last_provider', 'pollinations')
            })
            
            logger.info({
                "stage": "image_generated", 
                "rid": rid,
                "url": image_url
            })
        except Exception as img_error:
            logger.error({
                "stage": "image_generation_failed", 
                "rid": rid,
                "error": str(img_error)
            })
        
        # Prepare response
        if len(images) > 0:
            response_message = "تم! إليك تصميمك للسوشيال ميديا 🎨"
        else:
            response_message = "عذراً، حدثت مشكلة في توليد التصميم. حاول مرة أخرى"
        
        response = {
            "response": response_message,
            "images": images,
            "suggestions": [
                "أضف المزيد من التفاصيل",
                "غير الألوان",
                "جرب نمط مختلف",
                "أضف صورة ماكينة خياطة حديثة",
                "بألوان عصرية",
                "المزيد من التصاميم"
            ],
            "metadata": {
                "prompt_used": content,
                "design_type": design_type,
                "images_count": len(images),
                "timestamp": time.time()
            }
        }
        
        logger.info({
            "stage": "ai_conversation_done", 
            "rid": rid, 
            "images_generated": len(images)
        })
        return response
        
    except Exception as e:
        logger.exception({"stage": "ai_conversation_error", "rid": rid, "error": str(e)})
        raise HTTPException(status_code=500, detail=f"Conversation failed: {str(e)}")


if __name__ == "__main__":
    uvicorn.run(
        "main_simple:app",
        host=settings.HOST,
        port=settings.PORT,
        reload=settings.DEBUG
    )






