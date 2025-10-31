"""
Base Storage Abstract Class
Defines interface for different storage backends (Local, S3, etc.)
"""
from abc import ABC, abstractmethod
from typing import Optional


class BaseStorage(ABC):
    """Abstract base class for storage backends"""
    
    @abstractmethod
    async def save_image(self, image_data: bytes, filename: str, 
                        content_type: str = "image/png") -> str:
        """
        Save image and return public URL
        
        Args:
            image_data: Image bytes
            filename: Desired filename
            content_type: MIME type (default: image/png)
            
        Returns:
            Public URL to access the image
        """
        pass
    
    @abstractmethod
    async def delete_image(self, url: str) -> bool:
        """
        Delete image by URL
        
        Args:
            url: Image URL to delete
            
        Returns:
            True if deleted successfully, False otherwise
        """
        pass
    
    @abstractmethod
    async def get_image(self, url: str) -> bytes:
        """
        Download image by URL
        
        Args:
            url: Image URL to download
            
        Returns:
            Image bytes
        """
        pass

