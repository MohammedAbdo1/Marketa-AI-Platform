# Multi-stage build for production optimization
FROM python:3.11-slim as base

# Set environment variables
ENV PYTHONDONTWRITEBYTECODE=1 \
    PYTHONUNBUFFERED=1 \
    PIP_NO_CACHE_DIR=1 \
    PIP_DISABLE_PIP_VERSION_CHECK=1

# Install system dependencies
RUN apt-get update && apt-get install -y \
    gcc \
    g++ \
    curl \
    && rm -rf /var/lib/apt/lists/*

# Create app directory
WORKDIR /app

# Copy requirements first for better caching
COPY requirements.txt .

# Install Python dependencies
# Install in stages to avoid timeout on large packages
RUN pip install --no-cache-dir --timeout=300 fastapi==0.104.1 uvicorn[standard]==0.24.0 pydantic==2.5.0 python-dotenv==1.0.0
RUN pip install --no-cache-dir --timeout=300 google-generativeai==0.3.2 openai>=1.6.1 requests==2.31.0 aiohttp==3.9.1 httpx==0.25.2
RUN pip install --no-cache-dir --timeout=300 python-multipart==0.0.6 pillow==10.1.0
RUN pip install --no-cache-dir --timeout=300 arabic-reshaper==3.0.0 python-bidi==0.4.2 aiofiles==23.2.1
RUN pip install --no-cache-dir --timeout=600 numpy==1.24.3
RUN pip install --no-cache-dir --timeout=900 opencv-python-headless==4.8.1.78
RUN pip install --no-cache-dir --timeout=300 boto3==1.34.0 botocore==1.34.0
RUN pip install --no-cache-dir --timeout=300 redis>=5.0.0 celery>=5.3.0 celery[redis]
RUN pip install --no-cache-dir --timeout=300 python-socketio==5.9.0 python-engineio==4.7.0
RUN pip install --no-cache-dir --timeout=300 structlog>=23.2.0 loguru>=0.7.2

# Copy application code
COPY . .

# Create non-root user
RUN useradd --create-home --shell /bin/bash app \
    && chown -R app:app /app
USER app

# Expose port
EXPOSE 8001

# Health check
HEALTHCHECK --interval=30s --timeout=30s --start-period=5s --retries=3 \
    CMD curl -f http://localhost:8001/health || exit 1

# Run the application
# Use simple mode by default (no Redis/Celery required)
CMD ["python", "-m", "uvicorn", "app.main_simple:app", "--host", "0.0.0.0", "--port", "8001"]
