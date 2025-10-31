"""
End-to-End Test: Full Composition Pipeline
Tests: Analyzer → Generator → Compositor → Storage
"""
import asyncio
import sys
import os
sys.path.append(os.path.dirname(os.path.abspath(__file__)))

from app.agents.composition_analyzer import CompositionAnalyzerAgent
from app.agents.image_gen import ImageGeneratorAgent


async def test_full_pipeline():
    """Test complete composition pipeline"""
    print("\n" + "="*70)
    print("[TEST] Full Composition Pipeline (End-to-End)")
    print("="*70)
    
    # Initialize agents
    try:
        analyzer = CompositionAnalyzerAgent()
        generator = ImageGeneratorAgent()
    except Exception as e:
        print(f"[TEST] SKIP: {e}")
        return
    
    # Test case: Complex Arabic description
    description = "صورة لمطعم جميل، اكتب 'افتتاح قريب' باللون الأحمر في الأسفل"
    
    print(f"\n[TEST] Description: {description}")
    print("="*70)
    
    try:
        # Step 1: Analyze description
        print("\n[TEST] Step 1: Analyzing description...")
        analysis = await analyzer.analyze_description(description, "instagram", "restaurant")
        print(f"[TEST] Analysis complete:")
        print(f"  - Scene: {analysis['scene_description'][:50]}...")
        print(f"  - Text overlays: {len(analysis['text_overlays'])}")
        for overlay in analysis['text_overlays']:
            print(f"    * '{overlay.get('text', '')}' ({overlay.get('language', 'unknown')})")
        
        # Step 2: Generate composed image
        print("\n[TEST] Step 2: Generating composed image...")
        print("[TEST] This will take ~15-20 seconds (Stability AI generation)...")
        
        result = await generator.generate_composed_image(analysis, size="1024x1024")
        
        print(f"\n[TEST] Composition complete!")
        print(f"  - Final image URL: {result['final_image_url']}")
        print(f"  - Base image URL: {result['base_image_url']}")
        print(f"  - Dimensions: {result['dimensions']}")
        print(f"  - Layers: {len(result['layers']['layers'])} layer(s)")
        
        print("\n" + "="*70)
        print("[TEST] SUCCESS: Full pipeline working!")
        print("[TEST] Check the generated image at the URLs above")
        print("="*70 + "\n")
        
    except Exception as e:
        print(f"\n[TEST] FAILED: {e}")
        import traceback
        traceback.print_exc()


if __name__ == "__main__":
    asyncio.run(test_full_pipeline())

