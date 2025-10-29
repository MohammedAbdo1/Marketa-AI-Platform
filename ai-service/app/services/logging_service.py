import structlog
import logging
import sys
from typing import Dict, Any, Optional
from datetime import datetime
import json

class LoggingService:
    def __init__(self):
        # Configure structlog
        structlog.configure(
            processors=[
                structlog.stdlib.filter_by_level,
                structlog.stdlib.add_logger_name,
                structlog.stdlib.add_log_level,
                structlog.stdlib.PositionalArgumentsFormatter(),
                structlog.processors.TimeStamper(fmt="iso"),
                structlog.processors.StackInfoRenderer(),
                structlog.processors.format_exc_info,
                structlog.processors.UnicodeDecoder(),
                structlog.processors.JSONRenderer()
            ],
            context_class=dict,
            logger_factory=structlog.stdlib.LoggerFactory(),
            wrapper_class=structlog.stdlib.BoundLogger,
            cache_logger_on_first_use=True,
        )
        
        # Configure standard logging
        logging.basicConfig(
            format="%(message)s",
            stream=sys.stdout,
            level=logging.INFO,
        )
        
        self.logger = structlog.get_logger()
    
    def log_request(self, method: str, path: str, user_id: str = None, 
                   request_id: str = None, **kwargs) -> None:
        """Log incoming request"""
        self.logger.info(
            "request_received",
            method=method,
            path=path,
            user_id=user_id,
            request_id=request_id,
            **kwargs
        )
    
    def log_response(self, status_code: int, response_time: float, 
                    user_id: str = None, request_id: str = None, **kwargs) -> None:
        """Log response"""
        self.logger.info(
            "response_sent",
            status_code=status_code,
            response_time_ms=round(response_time * 1000, 2),
            user_id=user_id,
            request_id=request_id,
            **kwargs
        )
    
    def log_task_start(self, task_id: str, task_name: str, user_id: str = None, **kwargs) -> None:
        """Log task start"""
        self.logger.info(
            "task_started",
            task_id=task_id,
            task_name=task_name,
            user_id=user_id,
            **kwargs
        )
    
    def log_task_progress(self, task_id: str, progress: int, message: str, **kwargs) -> None:
        """Log task progress"""
        self.logger.info(
            "task_progress",
            task_id=task_id,
            progress=progress,
            message=message,
            **kwargs
        )
    
    def log_task_completion(self, task_id: str, duration: float, result_size: int = None, **kwargs) -> None:
        """Log task completion"""
        self.logger.info(
            "task_completed",
            task_id=task_id,
            duration_seconds=round(duration, 2),
            result_size=result_size,
            **kwargs
        )
    
    def log_task_failure(self, task_id: str, error: str, duration: float = None, **kwargs) -> None:
        """Log task failure"""
        self.logger.error(
            "task_failed",
            task_id=task_id,
            error=error,
            duration_seconds=round(duration, 2) if duration else None,
            **kwargs
        )
    
    def log_ai_request(self, model: str, prompt_length: int, user_id: str = None, **kwargs) -> None:
        """Log AI model request"""
        self.logger.info(
            "ai_request",
            model=model,
            prompt_length=prompt_length,
            user_id=user_id,
            **kwargs
        )
    
    def log_ai_response(self, model: str, response_length: int, tokens_used: int = None, 
                       duration: float = None, user_id: str = None, **kwargs) -> None:
        """Log AI model response"""
        self.logger.info(
            "ai_response",
            model=model,
            response_length=response_length,
            tokens_used=tokens_used,
            duration_seconds=round(duration, 2) if duration else None,
            user_id=user_id,
            **kwargs
        )
    
    def log_cache_hit(self, cache_key: str, cache_type: str, **kwargs) -> None:
        """Log cache hit"""
        self.logger.info(
            "cache_hit",
            cache_key=cache_key,
            cache_type=cache_type,
            **kwargs
        )
    
    def log_cache_miss(self, cache_key: str, cache_type: str, **kwargs) -> None:
        """Log cache miss"""
        self.logger.info(
            "cache_miss",
            cache_key=cache_key,
            cache_type=cache_type,
            **kwargs
        )
    
    def log_rate_limit(self, user_id: str, endpoint: str, limit: int, remaining: int, **kwargs) -> None:
        """Log rate limiting"""
        self.logger.info(
            "rate_limit",
            user_id=user_id,
            endpoint=endpoint,
            limit=limit,
            remaining=remaining,
            **kwargs
        )
    
    def log_rate_limit_exceeded(self, user_id: str, endpoint: str, limit: int, **kwargs) -> None:
        """Log rate limit exceeded"""
        self.logger.warning(
            "rate_limit_exceeded",
            user_id=user_id,
            endpoint=endpoint,
            limit=limit,
            **kwargs
        )
    
    def log_error(self, error: str, error_type: str = None, user_id: str = None, **kwargs) -> None:
        """Log error"""
        self.logger.error(
            "error_occurred",
            error=error,
            error_type=error_type,
            user_id=user_id,
            **kwargs
        )
    
    def log_performance(self, operation: str, duration: float, **kwargs) -> None:
        """Log performance metrics"""
        self.logger.info(
            "performance_metric",
            operation=operation,
            duration_seconds=round(duration, 2),
            **kwargs
        )
    
    def log_system_event(self, event: str, **kwargs) -> None:
        """Log system events"""
        self.logger.info(
            "system_event",
            event=event,
            **kwargs
        )
    
    def get_structured_log(self, level: str, message: str, **kwargs) -> Dict[str, Any]:
        """Get structured log entry"""
        return {
            "timestamp": datetime.utcnow().isoformat(),
            "level": level,
            "message": message,
            **kwargs
        }

# Global logging service instance
logging_service = LoggingService()
