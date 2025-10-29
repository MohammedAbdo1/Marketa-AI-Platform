from pydantic import BaseModel
from typing import List, Optional, Dict, Any

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