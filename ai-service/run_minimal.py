"""
Minimal AI Service - For Quick Testing
"""
from fastapi import FastAPI
from fastapi.middleware.cors import CORSMiddleware
import uvicorn

app = FastAPI(title="Marketa AI - Minimal")

app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

@app.get("/health")
def health():
    return {"status": "healthy", "service": "Marketa AI Service", "version": "1.0.0"}

@app.post("/api/campaign/preview")
def preview(data: dict):
    return {
        "total_posts": 6,
        "posts": [
            {
                "platform": "instagram",
                "post_type": "text",
                "content_ar": "محتوى تجريبي",
                "content_en": "Test content",
                "hashtags": ["#test"],
                "image_prompt": "test image",
                "week": 1,
                "day": i+1
            } for i in range(6)
        ]
    }

if __name__ == "__main__":
    print("\n" + "="*60)
    print("MINIMAL AI SERVICE - FOR TESTING")
    print("="*60)
    print("Running on: http://localhost:8001")
    print("="*60 + "\n")
    
    uvicorn.run(app, host="0.0.0.0", port=8001, log_level="info")

