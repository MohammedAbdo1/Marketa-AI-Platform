# Content Writer Prompts

def get_post_generation_prompt(post_data: dict, brand_guidelines: dict) -> str:
    """Generate prompt for creating social media post content"""
    return f"""
    You are an expert social media content writer. Create engaging content for the following post:

    Post Details:
    - Platform: {post_data.get('platform', 'instagram')}
    - Post Type: {post_data.get('post_type', 'text')}
    - Topic: {post_data.get('topic', 'general')}
    - Content Theme: {post_data.get('content_theme', 'general')}
    - Target Audience: {post_data.get('target_audience', {})}
    
    Brand Guidelines:
    - Voice: {brand_guidelines.get('voice', 'professional')}
    - Colors: {brand_guidelines.get('colors', {})}
    - Languages: {', '.join(post_data.get('languages', ['ar', 'en']))}
    
    Requirements:
    1. Write engaging content that matches the platform's style
    2. Use appropriate tone for the target audience
    3. Include relevant hashtags (3-5 hashtags)
    4. Add a clear call-to-action
    5. Keep content concise but impactful
    6. Make it shareable and engaging
    
    For each language, create content that:
    - Is culturally appropriate
    - Uses local expressions and idioms
    - Maintains brand consistency
    - Encourages engagement
    
    Return as JSON:
    {{
        "content_ar": "Arabic content here",
        "content_en": "English content here", 
        "hashtags": ["#tag1", "#tag2", "#tag3"],
        "cta": "Call to action text",
        "engagement_hooks": ["hook1", "hook2"],
        "platform_optimized": true
    }}
    """

def get_regeneration_prompt(original_content: str, improvement_notes: str) -> str:
    """Generate prompt for regenerating existing content"""
    return f"""
    Improve this social media post based on the feedback:

    Original Content:
    "{original_content}"
    
    Improvement Notes:
    "{improvement_notes}"
    
    Please create an improved version that:
    1. Addresses the feedback points
    2. Maintains the original intent
    3. Improves engagement potential
    4. Keeps the same length/style
    5. Uses better language and structure
    
    Return as JSON:
    {{
        "improved_content": "New improved content",
        "changes_made": ["change1", "change2"],
        "improvement_reasoning": "Why these changes were made"
    }}
    """

def get_tone_adjustment_prompt(content: str, new_tone: str) -> str:
    """Generate prompt for adjusting content tone"""
    return f"""
    Adjust the tone of this social media content to be more {new_tone}:

    Original Content:
    "{content}"
    
    Current Tone: Professional
    Desired Tone: {new_tone}
    
    Please rewrite the content to:
    1. Match the {new_tone} tone
    2. Keep the same core message
    3. Maintain appropriate length
    4. Use {new_tone} language and expressions
    5. Keep it engaging and shareable
    
    Tone Guidelines:
    - Professional: Formal, business-like, authoritative
    - Casual: Friendly, conversational, relaxed
    - Funny: Humorous, witty, entertaining
    - Inspirational: Motivational, uplifting, positive
    - Urgent: Time-sensitive, action-oriented
    
    Return as JSON:
    {{
        "adjusted_content": "Content with new tone",
        "tone_changes": ["specific change 1", "specific change 2"],
        "maintained_elements": ["element1", "element2"]
    }}
    """

def get_hashtag_optimization_prompt(content: str, platform: str) -> str:
    """Generate prompt for optimizing hashtags"""
    return f"""
    Optimize hashtags for this {platform} post:

    Content:
    "{content}"
    
    Platform: {platform}
    
    Create optimized hashtags that:
    1. Are relevant to the content
    2. Match the platform's hashtag culture
    3. Include mix of popular and niche tags
    4. Are appropriate for the target audience
    5. Follow platform best practices
    
    Platform Guidelines:
    - Instagram: 5-10 hashtags, mix of popular and niche
    - Facebook: 1-3 hashtags, focus on relevance
    - Twitter: 1-2 hashtags, trending when possible
    - LinkedIn: 3-5 professional hashtags
    - TikTok: 3-5 trending hashtags
    
    Return as JSON:
    {{
        "optimized_hashtags": ["#tag1", "#tag2", "#tag3"],
        "hashtag_strategy": "Strategy explanation",
        "trending_tags": ["#trending1", "#trending2"],
        "niche_tags": ["#niche1", "#niche2"]
    }}
    """
