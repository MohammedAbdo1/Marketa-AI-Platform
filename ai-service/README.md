# Marketa AI Service

AI-powered campaign generation service for Marketa platform.

## Features

- **Campaign Planning**: AI-powered campaign structure generation
- **Content Writing**: Multi-language content generation (Arabic/English)
- **Image Generation**: AI-generated marketing images
- **Quality Review**: Automated content quality assessment
- **Brand Integration**: Smart color palette suggestions

## Setup

### 1. Install Dependencies

```bash
cd ai-service
pip install -r requirements.txt
```

### 2. Environment Variables

Create `.env` file:

```env
# Google Gemini API
GOOGLE_API_KEY=your-google-api-key-here

# OpenAI API (for premium features)
OPENAI_API_KEY=your-openai-api-key-here

# Stability AI API
STABILITY_API_KEY=your-stability-api-key-here

# FastAPI Settings
HOST=0.0.0.0
PORT=8001
DEBUG=True

# Laravel Backend URL
LARAVEL_BASE_URL=http://localhost:8000/api
```

### 3. Run the Service

```bash
python run.py
```

Service will be available at: `http://localhost:8000`

## API Endpoints

### Campaign Management
- `POST /api/campaign/preview` - Generate campaign structure
- `POST /api/campaign/generate` - Start full campaign generation

### Post Management
- `POST /api/post/regenerate-text` - Regenerate post text
- `POST /api/post/regenerate-image` - Regenerate post image

### Brand Management
- `POST /api/brand/suggest-colors` - AI color palette suggestions

### Health Check
- `GET /health` - Service health status

## Architecture

```
ai-service/
├── app/
│   ├── main.py              # FastAPI application
│   ├── agents/              # AI Agents
│   │   ├── planner.py       # Campaign Planner
│   │   ├── writer.py        # Content Writer
│   │   ├── image_gen.py     # Image Generator
│   │   └── reviewer.py      # Quality Reviewer
│   ├── services/            # External Services
│   │   ├── gemini.py        # Google Gemini
│   │   └── stability.py     # Stability AI
│   ├── models/              # Data Models
│   │   ├── campaign.py      # Campaign models
│   │   └── post.py         # Post models
│   └── prompts/             # AI Prompts
│       ├── planner_prompts.py
│       └── writer_prompts.py
├── config.py               # Configuration
├── requirements.txt         # Dependencies
└── run.py                  # Service runner
```

## AI Agents

### 1. Campaign Planner Agent
- Generates campaign structure
- Suggests content themes
- Creates posting schedule
- Provides color palette suggestions

### 2. Content Writer Agent
- Writes Arabic and English content
- Adjusts tone and style
- Optimizes hashtags
- Creates engaging CTAs

### 3. Image Generator Agent
- Generates marketing images
- Creates lifestyle visuals
- Produces infographics
- Maintains brand consistency

### 4. Quality Reviewer Agent
- Reviews content quality
- Ensures brand consistency
- Checks platform appropriateness
- Generates quality reports

## Integration with Laravel

The service communicates with Laravel backend via HTTP:

```php
// Laravel Service
class PythonAIService
{
    protected $baseUrl = 'http://localhost:8000/api';
    
    public function generateCampaignPreview($data)
    {
        return Http::post("{$this->baseUrl}/campaign/preview", $data);
    }
}
```

## Cost Optimization

- Uses Gemini 2.0 Flash (free tier) for basic generation
- Falls back to GPT-4o for premium features
- Implements prompt optimization
- Caches frequently used prompts

## Monitoring

- Health check endpoint
- Agent status monitoring
- Error logging and reporting
- Performance metrics

## Development

### Adding New Agents

1. Create agent class in `app/agents/`
2. Add to main.py initialization
3. Create API endpoints
4. Add prompts in `app/prompts/`

### Adding New Services

1. Create service class in `app/services/`
2. Add configuration in `config.py`
3. Update requirements.txt
4. Add environment variables

## Testing

```bash
# Test health endpoint
curl http://localhost:8000/health

# Test campaign preview
curl -X POST http://localhost:8000/api/campaign/preview \
  -H "Content-Type: application/json" \
  -d '{"business_type": "restaurant", "product_name": "Pizza Palace"}'
```

## Deployment

### Docker (Recommended)

```dockerfile
FROM python:3.11-slim

WORKDIR /app
COPY requirements.txt .
RUN pip install -r requirements.txt

COPY . .
EXPOSE 8001

CMD ["python", "run.py"]
```

### Manual Deployment

```bash
# Install dependencies
pip install -r requirements.txt

# Set environment variables
export GOOGLE_API_KEY=your-key
export STABILITY_API_KEY=your-key

# Run service
python run.py
```

## Troubleshooting

### Common Issues

1. **API Key Errors**: Check environment variables
2. **Import Errors**: Install missing dependencies
3. **Connection Errors**: Verify Laravel backend is running
4. **Memory Issues**: Reduce batch sizes in generation

### Logs

Service logs are available in console output. For production, configure proper logging.

## Support

For issues and questions:
- Check logs for error details
- Verify API keys are correct
- Ensure all dependencies are installed
- Test individual endpoints
