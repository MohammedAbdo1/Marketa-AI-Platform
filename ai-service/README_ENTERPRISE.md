# Marketa AI Service - Enterprise Edition

## 🚀 Enterprise-Grade AI Platform

This is a production-ready AI service built with enterprise-grade architecture to handle millions of requests efficiently, just like ChatGPT and Gemini platforms.

## 🏗️ Architecture Overview

### Core Components

1. **FastAPI Backend** - High-performance async API
2. **Redis** - Queue system, caching, and rate limiting
3. **Celery Workers** - Background task processing
4. **WebSocket** - Real-time updates
5. **PostgreSQL** - Data persistence
6. **Docker** - Containerization and scaling

### Key Features

- ✅ **Async Processing** - All requests processed in background
- ✅ **Redis Caching** - Intelligent caching for faster responses
- ✅ **Rate Limiting** - Multi-tier rate limiting (Free/Pro/Enterprise)
- ✅ **WebSocket Updates** - Real-time progress notifications
- ✅ **Horizontal Scaling** - Multiple workers and API instances
- ✅ **Health Monitoring** - Comprehensive health checks
- ✅ **Structured Logging** - Production-ready logging
- ✅ **Load Balancing** - Nginx load balancer
- ✅ **Performance Testing** - Built-in performance testing

## 🚀 Quick Start

### Development Setup

```bash
# Clone the repository
git clone <repository-url>
cd Marketa-ai-platform

# Start development environment
docker-compose up -d

# Install dependencies
cd ai-service
pip install -r requirements.txt

# Start the service
python -m uvicorn app.main:app --host 0.0.0.0 --port 8001 --reload
```

### Production Setup

```bash
# Start production environment
docker-compose -f docker-compose.prod.yml up -d

# Scale workers
docker-compose -f docker-compose.prod.yml up --scale celery-worker-1=3 --scale celery-worker-2=3 -d
```

## 📊 Performance Characteristics

### Expected Performance

- **Concurrent Users**: 10,000+ simultaneous users
- **Request Rate**: 100,000+ requests per minute
- **Response Time**: < 2 seconds for cached requests
- **Task Processing**: 1,000+ background tasks per minute
- **Cache Hit Rate**: 80%+ for repeated requests

### Scaling Capabilities

- **API Instances**: Scale to 10+ instances
- **Celery Workers**: Scale to 50+ workers
- **Redis Cluster**: Master-slave replication
- **Database**: Read replicas for scaling

## 🔧 Configuration

### Environment Variables

```bash
# Core Settings
DEBUG=False
HOST=0.0.0.0
PORT=8001

# Redis Configuration
REDIS_URL=redis://localhost:6379/0
REDIS_HOST=localhost
REDIS_PORT=6379

# Celery Configuration
CELERY_BROKER_URL=redis://localhost:6379/0
CELERY_RESULT_BACKEND=redis://localhost:6379/0

# Rate Limiting
RATE_LIMIT_FREE=10
RATE_LIMIT_PRO=100
RATE_LIMIT_ENTERPRISE=1000

# Cache Settings
CACHE_TTL=3600
CACHE_PREFIX=marketa_ai:

# AI Settings
AI_REQUEST_TIMEOUT=30
MAX_OUTPUT_TOKENS=1000
```

## 🛠️ API Endpoints

### Core Endpoints

- `POST /api/campaign/preview` - Generate campaign preview
- `POST /api/campaign/generate` - Generate full campaign
- `POST /api/post/regenerate-text` - Regenerate post text
- `POST /api/post/regenerate-image` - Regenerate post image
- `POST /api/brand/suggest-colors` - Suggest brand colors

### Task Management

- `GET /api/task/status/{task_id}` - Get task status
- `GET /api/task/result/{task_id}` - Get task result

### Health & Monitoring

- `GET /health` - Service health
- `GET /health/redis` - Redis health
- `GET /health/workers` - Celery workers health
- `GET /api/rate-limit/stats/{user_id}` - Rate limit stats

## 🔄 Request Flow

### 1. Campaign Generation Flow

```
Client Request → FastAPI → Rate Limiter → Redis Queue → Celery Worker → AI Agents → Cache → WebSocket Update → Response
```

### 2. Caching Strategy

- **AI Results**: Cached by prompt hash
- **Campaign Data**: Cached by campaign ID
- **User Limits**: Cached by user ID
- **TTL**: 1 hour default, configurable

### 3. Rate Limiting

- **Free Tier**: 10 requests/hour
- **Pro Tier**: 100 requests/hour
- **Enterprise**: 1000 requests/hour
- **Sliding Window**: 1-hour window

## 📈 Monitoring & Observability

### Health Checks

- Service health monitoring
- Redis connection status
- Celery worker status
- Database connectivity

### Logging

- Structured JSON logging
- Request/response tracking
- Performance metrics
- Error tracking

### Metrics

- Request rate
- Response times
- Cache hit rates
- Task completion rates
- Error rates

## 🧪 Performance Testing

### Run Performance Tests

```bash
# Basic performance test
python test_performance.py

# High load test
python test_performance.py --users 100 --requests 20

# Custom URL test
python test_performance.py --url http://your-domain.com --users 200
```

### Test Results Interpretation

- **A+ Grade**: < 2s response, > 95% success
- **A Grade**: < 3s response, > 90% success
- **B Grade**: < 5s response, > 80% success
- **C Grade**: < 10s response, > 70% success

## 🔧 Troubleshooting

### Common Issues

1. **Redis Connection Failed**
   ```bash
   # Check Redis status
   docker-compose logs redis
   
   # Restart Redis
   docker-compose restart redis
   ```

2. **Celery Workers Not Processing**
   ```bash
   # Check worker status
   docker-compose logs celery_worker
   
   # Restart workers
   docker-compose restart celery_worker
   ```

3. **High Memory Usage**
   ```bash
   # Scale down workers
   docker-compose up --scale celery_worker=2 -d
   ```

### Performance Optimization

1. **Increase Cache TTL**
   ```bash
   CACHE_TTL=7200  # 2 hours
   ```

2. **Add More Workers**
   ```bash
   docker-compose up --scale celery_worker=5 -d
   ```

3. **Optimize Redis**
   ```bash
   # Increase Redis memory
   redis-server --maxmemory 2gb --maxmemory-policy allkeys-lru
   ```

## 🚀 Deployment

### Production Deployment

1. **Set Environment Variables**
2. **Configure SSL/TLS**
3. **Set up Monitoring**
4. **Configure Load Balancer**
5. **Set up Database Replication**

### Scaling Strategy

1. **Horizontal Scaling**: Add more API instances
2. **Worker Scaling**: Add more Celery workers
3. **Database Scaling**: Add read replicas
4. **Cache Scaling**: Redis cluster setup

## 📊 Monitoring Dashboard

Access monitoring tools:

- **Celery Flower**: http://localhost:5555
- **Grafana**: http://localhost:3000 (admin/admin)
- **Prometheus**: http://localhost:9090

## 🔒 Security

- Rate limiting per user
- Request validation
- CORS configuration
- Security headers
- Input sanitization

## 📝 Logs

### Log Locations

- **Application Logs**: Docker logs
- **Structured Logs**: JSON format
- **Performance Logs**: Response times
- **Error Logs**: Exception tracking

### Log Analysis

```bash
# View logs
docker-compose logs -f api

# Filter error logs
docker-compose logs api | grep ERROR

# Performance analysis
docker-compose logs api | grep "duration"
```

## 🎯 Best Practices

1. **Monitor Cache Hit Rates** - Aim for 80%+
2. **Set Appropriate Rate Limits** - Based on user tiers
3. **Monitor Worker Health** - Restart if needed
4. **Regular Performance Testing** - Weekly load tests
5. **Database Optimization** - Index frequently queried fields

## 📞 Support

For enterprise support and custom configurations, contact the development team.

---

**Built with ❤️ for Enterprise Scale**
