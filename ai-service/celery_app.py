from celery import Celery
from config import settings
import os

# Create Celery instance
celery_app = Celery(
    "marketa_ai",
    broker=settings.CELERY_BROKER_URL,
    backend=settings.CELERY_RESULT_BACKEND,
    include=['app.tasks.campaign_tasks']
)

# Celery configuration
celery_app.conf.update(
    task_serializer=settings.CELERY_TASK_SERIALIZER,
    accept_content=settings.CELERY_ACCEPT_CONTENT,
    result_serializer=settings.CELERY_RESULT_SERIALIZER,
    timezone=settings.CELERY_TIMEZONE,
    enable_utc=settings.CELERY_ENABLE_UTC,
    
    # Task routing
    task_routes={
        'app.tasks.campaign_tasks.generate_campaign_task': {'queue': 'campaign_generation'},
        'app.tasks.campaign_tasks.generate_preview_task': {'queue': 'preview_generation'},
        'app.tasks.campaign_tasks.regenerate_text_task': {'queue': 'text_regeneration'},
        'app.tasks.campaign_tasks.regenerate_image_task': {'queue': 'image_regeneration'},
    },
    
    # Task execution settings
    task_acks_late=True,
    worker_prefetch_multiplier=1,
    task_reject_on_worker_lost=True,
    
    # Result backend settings
    result_expires=3600,  # 1 hour
    result_persistent=True,
    
    # Task time limits
    task_soft_time_limit=300,  # 5 minutes soft limit
    task_time_limit=600,       # 10 minutes hard limit
    
    # Worker settings
    worker_max_tasks_per_child=50,
    worker_disable_rate_limits=False,
    
    # Monitoring
    worker_send_task_events=True,
    task_send_sent_event=True,
)

# Optional: Configure task compression
celery_app.conf.task_compression = 'gzip'
celery_app.conf.result_compression = 'gzip'

# Optional: Configure task compression
celery_app.conf.task_compression = 'gzip'
celery_app.conf.result_compression = 'gzip'

if __name__ == '__main__':
    celery_app.start()
