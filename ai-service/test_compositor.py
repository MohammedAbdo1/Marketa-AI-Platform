"""
Test Image Compositor Service
Tests text overlay with Arabic and English
"""
import asyncio
import sys
import os
sys.path.append(os.path.dirname(os.path.abspath(__file__)))

from app.services.image_compositor import ImageCompositor
from PIL import Image
import io


async def test_compositor():
    """Test compositor with text overlays"""
    print("\n" + "="*70)
    print("[TEST] Image Compositor Service")
    print("="*70)
    
    # Create compositor
    compositor = ImageCompositor()
    
    # Create a simple test image (800x600 white background)
    test_image = Image.new('RGB', (800, 600), color='white')
    
    # Add some visual elements
    from PIL import ImageDraw
    draw = ImageDraw.Draw(test_image)
    draw.rectangle([100, 100, 700, 500], fill='lightblue', outline='darkblue', width=3)
    draw.ellipse([300, 200, 500, 400], fill='yellow', outline='orange', width=2)
    
    # Convert to bytes
    img_bytes = io.BytesIO()
    test_image.save(img_bytes, format='PNG')
    test_image_bytes = img_bytes.getvalue()
    
    # Test cases
    test_cases = [
        {
            "name": "Arabic Text - Bottom Center",
            "analysis": {
                "scene_description": "Test scene",
                "text_overlays": [
                    {
                        "text": "نظام الخياطة ERP",
                        "position": "bottom-center",
                        "color": "#FF0000",
                        "font_size": 48,
                        "font_weight": "bold",
                        "language": "ar"
                    }
                ]
            }
        },
        {
            "name": "English Text - Top Center",
            "analysis": {
                "scene_description": "Test scene",
                "text_overlays": [
                    {
                        "text": "Summer Sale 50% OFF",
                        "position": "top-center",
                        "color": "#0000FF",
                        "font_size": 64,
                        "font_weight": "bold",
                        "language": "en"
                    }
                ]
            }
        },
        {
            "name": "Multiple Texts - Arabic + English",
            "analysis": {
                "scene_description": "Test scene",
                "text_overlays": [
                    {
                        "text": "مرحباً بكم",
                        "position": "top-center",
                        "color": "blue",
                        "font_size": "large",
                        "font_weight": "bold",
                        "language": "ar"
                    },
                    {
                        "text": "Welcome",
                        "position": "bottom-center",
                        "color": "red",
                        "font_size": "medium",
                        "font_weight": "bold",
                        "language": "en"
                    }
                ]
            }
        }
    ]
    
    for i, test_case in enumerate(test_cases, 1):
        print(f"\n{'='*70}")
        print(f"[TEST] Case {i}: {test_case['name']}")
        print(f"{'='*70}")
        
        try:
            # Compose image
            result = await compositor.compose_image(
                test_case['analysis'],
                test_image_bytes
            )
            
            # Verify result
            assert result.final_image, "Final image is empty"
            assert len(result.final_image) > 0, "Final image has no data"
            assert result.layers, "Layers are missing"
            assert result.dimensions == (800, 600), f"Wrong dimensions: {result.dimensions}"
            
            print(f"[TEST] Image size: {len(result.final_image)} bytes")
            print(f"[TEST] Layers: {len(result.layers['layers'])} layer(s)")
            print(f"[TEST] Dimensions: {result.dimensions}")
            
            # Save test output
            output_filename = f"test_output_{i}.png"
            with open(output_filename, 'wb') as f:
                f.write(result.final_image)
            print(f"[TEST] Saved output: {output_filename}")
            
            print(f"[TEST] PASS: {test_case['name']}")
            
        except Exception as e:
            print(f"[TEST] FAILED: {e}")
            import traceback
            traceback.print_exc()
    
    print("\n" + "="*70)
    print("[TEST] All compositor tests completed!")
    print("[TEST] Check test_output_*.png files to see results")
    print("="*70 + "\n")


if __name__ == "__main__":
    asyncio.run(test_compositor())

