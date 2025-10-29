from celery import current_task
from celery_app import celery_app
from app.agents.planner import CampaignPlannerAgent
from app.agents.writer import ContentWriterAgent
from app.agents.image_gen import ImageGeneratorAgent
from app.agents.reviewer import QualityReviewerAgent
from app.models.campaign import CampaignPreviewRequest
from app.models.post import PostGenerationRequest
from app.ws_manager import send_task_update, send_task_completion, send_task_failure, send_task_progress
import asyncio
import json
from typing import Dict, Any, List

# Global agents (lazy loaded)
_planner_agent = None
_writer_agent = None
_image_agent = None
_reviewer_agent = None

def get_planner_agent():
    global _planner_agent
    if _planner_agent is None:
        _planner_agent = CampaignPlannerAgent()
    return _planner_agent

def get_writer_agent():
    global _writer_agent
    if _writer_agent is None:
        _writer_agent = ContentWriterAgent()
    return _writer_agent

def get_image_agent():
    global _image_agent
    if _image_agent is None:
        _image_agent = ImageGeneratorAgent()
    return _image_agent

def get_reviewer_agent():
    global _reviewer_agent
    if _reviewer_agent is None:
        _reviewer_agent = QualityReviewerAgent()
    return _reviewer_agent

@celery_app.task(bind=True, name='app.tasks.campaign_tasks.generate_campaign_task')
def generate_campaign_task(self, request_data: Dict[str, Any]):
    """
    Generate complete campaign with all posts and images
    """
    try:
        # Update task state
        self.update_state(
            state='PROGRESS',
            meta={'status': 'Starting campaign generation...', 'progress': 0}
        )
        
        # Send WebSocket update
        # asyncio.run(send_task_progress(self.request.id, 0, "Starting campaign generation..."))
        
        # Parse request
        request = CampaignPreviewRequest(**request_data)
        
        # Step 1: Generate campaign structure (25%)
        self.update_state(
            state='PROGRESS',
            meta={'status': 'Generating campaign structure...', 'progress': 25}
        )
        # asyncio.run(send_task_progress(self.request.id, 25, "Generating campaign structure..."))
        
        planner = get_planner_agent()
        structure = asyncio.run(planner.generate_preview(request))
        
        # Step 2: Generate posts (50%)
        self.update_state(
            state='PROGRESS',
            meta={'status': 'Generating campaign posts...', 'progress': 50}
        )
        # asyncio.run(send_task_progress(self.request.id, 50, "Generating campaign posts..."))
        
        writer = get_writer_agent()
        posts = asyncio.run(writer.generate_posts(structure, request))
        
        # Step 3: Generate images (75%)
        self.update_state(
            state='PROGRESS',
            meta={'status': 'Generating images...', 'progress': 75}
        )
        # asyncio.run(send_task_progress(self.request.id, 75, "Generating images..."))
        
        image_agent = get_image_agent()
        for post in posts:
            if hasattr(post, 'needs_image') and post.needs_image:
                image_url = asyncio.run(image_agent.generate_image(post.image_prompt))
                post.image_url = image_url
        
        # Step 4: Review and finalize (100%)
        self.update_state(
            state='PROGRESS',
            meta={'status': 'Finalizing campaign...', 'progress': 100}
        )
        # asyncio.run(send_task_progress(self.request.id, 100, "Finalizing campaign..."))
        
        reviewer = get_reviewer_agent()
        reviewed_posts = asyncio.run(reviewer.review_posts(posts, request))
        
        # Return final result
        result = {
            "campaign_id": request.campaign_id,
            "structure": structure,
            "posts": [post.dict() if hasattr(post, 'dict') else post for post in reviewed_posts],
            "status": "completed",
            "total_posts": len(reviewed_posts)
        }
        
        # Send completion notification
        # asyncio.run(send_task_completion(self.request.id, result))
        
        return result
        
    except Exception as e:
        # Update task state with error
        self.update_state(
            state='FAILURE',
            meta={'status': f'Campaign generation failed: {str(e)}', 'progress': 0}
        )
        # asyncio.run(send_task_failure(self.request.id, str(e)))
        raise e

@celery_app.task(bind=True, name='app.tasks.campaign_tasks.generate_preview_task')
def generate_preview_task(self, request_data: Dict[str, Any]):
    """
    Generate campaign preview/structure only
    """
    import logging
    logger = logging.getLogger(__name__)
    
    try:
        logger.info(f"Preview task started: {self.request.id}")
        self.update_state(
            state='PROGRESS',
            meta={'status': 'Generating campaign preview...', 'progress': 10}
        )
        
        request = CampaignPreviewRequest(**request_data)
        logger.info(f"Request parsed successfully: {request.product_name}")
        
        self.update_state(
            state='PROGRESS',
            meta={'status': 'Initializing AI planner...', 'progress': 20}
        )
        planner = get_planner_agent()
        
        self.update_state(
            state='PROGRESS',
            meta={'status': 'Calling Google API...', 'progress': 30}
        )
        logger.info("Calling planner.generate_preview...")
        preview = asyncio.run(planner.generate_preview(request))
        logger.info("planner.generate_preview completed")
        
        # Check if result contains error (planner returns error dict instead of raising exception)
        if isinstance(preview, dict) and 'error' in preview:
            error_msg = preview.get('error', 'Preview generation failed')
            logger.error(f"Preview generation failed: {error_msg}")
            self.update_state(
                state='FAILURE',
                meta={'status': f'Preview generation failed: {error_msg}', 'progress': 0}
            )
            raise Exception(error_msg)
        
        self.update_state(
            state='PROGRESS',
            meta={'status': 'Preview generated successfully', 'progress': 100}
        )
        logger.info(f"Preview task completed successfully: {self.request.id}")
        
        return preview
        
    except Exception as e:
        self.update_state(
            state='FAILURE',
            meta={'status': f'Preview generation failed: {str(e)}', 'progress': 0}
        )
        raise e

@celery_app.task(bind=True, name='app.tasks.campaign_tasks.regenerate_text_task')
def regenerate_text_task(self, request_data: Dict[str, Any]):
    """
    Regenerate text for a specific post
    """
    try:
        self.update_state(
            state='PROGRESS',
            meta={'status': 'Regenerating post text...', 'progress': 50}
        )
        
        request = PostGenerationRequest(**request_data)
        writer = get_writer_agent()
        new_text = asyncio.run(writer.regenerate_post_text(request))
        
        self.update_state(
            state='PROGRESS',
            meta={'status': 'Text regenerated successfully', 'progress': 100}
        )
        
        return new_text
        
    except Exception as e:
        self.update_state(
            state='FAILURE',
            meta={'status': f'Text regeneration failed: {str(e)}', 'progress': 0}
        )
        raise e

@celery_app.task(bind=True, name='app.tasks.campaign_tasks.regenerate_image_task')
def regenerate_image_task(self, request_data: Dict[str, Any]):
    """
    Regenerate image for a specific post
    """
    try:
        self.update_state(
            state='PROGRESS',
            meta={'status': 'Regenerating post image...', 'progress': 50}
        )
        
        request = PostGenerationRequest(**request_data)
        image_agent = get_image_agent()
        new_image_url = asyncio.run(image_agent.generate_image(request.image_prompt))
        
        self.update_state(
            state='PROGRESS',
            meta={'status': 'Image regenerated successfully', 'progress': 100}
        )
        
        return {
            "post_id": request.post_id,
            "image_url": new_image_url,
            "status": "completed"
        }
        
    except Exception as e:
        self.update_state(
            state='FAILURE',
            meta={'status': f'Image regeneration failed: {str(e)}', 'progress': 0}
        )
        raise e

@celery_app.task(bind=True, name='app.tasks.campaign_tasks.suggest_colors_task')
def suggest_colors_task(self, description: str):
    """
    Suggest color palettes based on description
    """
    try:
        self.update_state(
            state='PROGRESS',
            meta={'status': 'Analyzing description and suggesting colors...', 'progress': 50}
        )
        
        planner = get_planner_agent()
        colors = asyncio.run(planner.suggest_colors(description))
        
        self.update_state(
            state='PROGRESS',
            meta={'status': 'Color suggestions generated', 'progress': 100}
        )
        
        return colors
        
    except Exception as e:
        self.update_state(
            state='FAILURE',
            meta={'status': f'Color suggestion failed: {str(e)}', 'progress': 0}
        )
        raise e
