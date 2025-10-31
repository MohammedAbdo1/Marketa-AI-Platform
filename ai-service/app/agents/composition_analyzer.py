"""
Composition Analyzer Agent
Analyzes complex image descriptions and breaks them into structured components
"""
import google.generativeai as genai
from config import settings
from typing import Dict, Any, List, Optional
import json
import re


class CompositionAnalyzerAgent:
    """
    AI Agent that analyzes user descriptions and extracts:
    - Scene description (base image without text)
    - Text overlays (what text to add, where, and how)
    - Objects to composite (logos, screen mockups, etc.)
    - Style preferences
    """
    
    def __init__(self):
        """Initialize the Gemini model for analysis"""
        if not settings.GOOGLE_API_KEY:
            raise ValueError("GOOGLE_API_KEY required for CompositionAnalyzerAgent")
        
        genai.configure(api_key=settings.GOOGLE_API_KEY)
        self.model = genai.GenerativeModel(
            settings.TEXT_MODEL,
            generation_config=genai.types.GenerationConfig(
                temperature=0.3,  # Low temperature for structured output
                max_output_tokens=2000,
                candidate_count=1
            )
        )
        print("[CompositionAnalyzer] Agent initialized")
    
    async def analyze_description(self, user_description: str, 
                                  platform: str = "instagram",
                                  business_type: str = "") -> Dict[str, Any]:
        """
        Analyze user description and extract composition components
        
        Args:
            user_description: Complex description from user
            platform: Target platform (instagram, facebook, etc.)
            business_type: Type of business (restaurant, tech, etc.)
            
        Returns:
            Structured dict with scene_description, text_overlays, etc.
        """
        print(f"[CompositionAnalyzer] Analyzing: {user_description[:100]}...")
        
        # Build analysis prompt
        prompt = self._build_analysis_prompt(user_description, platform, business_type)
        
        try:
            # Call Gemini
            response = self.model.generate_content(prompt)
            response_text = response.text.strip()
            
            # Extract JSON from response
            analysis = self._extract_json_from_response(response_text)
            
            # Validate and enrich
            analysis = self._validate_and_enrich(analysis, user_description)
            
            print(f"[CompositionAnalyzer] Analysis complete: {len(analysis.get('text_overlays', []))} text overlays")
            return analysis
            
        except Exception as e:
            print(f"[CompositionAnalyzer] ERROR: {e}")
            # Return simple fallback
            return self._create_fallback_analysis(user_description)
    
    def _build_analysis_prompt(self, description: str, platform: str, business_type: str) -> str:
        """Build the prompt for Gemini to analyze the description"""
        return f"""
You are an expert at analyzing marketing image descriptions and breaking them into components.

User Description: "{description}"
Platform: {platform}
Business Type: {business_type}

Analyze this description and extract:

1. **scene_description**: The main visual scene WITHOUT any text overlays
   - Remove any mentions of text, captions, or writing
   - Focus only on the visual elements (people, objects, environment)
   - Example: "Two people standing next to a computer screen in a tailor workshop"

2. **text_overlays**: Any text that should be added ON TOP of the image
   - Extract the exact text content
   - Determine position (top, bottom, center, top-left, top-right, bottom-left, bottom-right, center-left, center-right)
   - Detect color (if mentioned, otherwise suggest appropriate color)
   - Detect font size (small, medium, large, extra-large)
   - Detect font weight (normal, bold)

3. **screen_content**: What should appear inside screens/monitors in the image (if any)
   - Example: "ERP dashboard with charts"

4. **objects_to_composite**: Additional objects to overlay (logos, icons, etc.)
   - Type: logo, icon, sticker, badge
   - Position: Where to place it

5. **image_style**: Overall style and mood
   - Example: "professional, corporate, modern"

**Important Rules:**
- Detect Arabic text: "اكتب", "نص", "عنوان", "caption"
- Detect English text: "write", "text", "caption", "title"
- Detect position keywords: Arabic ("في الأعلى", "أسفل", "في الوسط") and English ("at the top", "at the bottom", "centered")
- Detect color keywords: Arabic ("باللون الأحمر", "باللون الأزرق") and English ("in red", "in blue color")

Return ONLY valid JSON in this exact format:
{{
  "scene_description": "base scene without text",
  "text_overlays": [
    {{
      "text": "exact text content",
      "position": "bottom-center",
      "color": "#FF0000",
      "font_size": 48,
      "font_weight": "bold",
      "language": "ar or en"
    }}
  ],
  "screen_content": "what appears on screens or null",
  "objects_to_composite": [
    {{
      "type": "logo or icon",
      "position": "top-right",
      "size": "10% or 20%"
    }}
  ],
  "image_style": "professional, modern"
}}

Return ONLY the JSON, no explanations.
"""
    
    def _extract_json_from_response(self, response_text: str) -> Dict[str, Any]:
        """Extract JSON from Gemini response"""
        # Try to find JSON in response
        json_match = re.search(r'\{.*\}', response_text, re.DOTALL)
        if json_match:
            try:
                return json.loads(json_match.group(0))
            except json.JSONDecodeError:
                pass
        
        # If no valid JSON found, parse manually
        return self._manual_parse(response_text)
    
    def _manual_parse(self, text: str) -> Dict[str, Any]:
        """Fallback: manually parse response if JSON parsing fails"""
        return {
            "scene_description": text[:200],  # First 200 chars as scene
            "text_overlays": [],
            "screen_content": None,
            "objects_to_composite": [],
            "image_style": "professional, modern"
        }
    
    def _validate_and_enrich(self, analysis: Dict[str, Any], 
                            original_description: str) -> Dict[str, Any]:
        """Validate analysis structure and enrich with defaults"""
        # Ensure all required keys exist
        if "scene_description" not in analysis or not analysis["scene_description"]:
            analysis["scene_description"] = original_description
        
        if "text_overlays" not in analysis:
            analysis["text_overlays"] = []
        
        if "screen_content" not in analysis:
            analysis["screen_content"] = None
        
        if "objects_to_composite" not in analysis:
            analysis["objects_to_composite"] = []
        
        if "image_style" not in analysis:
            analysis["image_style"] = "professional, high quality"
        
        # Validate and normalize text_overlays
        for overlay in analysis["text_overlays"]:
            if "position" not in overlay:
                overlay["position"] = "bottom-center"
            if "color" not in overlay:
                overlay["color"] = "#FFFFFF"  # Default white
            if "font_size" not in overlay:
                overlay["font_size"] = 48
            if "font_weight" not in overlay:
                overlay["font_weight"] = "bold"
            if "language" not in overlay:
                # Auto-detect language
                overlay["language"] = self._detect_language(overlay.get("text", ""))
        
        return analysis
    
    def _detect_language(self, text: str) -> str:
        """Detect if text is Arabic or English"""
        if not text:
            return "en"
        # Check for Arabic characters
        if re.search(r'[\u0600-\u06FF]', text):
            return "ar"
        return "en"
    
    def _create_fallback_analysis(self, description: str) -> Dict[str, Any]:
        """Create a simple fallback analysis if Gemini fails"""
        return {
            "scene_description": description,
            "text_overlays": [],
            "screen_content": None,
            "objects_to_composite": [],
            "image_style": "professional, modern, high quality"
        }
    
    def needs_composition(self, description: str) -> bool:
        """
        Quick check: Does this description need composition?
        Returns True if description mentions text, logos, or complex elements
        """
        # Keywords that indicate composition is needed
        composition_keywords = [
            # Arabic
            "اكتب", "نص", "عنوان", "كلمة", "لوجو", "شاشة", "شعار",
            # English
            "write", "text", "caption", "title", "logo", "screen", "add"
        ]
        
        description_lower = description.lower()
        return any(keyword in description_lower for keyword in composition_keywords)

