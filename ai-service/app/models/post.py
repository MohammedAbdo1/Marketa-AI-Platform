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

class DescriptionAnalysisRequest(BaseModel):
    description: str
    platform: Optional[str] = "instagram"
    business_type: Optional[str] = ""

class DescriptionAnalysisResponse(BaseModel):
    scene_description: str
    text_overlays: List[Dict[str, Any]]
    screen_content: Optional[str] = None
    objects_to_composite: List[Dict[str, Any]]
    image_style: str
    needs_composition: bool

class ComposedImageRequest(BaseModel):
    analysis: Dict[str, Any]
    size: Optional[str] = "1024x1024"

class ComposedImageResponse(BaseModel):
    final_image_url: str
    base_image_url: str
    layers: Dict[str, Any]
    dimensions: Dict[str, int]

class LayerRegenerationRequest(BaseModel):
    post_id: int
    layer_index: int
    changes: Dict[str, Any]