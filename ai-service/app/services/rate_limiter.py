from typing import Dict, Optional, Tuple
from app.services.cache_service import cache_service
from config import settings
import time

class RateLimiter:
    def __init__(self):
        self.cache = cache_service
    
    def get_user_tier(self, user_id: str) -> str:
        """Get user subscription tier (free, pro, enterprise)"""
        # In production, this would query the database
        # For now, return 'free' as default
        return "free"
    
    def get_rate_limit(self, user_tier: str) -> int:
        """Get rate limit based on user tier"""
        limits = {
            "free": settings.RATE_LIMIT_FREE,
            "pro": settings.RATE_LIMIT_PRO,
            "enterprise": settings.RATE_LIMIT_ENTERPRISE
        }
        return limits.get(user_tier, settings.RATE_LIMIT_FREE)
    
    def is_allowed(self, user_id: str, endpoint: str = "general") -> Tuple[bool, Dict[str, any]]:
        """
        Check if request is allowed based on rate limits
        
        Returns:
            (is_allowed, rate_limit_info)
        """
        try:
            # In development mode, always allow requests
            if settings.DEBUG:
                return True, {
                    "limit": settings.RATE_LIMIT_FREE,
                    "remaining": settings.RATE_LIMIT_FREE,
                    "reset_time": int(time.time()) + settings.RATE_LIMIT_WINDOW,
                    "user_tier": "free",
                    "debug_mode": True
                }
            
            # Get user tier
            user_tier = self.get_user_tier(user_id)
            rate_limit = self.get_rate_limit(user_tier)
            
            # Create rate limit key
            window_start = int(time.time() // settings.RATE_LIMIT_WINDOW) * settings.RATE_LIMIT_WINDOW
            rate_key = f"rate_limit:{user_id}:{endpoint}:{window_start}"
            
            # Get current count
            current_count = self.cache.get_user_request_count(user_id)
            
            # Check if limit exceeded
            if current_count >= rate_limit:
                return False, {
                    "limit": rate_limit,
                    "remaining": 0,
                    "reset_time": window_start + settings.RATE_LIMIT_WINDOW,
                    "user_tier": user_tier
                }
            
            # Increment counter
            new_count = self.cache.increment_user_request_count(user_id, settings.RATE_LIMIT_WINDOW)
            
            return True, {
                "limit": rate_limit,
                "remaining": rate_limit - new_count,
                "reset_time": window_start + settings.RATE_LIMIT_WINDOW,
                "user_tier": user_tier
            }
            
        except Exception as e:
            # On error, allow the request but log the error
            print(f"Rate limiter error: {e}")
            return True, {
                "limit": settings.RATE_LIMIT_FREE,
                "remaining": settings.RATE_LIMIT_FREE,
                "reset_time": int(time.time()) + settings.RATE_LIMIT_WINDOW,
                "user_tier": "free",
                "error": "Rate limiter error"
            }
    
    def get_rate_limit_headers(self, user_id: str, endpoint: str = "general") -> Dict[str, str]:
        """Get rate limit headers for response"""
        is_allowed, info = self.is_allowed(user_id, endpoint)
        
        return {
            "X-RateLimit-Limit": str(info.get("limit", 0)),
            "X-RateLimit-Remaining": str(info.get("remaining", 0)),
            "X-RateLimit-Reset": str(info.get("reset_time", 0)),
            "X-RateLimit-Tier": info.get("user_tier", "free")
        }
    
    def reset_user_limits(self, user_id: str) -> bool:
        """Reset rate limits for a user (admin function)"""
        try:
            return self.cache.clear_user_limits(user_id)
        except Exception as e:
            print(f"Reset rate limits error: {e}")
            return False
    
    def get_user_stats(self, user_id: str) -> Dict[str, any]:
        """Get user rate limit statistics"""
        try:
            user_tier = self.get_user_tier(user_id)
            rate_limit = self.get_rate_limit(user_tier)
            current_count = self.cache.get_user_request_count(user_id)
            
            window_start = int(time.time() // settings.RATE_LIMIT_WINDOW) * settings.RATE_LIMIT_WINDOW
            
            return {
                "user_id": user_id,
                "tier": user_tier,
                "limit": rate_limit,
                "used": current_count,
                "remaining": max(0, rate_limit - current_count),
                "reset_time": window_start + settings.RATE_LIMIT_WINDOW,
                "window_seconds": settings.RATE_LIMIT_WINDOW
            }
        except Exception as e:
            return {
                "user_id": user_id,
                "tier": "free",
                "limit": settings.RATE_LIMIT_FREE,
                "used": 0,
                "remaining": settings.RATE_LIMIT_FREE,
                "reset_time": int(time.time()) + settings.RATE_LIMIT_WINDOW,
                "window_seconds": settings.RATE_LIMIT_WINDOW,
                "error": str(e)
            }
    
    def is_endpoint_limited(self, endpoint: str) -> bool:
        """Check if endpoint has rate limiting enabled"""
        # Define which endpoints have rate limiting
        limited_endpoints = [
            "/api/campaign/generate",
            "/api/campaign/preview", 
            "/api/post/regenerate-text",
            "/api/post/regenerate-image",
            "/api/brand/suggest-colors"
        ]
        # Task status and result endpoints should not be rate limited (they're polling endpoints)
        if endpoint.startswith("/api/task/"):
            return False
        return endpoint in limited_endpoints
    
    def get_endpoint_weight(self, endpoint: str) -> int:
        """Get weight/cost of endpoint for rate limiting"""
        # Different endpoints consume different amounts of rate limit
        weights = {
            "/api/campaign/generate": 3,  # Most expensive
            "/api/campaign/preview": 1,
            "/api/post/regenerate-text": 1,
            "/api/post/regenerate-image": 2,
            "/api/brand/suggest-colors": 1
        }
        return weights.get(endpoint, 1)

# Global rate limiter instance
rate_limiter = RateLimiter()
