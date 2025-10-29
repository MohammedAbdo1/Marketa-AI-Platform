from fastapi import FastAPI, HTTPException
from fastapi.middleware.cors import CORSMiddleware
import uvicorn
import sys
import os
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

# CORS middleware
app.add_middleware(
    CORSMiddleware,
    allow_origins=["http://localhost:5173", "http://localhost:8000"],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

# Global agents (lazy loaded)
planner_agent = None
writer_agent = None
image_agent = None

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

@app.post("/api/campaign/preview")
async def generate_campaign_preview(request: CampaignPreviewRequest):
    """Generate campaign structure preview"""
    try:
        planner = get_planner_agent()
        preview = await planner.generate_preview(request)
        return CampaignPreviewResponse(**preview)
    
    except Exception as e:
        raise HTTPException(status_code=500, detail=f"Preview generation failed: {str(e)}")

@app.post("/api/campaign/generate")
async def generate_campaign(request: CampaignPreviewRequest):
    """Generate complete campaign"""
    try:
        planner = get_planner_agent()
        writer = get_writer_agent()
        image_agent = get_image_agent()
        
        # Generate campaign structure
        structure = await planner.generate_preview(request)
        
        # Generate posts
        posts = await writer.generate_posts(structure, request)
        
        # Generate images for posts
        for post in posts:
            if hasattr(post, 'needs_image') and post.needs_image:
                image_url = await image_agent.generate_image(post.image_prompt)
                post.image_url = image_url
        
        return {
            "campaign_id": request.campaign_id,
            "structure": structure,
            "posts": [post.dict() if hasattr(post, 'dict') else post for post in posts],
            "status": "completed"
        }
    
    except Exception as e:
        raise HTTPException(status_code=500, detail=f"Campaign generation failed: {str(e)}")

@app.post("/api/post/regenerate-text")
async def regenerate_post_text(request: PostGenerationRequest):
    """Regenerate text for a specific post"""
    try:
        writer = get_writer_agent()
        new_text = await writer.regenerate_post_text(request)
        return PostGenerationResponse(
            post_id=request.post_id,
            content_ar=new_text.get("content_ar"),
            content_en=new_text.get("content_en"),
            status="completed"
        )
    
    except Exception as e:
        raise HTTPException(status_code=500, detail=f"Text regeneration failed: {str(e)}")

@app.post("/api/post/regenerate-image")
async def regenerate_post_image(request: PostGenerationRequest):
    """Regenerate image for a specific post"""
    try:
        image_agent = get_image_agent()
        new_image_url = await image_agent.generate_image(request.image_prompt)
        return PostGenerationResponse(
            post_id=request.post_id,
            image_url=new_image_url,
            status="completed"
        )
    
    except Exception as e:
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

if __name__ == "__main__":
    uvicorn.run(
        "main_simple:app",
        host=settings.HOST,
        port=settings.PORT,
        reload=settings.DEBUG
    )






