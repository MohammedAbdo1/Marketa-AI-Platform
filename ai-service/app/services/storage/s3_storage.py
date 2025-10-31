"""
S3 Storage Implementation
Stores files on AWS S3
"""
import boto3
from botocore.exceptions import ClientError
from .base_storage import BaseStorage
from config import settings


class S3Storage(BaseStorage):
    """AWS S3 storage implementation"""
    
    def __init__(self):
        """Initialize S3 client"""
        if not settings.AWS_ACCESS_KEY_ID or not settings.AWS_SECRET_ACCESS_KEY:
            raise ValueError(
                "AWS credentials not configured. "
                "Set AWS_ACCESS_KEY_ID and AWS_SECRET_ACCESS_KEY environment variables."
            )
        
        if not settings.S3_BUCKET_NAME:
            raise ValueError("S3_BUCKET_NAME not configured")
        
        self.s3_client = boto3.client(
            's3',
            aws_access_key_id=settings.AWS_ACCESS_KEY_ID,
            aws_secret_access_key=settings.AWS_SECRET_ACCESS_KEY,
            region_name=settings.AWS_REGION
        )
        self.bucket = settings.S3_BUCKET_NAME
        self.cdn_url = settings.CDN_URL or f"https://{self.bucket}.s3.{settings.AWS_REGION}.amazonaws.com"
        
        print(f"[Storage] S3 Storage initialized: {self.bucket} ({settings.AWS_REGION})")
    
    async def save_image(self, image_data: bytes, filename: str, 
                        content_type: str = "image/png") -> str:
        """Upload image to S3"""
        try:
            key = f"images/{filename}"
            
            self.s3_client.put_object(
                Bucket=self.bucket,
                Key=key,
                Body=image_data,
                ContentType=content_type,
                ACL='public-read',  # Make publicly accessible
                CacheControl='max-age=31536000'  # Cache for 1 year
            )
            
            url = f"{self.cdn_url}/{key}"
            print(f"[Storage] Uploaded to S3: {filename} ({len(image_data)} bytes)")
            return url
            
        except ClientError as e:
            error_msg = f"S3 upload failed: {str(e)}"
            print(f"[Storage] ERROR: {error_msg}")
            raise Exception(error_msg)
    
    async def delete_image(self, url: str) -> bool:
        """Delete image from S3"""
        try:
            # Extract key from URL
            key = url.split(self.cdn_url)[-1].lstrip("/")
            
            self.s3_client.delete_object(
                Bucket=self.bucket,
                Key=key
            )
            
            print(f"[Storage] Deleted from S3: {key}")
            return True
            
        except ClientError as e:
            print(f"[Storage] S3 delete failed: {e}")
            return False
    
    async def get_image(self, url: str) -> bytes:
        """Download image from S3"""
        try:
            # Extract key from URL
            key = url.split(self.cdn_url)[-1].lstrip("/")
            
            response = self.s3_client.get_object(
                Bucket=self.bucket,
                Key=key
            )
            
            return response['Body'].read()
            
        except ClientError as e:
            raise Exception(f"S3 download failed: {str(e)}")

