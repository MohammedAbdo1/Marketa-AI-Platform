from fastapi import FastAPI, HTTPException, Depends, Request, Response
from fastapi.middleware.cors import CORSMiddleware
from contextlib import asynccontextmanager
import uvicorn
import sys
import os
sys.path.append(os.path.dirname(os.path.dirname(os.path.abspath(__file__))))

from config import settings
from app.models.campaign import CampaignPreviewRequest, CampaignPreviewResponse
from app.models.post import PostGenerationRequest, PostGenerationResponse
from app.tasks.campaign_tasks import (
    generate_campaign_task,
    generate_preview_task,
    regenerate_text_task,
    regenerate_image_task,
    suggest_colors_task
)
from app.services.rate_limiter import rate_limiter
from app.ws_manager import sio, start_websocket_cleanup, stop_websocket_cleanup
import socketio

@asynccontextmanager
async def lifespan(app: FastAPI):
    # Startup
    print("🚀 Initializing AI Service...")
    await start_websocket_cleanup()
    print("✅ AI Service initialized successfully!")
    
    yield
    
    # Shutdown
    print("🛑 Shutting down AI Service...")
    await stop_websocket_cleanup()

app = FastAPI(
    title="Marketa AI Service",
    description="AI-powered campaign generation service",
    version="1.0.0",
    lifespan=lifespan
)

# Mount Socket.IO with different approach
sio_app = socketio.ASGIApp(sio, app, socketio_path='socket.io')

# CORS middleware
app.add_middleware(
    CORSMiddleware,
    allow_origins=["http://localhost:5173", "http://localhost:8000", "http://localhost:3000", "http://localhost:3001"],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

# Rate limiting middleware
@app.middleware("http")
async def rate_limit_middleware(request: Request, call_next):
    # Get user ID from request (in production, this would come from JWT token)
    user_id = request.headers.get("X-User-ID", "anonymous")
    
    # Check if endpoint has rate limiting
    if rate_limiter.is_endpoint_limited(request.url.path):
        is_allowed, rate_info = rate_limiter.is_allowed(user_id, request.url.path)
        
        if not is_allowed:
            response = Response(
                content='{"error": "Rate limit exceeded", "message": "Too many requests"}',
                status_code=429,
                media_type="application/json"
            )
            # Add rate limit headers
            headers = rate_limiter.get_rate_limit_headers(user_id, request.url.path)
            for key, value in headers.items():
                response.headers[key] = value
            return response
    
    # Process request
    response = await call_next(request)
    
    # Add rate limit headers to response
    if rate_limiter.is_endpoint_limited(request.url.path):
        headers = rate_limiter.get_rate_limit_headers(user_id, request.url.path)
        for key, value in headers.items():
            response.headers[key] = value
    
    return response

@app.get("/")
async def root():
    return {"message": "Marketa AI Service is running!", "status": "healthy"}

@app.get("/health")
async def health_check():
    return {
        "status": "healthy",
        "service": "Marketa AI Service",
        "version": "1.0.0"
    }

@app.post("/api/campaign/preview")
async def generate_campaign_preview(request: CampaignPreviewRequest):
    """Generate campaign structure preview"""
    try:
        # Start async task
        task = generate_preview_task.delay(request.dict())
        
        return {
            "task_id": task.id,
            "status": "processing",
            "message": "Campaign preview generation started"
        }
    
    except Exception as e:
        raise HTTPException(status_code=500, detail=f"Preview generation failed: {str(e)}")

@app.post("/api/campaign/generate")
async def generate_campaign(request: CampaignPreviewRequest):
    """Start full campaign generation"""
    try:
        # Start async task
        task = generate_campaign_task.delay(request.dict())
        
        return {
            "task_id": task.id,
            "status": "processing",
            "message": "Campaign generation started"
        }
    
    except Exception as e:
        raise HTTPException(status_code=500, detail=f"Campaign generation failed: {str(e)}")

@app.post("/api/post/regenerate-text")
async def regenerate_post_text(request: PostGenerationRequest):
    """Regenerate text for a specific post"""
    try:
        # Start async task
        task = regenerate_text_task.delay(request.dict())
        
        return {
            "task_id": task.id,
            "status": "processing",
            "message": "Text regeneration started"
        }
    
    except Exception as e:
        raise HTTPException(status_code=500, detail=f"Text regeneration failed: {str(e)}")

@app.post("/api/post/regenerate-image")
async def regenerate_post_image(request: PostGenerationRequest):
    """Regenerate image for a specific post"""
    try:
        # Start async task
        task = regenerate_image_task.delay(request.dict())
        
        return {
            "task_id": task.id,
            "status": "processing",
            "message": "Image regeneration started"
        }
    
    except Exception as e:
        raise HTTPException(status_code=500, detail=f"Image regeneration failed: {str(e)}")

@app.post("/api/brand/suggest-colors")
async def suggest_brand_colors(request: dict):
    """Suggest color palettes based on product description"""
    try:
        # Start async task
        task = suggest_colors_task.delay(request.get("description", ""))
        
        return {
            "task_id": task.id,
            "status": "processing",
            "message": "Color suggestion started"
        }
    
    except Exception as e:
        raise HTTPException(status_code=500, detail=f"Color suggestion failed: {str(e)}")

# Task Status and Result Endpoints
@app.get("/api/task/status/{task_id}")
async def get_task_status(task_id: str):
    """Get the status of a task"""
    try:
        from celery_app import celery_app
        task_result = celery_app.AsyncResult(task_id)
        
        if task_result.state == 'PENDING':
            return {
                "task_id": task_id,
                "status": "pending",
                "message": "Task is waiting to be processed"
            }
        elif task_result.state == 'PROGRESS':
            return {
                "task_id": task_id,
                "status": "processing",
                "progress": task_result.info.get('progress', 0),
                "message": task_result.info.get('status', 'Processing...')
            }
        elif task_result.state == 'SUCCESS':
            return {
                "task_id": task_id,
                "status": "completed",
                "progress": 100,
                "message": "Task completed successfully"
            }
        elif task_result.state == 'FAILURE':
            return {
                "task_id": task_id,
                "status": "failed",
                "message": str(task_result.info)
            }
        else:
            return {
                "task_id": task_id,
                "status": task_result.state,
                "message": "Unknown status"
            }
    
    except Exception as e:
        raise HTTPException(status_code=500, detail=f"Failed to get task status: {str(e)}")

@app.get("/api/task/result/{task_id}")
async def get_task_result(task_id: str):
    """Get the result of a completed task"""
    try:
        from celery_app import celery_app
        task_result = celery_app.AsyncResult(task_id)
        
        if task_result.state == 'SUCCESS':
            return {
                "task_id": task_id,
                "status": "completed",
                "result": task_result.result
            }
        elif task_result.state == 'FAILURE':
            return {
                "task_id": task_id,
                "status": "failed",
                "error": str(task_result.info)
            }
        else:
            return {
                "task_id": task_id,
                "status": task_result.state,
                "message": "Task is not completed yet"
            }
    
    except Exception as e:
        raise HTTPException(status_code=500, detail=f"Failed to get task result: {str(e)}")

# Health check endpoints
@app.get("/health/redis")
async def health_check_redis():
    """Check Redis connection"""
    try:
        import redis
        r = redis.from_url(settings.REDIS_URL)
        r.ping()
        return {"status": "healthy", "service": "Redis"}
    except Exception as e:
        return {"status": "unhealthy", "service": "Redis", "error": str(e)}

@app.get("/health/workers")
async def health_check_workers():
    """Check Celery workers status"""
    try:
        from celery_app import celery_app
        # Use inspect instead of control.stats
        inspect = celery_app.control.inspect()
        stats = inspect.stats()
        return {
            "status": "healthy", 
            "service": "Celery Workers",
            "active_workers": len(stats) if stats else 0,
            "workers": list(stats.keys()) if stats else []
        }
    except Exception as e:
        return {"status": "unhealthy", "service": "Celery Workers", "error": str(e)}

# Rate limiting endpoints
@app.get("/api/rate-limit/stats/{user_id}")
async def get_rate_limit_stats(user_id: str):
    """Get rate limit statistics for a user"""
    try:
        stats = rate_limiter.get_user_stats(user_id)
        return stats
    except Exception as e:
        raise HTTPException(status_code=500, detail=f"Failed to get rate limit stats: {str(e)}")

@app.post("/api/rate-limit/reset/{user_id}")
async def reset_rate_limits(user_id: str):
    """Reset rate limits for a user (admin function)"""
    try:
        success = rate_limiter.reset_user_limits(user_id)
        if success:
            return {"message": f"Rate limits reset for user {user_id}"}
        else:
            raise HTTPException(status_code=500, detail="Failed to reset rate limits")
    except Exception as e:
        raise HTTPException(status_code=500, detail=f"Failed to reset rate limits: {str(e)}")

if __name__ == "__main__":
    uvicorn.run(
        "main:app",
        host=settings.HOST,
        port=settings.PORT,
        reload=settings.DEBUG
    )
