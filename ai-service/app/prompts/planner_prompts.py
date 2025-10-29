# Campaign Planner Prompts

def get_campaign_structure_prompt(request_data: dict) -> str:
    """Generate prompt for campaign structure planning"""
    return f"""
    You are an expert marketing strategist. Create a comprehensive social media campaign structure based on the following information:

    Business Details:
    - Type: {request_data.get('business_type', 'general')}
    - Product/Service: {request_data.get('product_name', 'N/A')}
    - Description: {request_data.get('description', 'N/A')}
    
    Campaign Goals:
    - Primary Goal: {request_data.get('campaign_goal', 'awareness')}
    - Target Audience: {request_data.get('target_audience', {})}
    - Duration: {request_data.get('duration_weeks', 4)} weeks
    - Posts per week: {request_data.get('posts_per_week', 3)}
    - Platforms: {', '.join(request_data.get('platforms', []))}
    
    Brand Guidelines:
    - Colors: {request_data.get('brand_colors', {})}
    - Voice: {request_data.get('brand_voice', 'professional')}
    - Languages: {', '.join(request_data.get('languages', ['ar', 'en']))}
    
    Please create a detailed campaign structure that includes:
    1. Total number of posts
    2. Weekly distribution across platforms
    3. Post types for each platform
    4. Content themes and topics
    5. Suggested hashtags
    6. Call-to-action strategies
    
    Return the response as JSON with the following structure:
    {{
        "total_posts": number,
        "weekly_distribution": {{"week_1": number, "week_2": number, ...}},
        "post_types": ["text", "image", "video", "carousel"],
        "suggested_topics": ["topic1", "topic2", ...],
        "content_themes": ["theme1", "theme2", ...],
        "platforms_breakdown": {{"instagram": number, "facebook": number, ...}},
        "estimated_duration": "X weeks",
        "posts": [
            {{
                "week": 1,
                "day": 1,
                "platform": "instagram",
                "post_type": "image",
                "topic": "Introduction to product",
                "content_theme": "brand_awareness",
                "needs_image": true,
                "image_prompt": "Professional product photo with clean background",
                "hashtags": ["#brand", "#product"],
                "cta": "Learn more"
            }}
        ]
    }}
    """

def get_color_suggestion_prompt(description: str) -> str:
    """Generate prompt for color palette suggestions"""
    return f"""
    As a brand color expert, analyze this product/service description and suggest 3 color palettes:

    Description: "{description}"
    
    For each palette, consider:
    - Industry appropriateness
    - Target audience appeal
    - Social media visibility
    - Brand personality match
    
    Return 3 color palettes as JSON array:
    [
        {{
            "name": "Palette Name",
            "primary_color": "#hexcode",
            "secondary_color": "#hexcode", 
            "accent_color": "#hexcode",
            "reasoning": "Why this palette works"
        }}
    ]
    
    Make sure colors are:
    - Visually appealing
    - Accessible (good contrast)
    - Social media friendly
    - Professional yet engaging
    """

def get_content_theme_prompt(business_type: str, campaign_goal: str, target_audience: dict) -> str:
    """Generate prompt for content themes"""
    return f"""
    Create engaging content themes for a {business_type} business with the goal of {campaign_goal}.
    
    Target Audience: {target_audience}
    
    Generate 8-12 content themes that would work well across social media platforms.
    Each theme should be:
    - Relevant to the business type
    - Aligned with the campaign goal
    - Appealing to the target audience
    - Suitable for multiple post formats
    
    Return as JSON array of theme objects:
    [
        {{
            "theme": "Theme Name",
            "description": "Brief description",
            "post_ideas": ["idea1", "idea2", "idea3"],
            "hashtags": ["#tag1", "#tag2"],
            "platforms": ["instagram", "facebook"]
        }}
    ]
    """
