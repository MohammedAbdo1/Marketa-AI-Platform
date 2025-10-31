"""
Image Compositor Service
Composes complex images with text overlays, screen mockups, and multiple layers
Supports Arabic (RTL) and English (LTR) text rendering
"""
import os
from PIL import Image, ImageDraw, ImageFont, ImageFilter, ImageEnhance
import arabic_reshaper
from bidi.algorithm import get_display
import numpy as np
import cv2
from typing import Dict, Any, List, Optional, Tuple
from dataclasses import dataclass
import io
from config import settings
from .storage import storage


@dataclass
class ComposedImage:
    """Result of image composition"""
    final_image: bytes  # PNG bytes
    layers: Dict[str, Any]  # JSON structure
    base_image_url: str
    dimensions: Tuple[int, int]  # (width, height)
    
    def to_json(self) -> Dict[str, Any]:
        """Export to JSON format"""
        return {
            "base_image_url": self.base_image_url,
            "layers": self.layers["layers"],
            "dimensions": {
                "width": self.dimensions[0],
                "height": self.dimensions[1]
            }
        }


class ImageCompositor:
    """
    Main service for composing images with multiple layers
    Handles text overlays, perspective transforms, and complex compositions
    """
    
    def __init__(self):
        """Initialize compositor with fonts and settings"""
        self.fonts_path = settings.TEXT_OVERLAY_FONTS_PATH
        self.arabic_font_name = settings.DEFAULT_ARABIC_FONT
        self.english_font_name = settings.DEFAULT_ENGLISH_FONT
        
        # Load fonts
        self._load_fonts()
        
        print("[ImageCompositor] Service initialized")
    
    def _load_fonts(self):
        """Load Arabic and English fonts"""
        self.fonts = {}
        
        # Try to load fonts
        for size in [24, 32, 48, 64, 96]:
            try:
                # Arabic font
                arabic_path = os.path.join(self.fonts_path, self.arabic_font_name)
                if os.path.exists(arabic_path):
                    self.fonts[f'arabic_{size}'] = ImageFont.truetype(arabic_path, size)
                
                # English font
                english_path = os.path.join(self.fonts_path, self.english_font_name)
                if os.path.exists(english_path):
                    self.fonts[f'english_{size}'] = ImageFont.truetype(english_path, size)
            except Exception as e:
                print(f"[ImageCompositor] Warning: Could not load font size {size}: {e}")
        
        # Fallback to default font if no fonts loaded
        if not self.fonts:
            print("[ImageCompositor] Warning: Using default font")
            for size in [24, 32, 48, 64, 96]:
                try:
                    self.fonts[f'default_{size}'] = ImageFont.truetype("arial.ttf", size)
                except:
                    self.fonts[f'default_{size}'] = ImageFont.load_default()
    
    async def compose_image(self, analysis: Dict[str, Any], 
                           base_image_bytes: bytes) -> ComposedImage:
        """
        Main composition function
        Takes analysis from CompositionAnalyzerAgent and base image, returns composed result
        
        Args:
            analysis: Structured analysis with text_overlays, screen_content, etc.
            base_image_bytes: Base image as bytes
            
        Returns:
            ComposedImage object with final image and layers
        """
        print("[ImageCompositor] Starting composition...")
        
        # Load base image
        base_image = Image.open(io.BytesIO(base_image_bytes)).convert("RGBA")
        width, height = base_image.size
        print(f"[ImageCompositor] Base image: {width}x{height}")
        
        # Create working copy
        composed = base_image.copy()
        
        # Track layers for editing
        layers = {
            "base_image_url": "",  # Will be set after saving
            "layers": []
        }
        
        # Add text overlays
        for overlay in analysis.get("text_overlays", []):
            print(f"[ImageCompositor] Adding text overlay: {overlay.get('text', '')[:50]}")
            composed = await self._add_text_overlay(
                composed, 
                overlay,
                layers["layers"]
            )
        
        # Convert to RGB for JPEG/PNG export
        if composed.mode == 'RGBA':
            background = Image.new('RGB', composed.size, (255, 255, 255))
            background.paste(composed, mask=composed.split()[3])
            composed = background
        
        # Convert to bytes
        output = io.BytesIO()
        composed.save(output, format='PNG', quality=95, optimize=True)
        final_bytes = output.getvalue()
        
        print(f"[ImageCompositor] Composition complete: {len(final_bytes)} bytes")
        
        # Save base image to storage (for future editing)
        import uuid
        base_filename = f"base_{uuid.uuid4().hex}.png"
        base_url = await storage.save_image(base_image_bytes, base_filename, "image/png")
        layers["base_image_url"] = base_url
        
        return ComposedImage(
            final_image=final_bytes,
            layers=layers,
            base_image_url=base_url,
            dimensions=(width, height)
        )
    
    async def _add_text_overlay(self, image: Image.Image, 
                                overlay: Dict[str, Any],
                                layers_list: List[Dict]) -> Image.Image:
        """
        Add text overlay on image
        Supports Arabic (RTL) and English (LTR)
        """
        text = overlay.get("text", "")
        position = overlay.get("position", "bottom-center")
        color = overlay.get("color", "#FFFFFF")
        font_size = overlay.get("font_size", 48)
        font_weight = overlay.get("font_weight", "bold")
        language = overlay.get("language", "en")
        
        # Normalize color
        if isinstance(color, str):
            if not color.startswith("#"):
                # Named colors
                color_map = {
                    "red": "#FF0000", "blue": "#0000FF", "green": "#00FF00",
                    "yellow": "#FFFF00", "white": "#FFFFFF", "black": "#000000",
                    "الأحمر": "#FF0000", "الأزرق": "#0000FF", "الأخضر": "#00FF00"
                }
                color = color_map.get(color.lower(), "#FFFFFF")
        
        # Normalize font size
        if isinstance(font_size, str):
            size_map = {"small": 32, "medium": 48, "large": 64, "extra-large": 96}
            font_size = size_map.get(font_size, 48)
        else:
            font_size = int(font_size)
        
        # Get appropriate font
        font = self._get_font(language, font_size)
        
        # Prepare text for rendering
        if language == "ar":
            # Arabic: reshape and apply bidi
            reshaped_text = arabic_reshaper.reshape(text)
            display_text = get_display(reshaped_text)
        else:
            display_text = text
        
        # Calculate text position
        draw = ImageDraw.Draw(image)
        
        # Get text bounding box
        try:
            bbox = draw.textbbox((0, 0), display_text, font=font)
            text_width = bbox[2] - bbox[0]
            text_height = bbox[3] - bbox[1]
        except:
            # Fallback for older Pillow versions
            text_width, text_height = draw.textsize(display_text, font=font)
        
        # Calculate position
        x, y = self._calculate_position(
            position, 
            image.size, 
            (text_width, text_height)
        )
        
        # Add shadow for better readability
        shadow_offset = 2
        draw.text(
            (x + shadow_offset, y + shadow_offset),
            display_text,
            font=font,
            fill=(0, 0, 0, 128)  # Semi-transparent black shadow
        )
        
        # Add main text
        draw.text(
            (x, y),
            display_text,
            font=font,
            fill=self._hex_to_rgb(color)
        )
        
        # Track layer for editing
        layers_list.append({
            "type": "text",
            "content": text,
            "position": {
                "x": x,
                "y": y,
                "alignment": position
            },
            "style": {
                "color": color,
                "fontSize": font_size,
                "fontWeight": font_weight,
                "language": language
            }
        })
        
        return image
    
    def _get_font(self, language: str, size: int) -> ImageFont.FreeTypeFont:
        """Get appropriate font for language and size"""
        # Find closest size available
        available_sizes = [24, 32, 48, 64, 96]
        closest_size = min(available_sizes, key=lambda x: abs(x - size))
        
        # Try to get language-specific font
        font_key = f"{language}_{closest_size}"
        if font_key in self.fonts:
            return self.fonts[font_key]
        
        # Fallback to default
        default_key = f"default_{closest_size}"
        if default_key in self.fonts:
            return self.fonts[default_key]
        
        # Last resort
        return ImageFont.load_default()
    
    def _calculate_position(self, position: str, 
                           image_size: Tuple[int, int],
                           text_size: Tuple[int, int]) -> Tuple[int, int]:
        """Calculate x, y coordinates based on position string"""
        img_width, img_height = image_size
        text_width, text_height = text_size
        
        # Parse position
        position = position.lower()
        
        # Horizontal alignment
        if "left" in position:
            x = 50  # Padding from left
        elif "right" in position:
            x = img_width - text_width - 50
        else:  # center
            x = (img_width - text_width) // 2
        
        # Vertical alignment
        if "top" in position:
            y = 50  # Padding from top
        elif "bottom" in position:
            y = img_height - text_height - 50
        else:  # center
            y = (img_height - text_height) // 2
        
        return (x, y)
    
    def _hex_to_rgb(self, hex_color: str) -> Tuple[int, int, int]:
        """Convert hex color to RGB tuple"""
        hex_color = hex_color.lstrip('#')
        return tuple(int(hex_color[i:i+2], 16) for i in (0, 2, 4))
    
    async def add_screen_mockup(self, base_image: Image.Image, 
                               mockup_bytes: bytes,
                               position: str = "center-screen") -> Image.Image:
        """
        Add screen mockup with perspective transform
        Uses OpenCV for advanced transformations
        """
        # Convert PIL to numpy for OpenCV
        base_np = np.array(base_image)
        mockup = Image.open(io.BytesIO(mockup_bytes))
        mockup_np = np.array(mockup)
        
        # TODO: Implement perspective transform with OpenCV
        # For now, simple overlay
        base_image.paste(mockup, (100, 100), mockup if mockup.mode == 'RGBA' else None)
        
        return base_image
    
    def get_text_dimensions(self, text: str, language: str, 
                           font_size: int) -> Tuple[int, int]:
        """Calculate text bounding box dimensions"""
        font = self._get_font(language, font_size)
        
        # Prepare text
        if language == "ar":
            reshaped_text = arabic_reshaper.reshape(text)
            display_text = get_display(reshaped_text)
        else:
            display_text = text
        
        # Create temporary image to measure
        temp_img = Image.new('RGB', (1, 1))
        draw = ImageDraw.Draw(temp_img)
        
        try:
            bbox = draw.textbbox((0, 0), display_text, font=font)
            width = bbox[2] - bbox[0]
            height = bbox[3] - bbox[1]
        except:
            width, height = draw.textsize(display_text, font=font)
        
        return (width, height)

