"""
Storage Factory
Automatically selects storage backend based on configuration
"""
from config import settings
from .base_storage import BaseStorage
from .local_storage import LocalStorage
from .s3_storage import S3Storage


def get_storage() -> BaseStorage:
    """
    Factory function - returns storage backend based on config
    
    Returns:
        Storage instance (LocalStorage or S3Storage)
    """
    backend = settings.STORAGE_BACKEND.lower()
    
    if backend == "s3":
        print(f"[Storage] Using S3 Storage (bucket: {settings.S3_BUCKET_NAME})")
        return S3Storage()
    else:
        print(f"[Storage] Using Local Storage (path: {settings.LOCAL_STORAGE_PATH})")
        return LocalStorage()


# Singleton instance - initialized once
storage: BaseStorage = get_storage()


__all__ = ['storage', 'BaseStorage', 'LocalStorage', 'S3Storage', 'get_storage']

