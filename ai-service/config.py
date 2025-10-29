import os
from dotenv import load_dotenv

# Try to load .env file, but don't fail if it has encoding issues
try:
    load_dotenv()
    print(".env file loaded successfully")
except Exception as e:
    print(f"Warning: Could not load .env file: {e}")
    print("Using environment variables or defaults...")

class Settings:
    # General
    APP_NAME = "Marketa AI Service"
    DEBUG = os.getenv("DEBUG", "True").lower() == "true"
    HOST = os.getenv("HOST", "0.0.0.0")
    PORT = int(os.getenv("PORT", 8001))

    # API Keys
    GOOGLE_API_KEY = os.getenv("GOOGLE_API_KEY")
    OPENAI_API_KEY = os.getenv("OPENAI_API_KEY")
    STABILITY_API_KEY = os.getenv("STABILITY_API_KEY")
    
    def __init__(self):
        print(f"Google API Key: {'Set' if self.GOOGLE_API_KEY else 'Missing'}")
        print(f"OpenAI API Key: {'Set' if self.OPENAI_API_KEY else 'Missing'}")
        print(f"Stability API Key: {'Set' if self.STABILITY_API_KEY else 'Missing'}")

    # AI Model Settings
    TEXT_MODEL = "gemini-2.0-flash"
    IMAGE_MODEL = "stable-diffusion-v3"

    # Laravel Backend URL
    LARAVEL_BASE_URL = os.getenv("LARAVEL_BASE_URL", "http://localhost:8000/api")

    # Agent specific settings
    PLANNER_TEMPERATURE = 0.3  # Reduced for speed
    WRITER_TEMPERATURE = 0.4   # Reduced for speed
    REVIEWER_TEMPERATURE = 0.3 # Reduced for speed
    IMAGE_GEN_STYLE = "photographic"
    
    # Timeout settings
    AI_REQUEST_TIMEOUT = int(os.getenv("AI_REQUEST_TIMEOUT", "120"))  # 120 seconds for Google API calls
    MAX_OUTPUT_TOKENS = int(os.getenv("MAX_OUTPUT_TOKENS", "1000"))  # 1000 tokens max
    
    # Redis Configuration
    REDIS_URL = os.getenv("REDIS_URL", "redis://localhost:6379/0")
    REDIS_HOST = os.getenv("REDIS_HOST", "localhost")
    REDIS_PORT = int(os.getenv("REDIS_PORT", "6379"))
    REDIS_DB = int(os.getenv("REDIS_DB", "0"))
    REDIS_PASSWORD = os.getenv("REDIS_PASSWORD", "")
    
    # Celery Configuration
    CELERY_BROKER_URL = os.getenv("CELERY_BROKER_URL", REDIS_URL)
    CELERY_RESULT_BACKEND = os.getenv("CELERY_RESULT_BACKEND", REDIS_URL)
    CELERY_TASK_SERIALIZER = "json"
    CELERY_RESULT_SERIALIZER = "json"
    CELERY_ACCEPT_CONTENT = ["json"]
    CELERY_TIMEZONE = "UTC"
    CELERY_ENABLE_UTC = True
    
    # Rate Limiting
    # In development (DEBUG=True), use very high limits to avoid blocking
    RATE_LIMIT_WINDOW = int(os.getenv("RATE_LIMIT_WINDOW", "3600"))  # 1 hour window
    
    # Set rate limits based on DEBUG mode
    _is_debug = os.getenv("DEBUG", "True").lower() == "true"
    if _is_debug:
        # In DEBUG mode, ignore env vars and use high limits
        RATE_LIMIT_FREE = 100000      # 100k requests per hour for development
        RATE_LIMIT_PRO = 100000      # 100k requests per hour for development
        RATE_LIMIT_ENTERPRISE = 100000  # 100k requests per hour for development
    else:
        RATE_LIMIT_FREE = int(os.getenv("RATE_LIMIT_FREE", "1000"))      # 1000 requests per hour
        RATE_LIMIT_PRO = int(os.getenv("RATE_LIMIT_PRO", "100"))      # 100 requests per hour
        RATE_LIMIT_ENTERPRISE = int(os.getenv("RATE_LIMIT_ENTERPRISE", "1000"))  # 1000 requests per hour
    
    # Cache Settings
    CACHE_TTL = int(os.getenv("CACHE_TTL", "3600"))  # 1 hour cache
    CACHE_PREFIX = "marketa_ai:"
    
    # WebSocket Settings
    WEBSOCKET_CORS_ORIGINS = ["http://localhost:5173", "http://localhost:8000", "http://localhost:3000", "http://localhost:3001"]
    
    # Connection Pool Settings
    REDIS_POOL_SIZE = int(os.getenv("REDIS_POOL_SIZE", "10"))
    REDIS_POOL_TIMEOUT = int(os.getenv("REDIS_POOL_TIMEOUT", "5"))

settings = Settings()