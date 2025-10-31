"""
Local Storage Implementation
Stores files on local filesystem
"""
import os
import aiofiles
from .base_storage import BaseStorage
from config import settings


class LocalStorage(BaseStorage):
    """Local filesystem storage implementation"""
    
    def __init__(self):
        self.base_path = settings.LOCAL_STORAGE_PATH or "app/static/images"
        self.base_url = settings.IMAGE_BASE_URL or "http://localhost:8001"
        
        # Ensure directory exists
        os.makedirs(self.base_path, exist_ok=True)
        print(f"[Storage] Local Storage initialized: {self.base_path}")
    
    async def save_image(self, image_data: bytes, filename: str, 
                        content_type: str = "image/png") -> str:
        """Save image to local filesystem"""
        filepath = os.path.join(self.base_path, filename)
        
        # Write file
        async with aiofiles.open(filepath, "wb") as f:
            await f.write(image_data)
        
        # Return public URL
        url = f"{self.base_url}/static/images/{filename}"
        print(f"[Storage] Saved locally: {filename} ({len(image_data)} bytes)")
        return url
    
    async def delete_image(self, url: str) -> bool:
        """Delete image from local filesystem"""
        try:
            filename = url.split("/")[-1]
            filepath = os.path.join(self.base_path, filename)
            
            if os.path.exists(filepath):
                os.remove(filepath)
                print(f"[Storage] Deleted: {filename}")
                return True
            return False
        except Exception as e:
            print(f"[Storage] Delete failed: {e}")
            return False
    
    async def get_image(self, url: str) -> bytes:
        """Load image from local filesystem"""
        filename = url.split("/")[-1]
        filepath = os.path.join(self.base_path, filename)
        
        if not os.path.exists(filepath):
            raise FileNotFoundError(f"Image not found: {filename}")
        
        async with aiofiles.open(filepath, "rb") as f:
            return await f.read()

