"""
End-to-End Test with Mock Image
Tests full pipeline without requiring Stability API credits
"""
import asyncio
import sys
import os
sys.path.append(os.path.dirname(os.path.abspath(__file__)))

from app.agents.composition_analyzer import CompositionAnalyzerAgent
from app.services.image_compositor import ImageCompositor
from PIL import Image, ImageDraw
import io


async def test_with_mock_image():
    """Test composition with a mock base image (no API needed)"""
    print("\n" + "="*70)
    print("[TEST] Full Composition Pipeline (Mock Image)")
    print("="*70)
    
    # Initialize
    try:
        analyzer = CompositionAnalyzerAgent()
        compositor = ImageCompositor()
    except Exception as e:
        print(f"[TEST] SKIP: {e}")
        return
    
    # Create mock base image (restaurant scene simulation)
    mock_image = Image.new('RGB', (1024, 1024), color='#F5E6D3')
    draw = ImageDraw.Draw(mock_image)
    
    # Draw simple restaurant scene
    draw.rectangle([100, 200, 924, 800], fill='#8B4513', outline='#654321', width=5)  # Table
    draw.ellipse([300, 100, 500, 200], fill='#FFD700')  # Plate
    draw.ellipse([600, 150, 750, 250], fill='#FF6B6B')  # Food
    
    # Convert to bytes
    img_bytes = io.BytesIO()
    mock_image.save(img_bytes, format='PNG')
    mock_image_bytes = img_bytes.getvalue()
    
    # Test cases
    test_cases = [
        {
            "name": "Arabic Text - Red Bottom",
            "description": "صورة لمطعم، اكتب 'افتتاح قريب' باللون الأحمر في الأسفل",
        },
        {
            "name": "ERP System Example",
            "description": "مكتب عمل، اكتب 'نظام الخياطة ERP' باللون الأحمر أسفل الصورة",
        },
        {
            "name": "English Sale",
            "description": "Restaurant image, write 'Grand Opening' in blue at the top",
        },
        {
            "name": "Multiple Texts",
            "description": "كافيه، اكتب 'قهوة طازجة' بالأخضر في الأعلى، و 'Fresh Coffee' بالأزرق في الأسفل",
        }
    ]
    
    for i, test_case in enumerate(test_cases, 1):
        print(f"\n{'='*70}")
        print(f"[TEST] Case {i}: {test_case['name']}")
        print(f"[TEST] Description: {test_case['description']}")
        print(f"{'='*70}")
        
        try:
            # Step 1: Analyze
            print("[TEST] Analyzing...")
            analysis = await analyzer.analyze_description(test_case['description'])
            
            print(f"[TEST] Scene: {analysis['scene_description'][:60]}...")
            print(f"[TEST] Text overlays: {len(analysis['text_overlays'])}")
            for overlay in analysis['text_overlays']:
                print(f"  - '{overlay.get('text', '')}' at {overlay.get('position', 'unknown')}, color: {overlay.get('color', 'unknown')}")
            
            # Step 2: Compose (using mock image instead of generating)
            print("[TEST] Composing...")
            composed = await compositor.compose_image(analysis, mock_image_bytes)
            
            # Step 3: Save result
            output_file = f"test_mock_output_{i}.png"
            with open(output_file, 'wb') as f:
                f.write(composed.final_image)
            
            print(f"[TEST] PASS: Saved to {output_file}")
            print(f"  - Image size: {len(composed.final_image)} bytes")
            print(f"  - Layers: {len(composed.layers['layers'])}")
            
        except Exception as e:
            print(f"[TEST] FAILED: {e}")
            import traceback
            traceback.print_exc()
    
    print("\n" + "="*70)
    print("[TEST] All tests completed!")
    print("[TEST] Check test_mock_output_*.png files")
    print("="*70 + "\n")


if __name__ == "__main__":
    asyncio.run(test_with_mock_image())

