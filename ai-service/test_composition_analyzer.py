"""
Test Composition Analyzer Agent
Tests the agent's ability to parse complex descriptions
"""
import asyncio
import sys
import os
sys.path.append(os.path.dirname(os.path.abspath(__file__)))

from app.agents.composition_analyzer import CompositionAnalyzerAgent
import json


async def test_analyzer():
    """Test composition analyzer with various scenarios"""
    print("\n" + "="*70)
    print("[TEST] Composition Analyzer Agent")
    print("="*70)
    
    # Initialize agent
    try:
        analyzer = CompositionAnalyzerAgent()
    except ValueError as e:
        print(f"[TEST] SKIP: {e}")
        print("[TEST] Set GOOGLE_API_KEY to run this test")
        return
    
    # Test cases
    test_cases = [
        {
            "name": "Arabic Text Overlay - Simple",
            "description": 'صورة جميلة، اكتب "مرحباً" باللون الأزرق في الأعلى',
            "platform": "instagram"
        },
        {
            "name": "Complex Multi-layer",
            "description": 'مطعم، لوجو في الزاوية، نص "افتتاح قريب" بالأحمر أسفل الصورة',
            "platform": "facebook"
        },
        {
            "name": "Screen Mockup",
            "description": 'رجل وبنت بجانب كمبيوتر، في الشاشة واجهة نظام الخياطة ERP، وتحت الصورة اكتب: نظام الخياطة ERP باللون الأحمر',
            "platform": "linkedin"
        },
        {
            "name": "English Text",
            "description": 'Beautiful sunset, write "Summer Sale" in yellow at the bottom',
            "platform": "instagram"
        },
        {
            "name": "No Composition Needed",
            "description": 'رجل يقف في الحديقة، صورة طبيعية',
            "platform": "instagram"
        }
    ]
    
    for i, test_case in enumerate(test_cases, 1):
        print(f"\n{'='*70}")
        print(f"[TEST] Case {i}: {test_case['name']}")
        print(f"[TEST] Description: {test_case['description']}")
        print(f"{'='*70}")
        
        try:
            # Check if composition is needed
            needs_comp = analyzer.needs_composition(test_case['description'])
            print(f"[TEST] Needs Composition: {needs_comp}")
            
            if needs_comp:
                # Analyze
                result = await analyzer.analyze_description(
                    test_case['description'],
                    test_case['platform']
                )
                
                # Print results
                print("\n[TEST] Analysis Results:")
                print(json.dumps(result, indent=2, ensure_ascii=False))
                
                # Validation
                assert "scene_description" in result, "Missing scene_description"
                assert "text_overlays" in result, "Missing text_overlays"
                assert isinstance(result["text_overlays"], list), "text_overlays must be list"
                
                print("\n[TEST] PASS: Analysis successful!")
            else:
                print("[TEST] SKIP: No composition needed for this description")
            
        except Exception as e:
            print(f"\n[TEST] FAILED: {e}")
            import traceback
            traceback.print_exc()
    
    print("\n" + "="*70)
    print("[TEST] All tests completed!")
    print("="*70 + "\n")


if __name__ == "__main__":
    asyncio.run(test_analyzer())

