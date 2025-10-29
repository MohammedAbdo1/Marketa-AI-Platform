import redis
import json
import hashlib
from typing import Any, Optional, Dict
from config import settings

class CacheService:
    def __init__(self):
        self.redis_client = redis.from_url(
            settings.REDIS_URL,
            decode_responses=True,
            socket_connect_timeout=5,
            socket_timeout=5,
            retry_on_timeout=True
        )
        self.prefix = settings.CACHE_PREFIX
        self.default_ttl = settings.CACHE_TTL
    
    def _get_key(self, key: str) -> str:
        """Generate cache key with prefix"""
        return f"{self.prefix}{key}"
    
    def _generate_hash(self, data: Any) -> str:
        """Generate hash for data to use as cache key"""
        if isinstance(data, dict):
            data_str = json.dumps(data, sort_keys=True)
        else:
            data_str = str(data)
        return hashlib.md5(data_str.encode()).hexdigest()
    
    def get(self, key: str) -> Optional[Any]:
        """Get value from cache"""
        try:
            cache_key = self._get_key(key)
            value = self.redis_client.get(cache_key)
            if value:
                return json.loads(value)
            return None
        except Exception as e:
            print(f"Cache get error: {e}")
            return None
    
    def set(self, key: str, value: Any, ttl: Optional[int] = None) -> bool:
        """Set value in cache"""
        try:
            cache_key = self._get_key(key)
            ttl = ttl or self.default_ttl
            serialized_value = json.dumps(value, default=str)
            return self.redis_client.setex(cache_key, ttl, serialized_value)
        except Exception as e:
            print(f"Cache set error: {e}")
            return False
    
    def delete(self, key: str) -> bool:
        """Delete key from cache"""
        try:
            cache_key = self._get_key(key)
            return bool(self.redis_client.delete(cache_key))
        except Exception as e:
            print(f"Cache delete error: {e}")
            return False
    
    def exists(self, key: str) -> bool:
        """Check if key exists in cache"""
        try:
            cache_key = self._get_key(key)
            return bool(self.redis_client.exists(cache_key))
        except Exception as e:
            print(f"Cache exists error: {e}")
            return False
    
    def get_or_set(self, key: str, func, ttl: Optional[int] = None, *args, **kwargs) -> Any:
        """Get value from cache or set it using function"""
        cached_value = self.get(key)
        if cached_value is not None:
            return cached_value
        
        # Generate value using function
        value = func(*args, **kwargs)
        self.set(key, value, ttl)
        return value
    
    def get_cached_ai_result(self, prompt: str, model_type: str = "text") -> Optional[Any]:
        """Get cached AI result based on prompt"""
        cache_key = f"ai_result:{model_type}:{self._generate_hash(prompt)}"
        return self.get(cache_key)
    
    def set_cached_ai_result(self, prompt: str, result: Any, model_type: str = "text", ttl: Optional[int] = None) -> bool:
        """Cache AI result"""
        cache_key = f"ai_result:{model_type}:{self._generate_hash(prompt)}"
        return self.set(cache_key, result, ttl)
    
    def get_cached_campaign(self, campaign_id: str) -> Optional[Dict]:
        """Get cached campaign data"""
        cache_key = f"campaign:{campaign_id}"
        return self.get(cache_key)
    
    def set_cached_campaign(self, campaign_id: str, campaign_data: Dict, ttl: Optional[int] = None) -> bool:
        """Cache campaign data"""
        cache_key = f"campaign:{campaign_id}"
        return self.set(cache_key, campaign_data, ttl)
    
    def invalidate_campaign(self, campaign_id: str) -> bool:
        """Invalidate campaign cache"""
        cache_key = f"campaign:{campaign_id}"
        return self.delete(cache_key)
    
    def get_cached_user_limits(self, user_id: str) -> Optional[Dict]:
        """Get cached user rate limits"""
        cache_key = f"user_limits:{user_id}"
        return self.get(cache_key)
    
    def set_cached_user_limits(self, user_id: str, limits: Dict, ttl: Optional[int] = None) -> bool:
        """Cache user rate limits"""
        cache_key = f"user_limits:{user_id}"
        return self.set(cache_key, limits, ttl)
    
    def increment_user_request_count(self, user_id: str, window: int = 3600) -> int:
        """Increment user request count for rate limiting"""
        cache_key = f"user_requests:{user_id}"
        try:
            # Use Redis pipeline for atomic operations
            pipe = self.redis_client.pipeline()
            pipe.incr(cache_key)
            pipe.expire(cache_key, window)
            results = pipe.execute()
            return results[0]
        except Exception as e:
            print(f"Rate limit increment error: {e}")
            return 0
    
    def get_user_request_count(self, user_id: str) -> int:
        """Get current user request count"""
        cache_key = f"user_requests:{user_id}"
        try:
            count = self.redis_client.get(cache_key)
            return int(count) if count else 0
        except Exception as e:
            print(f"Rate limit get error: {e}")
            return 0
    
    def clear_user_limits(self, user_id: str) -> bool:
        """Clear user rate limit data"""
        try:
            pipe = self.redis_client.pipeline()
            pipe.delete(f"user_requests:{user_id}")
            pipe.delete(f"user_limits:{user_id}")
            pipe.execute()
            return True
        except Exception as e:
            print(f"Clear user limits error: {e}")
            return False
    
    def health_check(self) -> Dict[str, Any]:
        """Check cache service health"""
        try:
            # Test basic operations
            test_key = "health_check"
            test_value = {"timestamp": "test", "status": "ok"}
            
            # Test set
            set_result = self.set(test_key, test_value, 10)
            
            # Test get
            get_result = self.get(test_key)
            
            # Test delete
            delete_result = self.delete(test_key)
            
            # Test connection
            ping_result = self.redis_client.ping()
            
            return {
                "status": "healthy",
                "ping": ping_result,
                "set": set_result,
                "get": get_result == test_value,
                "delete": delete_result
            }
        except Exception as e:
            return {
                "status": "unhealthy",
                "error": str(e)
            }

# Global cache service instance
cache_service = CacheService()
