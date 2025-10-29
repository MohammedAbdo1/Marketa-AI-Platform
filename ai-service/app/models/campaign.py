from pydantic import BaseModel
from typing import List, Optional, Dict, Any

class CampaignPreviewRequest(BaseModel):
    campaign_id: Optional[int] = None
    business_type: str
    product_name: str
    description: str
    goal: str
    target_audience: Dict[str, Any]
    platforms: List[str]
    duration_days: int
    posts_per_week: int
    tone_of_voice: Optional[str] = "friendly"
    languages: Optional[List[str]] = ["ar", "en"]

class CampaignPreviewResponse(BaseModel):
    campaign_name: str
    structure: str
    estimated_posts: int
    platforms_breakdown: Dict[str, int]
    error: Optional[str] = None

class PostGenerationRequest(BaseModel):
    post_id: int
    content_ar: Optional[str] = None
    content_en: Optional[str] = None
    image_prompt: Optional[str] = None

class PostGenerationResponse(BaseModel):
    post_id: int
    content_ar: Optional[str] = None
    content_en: Optional[str] = None
    image_url: Optional[str] = None
    status: str