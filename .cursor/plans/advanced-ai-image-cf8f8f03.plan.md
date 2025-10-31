<!-- cf8f8f03-e424-4a37-875d-b2e56c472fa2 daeb6889-97b2-495f-bb75-7ef4d927d087 -->
# Advanced AI Image Composition & Editor System

## Overview

Transform the current simple image generation into an intelligent multi-layer composition system where:

1. AI Agent analyzes complex user descriptions and breaks them into components
2. Generate base images without text (Stability AI handles scenes only)
3. Compose elements (screens, logos, text) with proper perspective/positioning
4. Store as editable JSON layers + final rendered image
5. Provide Canva-like editor for post-generation modifications

## System Architecture

```
User Description
    ↓
[Composition Analysis Agent] (Gemini) - Breaks down into tasks
    ↓
├─ scene_prompt → Stability AI (base image)
├─ screen_content → Generate UI mockups
├─ text_overlays → [{text, position, color, style}]
└─ objects_to_composite → [{type, position}]
    ↓
[Image Composition Service] (Pillow + OpenCV)
    ↓
├─ Generate base scene
├─ Add screen mockups (perspective transform)
├─ Add text overlays (Arabic/English support)
└─ Compose final image
    ↓
Store: JSON layers (editable) + PNG (display)
    ↓
[Frontend Editor] (Fabric.js/Konva.js)
```

## Phase 1: Backend Foundation (AI Service)

### 1.1 Dependencies & Setup

**File:** `ai-service/requirements.txt`

- Add new dependencies:
```
opencv-python==4.8.1.78
opencv-python-headless==4.8.1.78
arabic-reshaper==3.0.0
python-bidi==0.4.2
numpy==1.24.3
boto3==1.34.0
botocore==1.34.0
```


**File:** `ai-service/config.py`

- Add image composition settings:
```python
IMAGE_COMPOSITION_ENABLED = True
MAX_IMAGE_LAYERS = 20
TEXT_OVERLAY_FONTS_PATH = "app/fonts/"
DEFAULT_ARABIC_FONT = "Cairo-Bold.ttf"
DEFAULT_ENGLISH_FONT = "Roboto-Bold.ttf"

# Storage Configuration - Switch easily between local and S3
STORAGE_BACKEND = os.getenv("STORAGE_BACKEND", "local")  # "local" or "s3"

# Local Storage Settings
LOCAL_STORAGE_PATH = os.getenv("LOCAL_STORAGE_PATH", "app/static/images")
IMAGE_BASE_URL = os.getenv("IMAGE_BASE_URL", "http://localhost:8001")

# S3 Configuration (only used if STORAGE_BACKEND=s3)
AWS_ACCESS_KEY_ID = os.getenv("AWS_ACCESS_KEY_ID")
AWS_SECRET_ACCESS_KEY = os.getenv("AWS_SECRET_ACCESS_KEY")
AWS_REGION = os.getenv("AWS_REGION", "us-east-1")
S3_BUCKET_NAME = os.getenv("S3_BUCKET_NAME")
CDN_URL = os.getenv("CDN_URL")  # CloudFront or S3 public URL
```


### 1.2 Storage Abstraction Layer (Flexible Local/S3)

**Purpose:** Allow seamless switching between local and S3 storage with minimal config changes

**New Files:**

- `ai-service/app/services/storage/base_storage.py` - Abstract base class
- `ai-service/app/services/storage/local_storage.py` - Local filesystem implementation
- `ai-service/app/services/storage/s3_storage.py` - AWS S3 implementation
- `ai-service/app/services/storage/__init__.py` - Factory function

**Benefits:**

- Development: Use local storage (fast, no costs)
- Production: Switch to S3 with one env variable change
- Consistent API for all storage operations
- Easy to add more backends (Azure, GCS) later

### 1.2 Storage Abstraction Layer (Flexible Local/S3)

**New File:** `ai-service/app/services/storage/base_storage.py`

```python
from abc import ABC, abstractmethod
from typing import Optional

class BaseStorage(ABC):
    @abstractmethod
    async def save_image(self, image_data: bytes, filename: str, 
                        content_type: str = "image/png") -> str:
        """Save image and return public URL"""
        pass
    
    @abstractmethod
    async def delete_image(self, url: str) -> bool:
        """Delete image by URL"""
        pass
    
    @abstractmethod
    async def get_image(self, url: str) -> bytes:
        """Download image by URL"""
        pass
```

**New File:** `ai-service/app/services/storage/local_storage.py`

```python
class LocalStorage(BaseStorage):
    def __init__(self):
        self.base_path = settings.LOCAL_STORAGE_PATH or "app/static/images"
        self.base_url = settings.IMAGE_BASE_URL or "http://localhost:8001"
        os.makedirs(self.base_path, exist_ok=True)
    
    async def save_image(self, image_data, filename, content_type):
        filepath = os.path.join(self.base_path, filename)
        with open(filepath, "wb") as f:
            f.write(image_data)
        return f"{self.base_url}/static/images/{filename}"
    
    async def delete_image(self, url: str) -> bool:
        filename = url.split("/")[-1]
        filepath = os.path.join(self.base_path, filename)
        if os.path.exists(filepath):
            os.remove(filepath)
            return True
        return False
```

**New File:** `ai-service/app/services/storage/s3_storage.py`

```python
import boto3
from botocore.exceptions import ClientError

class S3Storage(BaseStorage):
    def __init__(self):
        self.s3_client = boto3.client(
            's3',
            aws_access_key_id=settings.AWS_ACCESS_KEY_ID,
            aws_secret_access_key=settings.AWS_SECRET_ACCESS_KEY,
            region_name=settings.AWS_REGION
        )
        self.bucket = settings.S3_BUCKET_NAME
        self.cdn_url = settings.CDN_URL  # CloudFront/CDN URL
    
    async def save_image(self, image_data, filename, content_type):
        try:
            self.s3_client.put_object(
                Bucket=self.bucket,
                Key=f"images/{filename}",
                Body=image_data,
                ContentType=content_type,
                ACL='public-read'
            )
            return f"{self.cdn_url}/images/{filename}"
        except ClientError as e:
            raise Exception(f"S3 upload failed: {str(e)}")
    
    async def delete_image(self, url: str) -> bool:
        try:
            key = url.split(self.cdn_url)[-1].lstrip("/")
            self.s3_client.delete_object(Bucket=self.bucket, Key=key)
            return True
        except ClientError:
            return False
```

**New File:** `ai-service/app/services/storage/__init__.py`

```python
from config import settings
from .local_storage import LocalStorage
from .s3_storage import S3Storage

def get_storage():
    """Factory function - returns storage based on config"""
    if settings.STORAGE_BACKEND == "s3":
        return S3Storage()
    else:
        return LocalStorage()

# Singleton instance
storage = get_storage()
```

**Update:** `ai-service/config.py`

```python
# Storage Configuration - Switch easily between local and S3
STORAGE_BACKEND = os.getenv("STORAGE_BACKEND", "local")  # "local" or "s3"

# Local Storage Settings
LOCAL_STORAGE_PATH = os.getenv("LOCAL_STORAGE_PATH", "app/static/images")
IMAGE_BASE_URL = os.getenv("IMAGE_BASE_URL", "http://localhost:8001")

# S3 Configuration (only used if STORAGE_BACKEND=s3)
AWS_ACCESS_KEY_ID = os.getenv("AWS_ACCESS_KEY_ID")
AWS_SECRET_ACCESS_KEY = os.getenv("AWS_SECRET_ACCESS_KEY")
AWS_REGION = os.getenv("AWS_REGION", "us-east-1")
S3_BUCKET_NAME = os.getenv("S3_BUCKET_NAME")
CDN_URL = os.getenv("CDN_URL")  # CloudFront or S3 public URL
```

**Update:** `ai-service/requirements.txt` - Add S3 support

```
boto3==1.34.0
botocore==1.34.0
```

**Environment Examples:**

Development (.env):

```
STORAGE_BACKEND=local
LOCAL_STORAGE_PATH=app/static/images
IMAGE_BASE_URL=http://localhost:8001
```

Production (.env):

```
STORAGE_BACKEND=s3
AWS_ACCESS_KEY_ID=your_access_key
AWS_SECRET_ACCESS_KEY=your_secret_key
AWS_REGION=us-east-1
S3_BUCKET_NAME=marketa-production-images
CDN_URL=https://cdn.marketa.com
```

**Usage in Code (same for both!):**

```python
from app.services.storage import storage

# Save image - works for both local and S3
image_url = await storage.save_image(image_bytes, filename, "image/png")

# Delete image
await storage.delete_image(image_url)

# Download image
image_data = await storage.get_image(image_url)
```

### 1.3 Composition Analysis Agent

**New File:** `ai-service/app/agents/composition_analyzer.py`

- Create `CompositionAnalyzerAgent` class
- Method: `analyze_description(user_description: str) -> dict`
  - Uses Gemini to parse complex descriptions
  - Returns structured JSON:
    ```json
    {
      "scene_description": "base scene without text",
      "screen_content": "what appears on screens",
      "text_overlays": [
        {"text": "نظام الخياطة ERP", "position": "bottom-center", 
         "color": "#FF0000", "font_size": 48, "font_weight": "bold"}
      ],
      "objects_to_composite": [
        {"type": "screen_mockup", "position": "center-screen", "size": "30%"}
      ],
      "image_style": "professional, corporate, modern"
    }
    ```

- Prompt engineering for accurate Arabic/English text detection
- Handle edge cases (ambiguous requests, conflicting requirements)

### 1.3 Image Composition Service

**New File:** `ai-service/app/services/image_compositor.py`

- Create `ImageCompositor` class with methods:

**Core Methods:**

1. `compose_image(analysis: dict, base_image: bytes) -> ComposedImage`

   - Orchestrates the entire composition process

2. `add_text_overlay(image, text, position, color, font, size) -> image`

   - Supports Arabic (RTL) and English (LTR)
   - Uses arabic-reshaper + python-bidi for proper rendering
   - Anti-aliasing for crisp text
   - Shadow/outline options

3. `add_screen_mockup(base_image, mockup_image, position, perspective) -> image`

   - Uses OpenCV perspective transform
   - Detects screen area in base image (optional)
   - Applies mockup with correct perspective

4. `save_as_layers(composed_image) -> dict`

   - Exports to JSON format:
     ```json
     {
       "base_image_url": "https://...",
       "layers": [
         {"type": "text", "content": "...", "position": {"x": 100, "y": 500}, 
          "style": {"color": "#FF0000", "fontSize": 48}},
         {"type": "image", "url": "...", "position": {...}, "transform": {...}}
       ],
       "dimensions": {"width": 1024, "height": 1024}
     }
     ```


**Helper Methods:**

- `get_text_dimensions(text, font, size)` - Calculate bounding box
- `apply_perspective_transform(image, points)` - OpenCV warping
- `blend_images(base, overlay, position, opacity)` - Alpha compositing

**Font Setup:**

- Create `ai-service/app/fonts/` directory
- Include Cairo (Arabic) and Roboto (English) fonts
- Font loading and caching mechanism

### 1.4 Updated Image Generator Agent

**File:** `ai-service/app/agents/image_gen.py`

- Modify `generate_image()` to accept `analysis` parameter
- New method: `generate_composed_image(analysis: dict, prompt: str)`
  - Calls Stability AI for base scene (NO text in prompt)
  - Passes result to ImageCompositor
  - Returns both: final image URL + layers JSON

**Flow:**

```python
async def generate_composed_image(self, analysis):
    # 1. Generate base scene (no text)
    base_prompt = analysis['scene_description'] + " " + analysis['image_style']
    base_image_url = await self.stability.generate_image(base_prompt)
    
    # 2. Download base image
    base_image = await self._download_image(base_image_url)
    
    # 3. Compose layers
    compositor = ImageCompositor()
    composed = await compositor.compose_image(analysis, base_image)
    
    # 4. Save final image + layers
    final_url = await self._save_image(composed.final_image)
    layers_json = composed.to_json()
    
    return {
        "final_image_url": final_url,
        "layers": layers_json,
        "base_image_url": base_image_url
    }
```

### 1.5 API Endpoints

**File:** `ai-service/app/main.py`

- Add new endpoints:
```python
@app.post("/api/post/analyze-description")
async def analyze_description(request: DescriptionAnalysisRequest):
    """Analyze user description and return composition plan"""
    
@app.post("/api/post/generate-composed")
async def generate_composed_image(request: ComposedImageRequest):
    """Generate image with multi-layer composition"""
    
@app.post("/api/post/regenerate-layer")
async def regenerate_layer(request: LayerRegenerationRequest):
    """Regenerate specific layer (text/image) without affecting others"""
```


## Phase 2: Database Schema Updates

### 2.1 Migration: Add JSON Layers Support

**New File:** `backend/database/migrations/2025_11_01_000001_add_composition_layers_to_posts.php`

```php
Schema::table('campaign_posts', function (Blueprint $table) {
    $table->json('composition_layers')->nullable()->after('media_urls');
    $table->string('base_image_url')->nullable()->after('composition_layers');
    $table->boolean('is_composed')->default(false)->after('base_image_url');
    $table->json('composition_analysis')->nullable(); // Store original analysis
});

Schema::table('post_versions', function (Blueprint $table) {
    $table->json('composition_layers')->nullable();
    $table->string('base_image_url')->nullable();
});
```

### 2.2 Model Updates

**File:** `backend/app/Models/CampaignPost.php`

- Add casts:
```php
protected $casts = [
    'composition_layers' => 'array',
    'composition_analysis' => 'array',
    'is_composed' => 'boolean',
];
```

- Add methods:
```php
public function getEditableLayers(): array
public function updateLayer(int $layerIndex, array $newData): void
public function exportForEditor(): array
```


## Phase 3: Laravel Backend Integration

### 3.1 Updated AI Service

**File:** `backend/app/Services/PythonAIService.php`

Add methods:

```php
public function analyzeImageDescription(string $description): array
{
    $response = Http::timeout($this->timeout)
        ->post("{$this->baseUrl}/post/analyze-description", [
            'description' => $description
        ]);
    return $response->json();
}

public function generateComposedImage(array $analysisData): array
{
    $response = Http::timeout($this->timeout)
        ->post("{$this->baseUrl}/post/generate-composed", $analysisData);
    return $response->json();
}

public function regenerateLayer(int $postId, int $layerIndex, array $changes): array
{
    // Call Python service to regenerate specific layer
}
```

### 3.2 Post Controller Updates

**File:** `backend/app/Http/Controllers/Api/CampaignPostController.php`

New methods:

```php
public function updateLayer(Request $request, CampaignPost $post, int $layerIndex)
{
    // Update specific layer (text, position, color, etc.)
    // Regenerate final image if needed
}

public function addLayer(Request $request, CampaignPost $post)
{
    // Add new text/image layer
}

public function removeLayer(Request $request, CampaignPost $post, int $layerIndex)
{
    // Remove layer and regenerate
}

public function exportLayers(CampaignPost $post)
{
    // Export post as editable JSON for frontend editor
}

public function importLayers(Request $request, CampaignPost $post)
{
    // Import edited layers from frontend editor
}
```

### 3.3 API Routes

**File:** `backend/routes/api.php`

```php
// Post Composition & Editing
Route::prefix('campaign-posts/{post}')->group(function () {
    Route::get('/layers', [CampaignPostController::class, 'exportLayers']);
    Route::post('/layers', [CampaignPostController::class, 'addLayer']);
    Route::put('/layers/{layerIndex}', [CampaignPostController::class, 'updateLayer']);
    Route::delete('/layers/{layerIndex}', [CampaignPostController::class, 'removeLayer']);
    Route::post('/layers/import', [CampaignPostController::class, 'importLayers']);
    Route::post('/layers/{layerIndex}/regenerate', [CampaignPostController::class, 'regenerateLayer']);
});
```

## Phase 4: Frontend Editor (Vue 3)

### 4.1 Dependencies

**File:** `frontend-user/package.json`

```json
{
  "fabric": "^5.3.0",
  "konva": "^9.2.0",
  "vue-konva": "^3.0.2"
}
```

**Decision:** Use **Fabric.js** (more mature, better text handling, easier Arabic support)

### 4.2 Editor Component

**New File:** `frontend-user/src/components/editor/PostEditor.vue`

- Canvas-based editor using Fabric.js
- Features:
  - Drag & drop layers
  - Text editing (inline + panel)
  - Color picker
  - Font selection (Arabic/English fonts)
  - Layer management (reorder, delete, duplicate)
  - Undo/Redo (15 history states)
  - Zoom & Pan
  - Export to PNG/JSON

**Key Methods:**

```javascript
initializeCanvas(compositionLayers)
addTextLayer(text, position, style)
updateTextLayer(layerId, newText, newStyle)
moveLayer(layerId, newPosition)
deleteLayer(layerId)
exportToJSON()
exportToPNG()
saveChanges() // Call backend API
```

### 4.3 Editor Store

**New File:** `frontend-user/src/stores/postEditor.js`

```javascript
export const usePostEditorStore = defineStore('postEditor', {
  state: () => ({
    currentPost: null,
    layers: [],
    history: [],
    historyIndex: 0,
    isDirty: false
  }),
  actions: {
    async loadPost(postId),
    async savePost(),
    addLayer(layer),
    updateLayer(layerId, changes),
    deleteLayer(layerId),
    undo(),
    redo(),
    exportAsImage()
  }
})
```

### 4.4 Editor View

**New File:** `frontend-user/src/views/dashboard/campaigns/PostEditor.vue`

- Full-screen editor interface
- Layout:
  - Left sidebar: Layers panel
  - Center: Canvas
  - Right sidebar: Properties panel (text, color, font, position)
  - Top toolbar: Save, Export, Undo/Redo, Zoom
- Route: `/dashboard/campaigns/:campaignId/posts/:postId/edit`

### 4.5 Integration with Campaign Details

**File:** `frontend-user/src/views/dashboard/campaigns/CampaignDetails.vue`

- Add "Edit" button on each post card
- Click → Navigate to PostEditor
- Show "Editable" badge on composed posts

## Phase 5: Enhanced Writer Agent

### 5.1 Smart Description Analysis

**File:** `ai-service/app/agents/writer.py`

Update `generate_posts()`:

```python
async def generate_posts(self, structure, request):
    posts = []
    for post_data in structure:
        # Generate content
        content = await self._generate_content(post_data)
        
        # NEW: Analyze if description needs composition
        needs_composition = await self._check_composition_needed(
            request.description, 
            content
        )
        
        if needs_composition:
            # Call composition analyzer
            analysis = await composition_analyzer.analyze_description(
                f"{request.description}. Post content: {content}"
            )
            posts.append({
                **content,
                "needs_composition": True,
                "composition_analysis": analysis
            })
        else:
            # Simple image generation (current flow)
            posts.append({
                **content,
                "needs_composition": False,
                "image_prompt": self._build_simple_prompt(content)
            })
    
    return posts
```

### 5.2 Composition Detection Logic

**Method:** `_check_composition_needed()`

- Detect keywords: "اكتب", "نص", "شاشة", "text", "caption", "screen"
- Detect color specifications: "باللون الأحمر", "in red color"
- Detect complex layouts: "في الأعلى", "أسفل الصورة", "at the bottom"
- Returns `True` if composition is needed

## Phase 6: Job Processing Updates

### 6.1 Campaign Generation Job

**File:** `backend/app/Jobs/GenerateCampaignPosts.php`

Update `handle()`:

```php
foreach ($posts as $postData) {
    if ($postData['needs_composition'] ?? false) {
        // Generate composed image
        $composedResult = $this->aiService->generateComposedImage(
            $postData['composition_analysis']
        );
        
        CampaignPost::create([
            // ... existing fields ...
            'media_urls' => [$composedResult['final_image_url']],
            'base_image_url' => $composedResult['base_image_url'],
            'composition_layers' => $composedResult['layers'],
            'composition_analysis' => $postData['composition_analysis'],
            'is_composed' => true,
        ]);
    } else {
        // Simple generation (current flow)
    }
}
```

## Phase 7: Testing & Validation

### 7.1 Test Cases

**Create:** `ai-service/tests/test_composition.py`

- Test Arabic text rendering
- Test RTL/LTR mixing
- Test perspective transforms
- Test layer ordering
- Test JSON export/import

**Create:** `backend/tests/Feature/PostCompositionTest.php`

- Test layer CRUD operations
- Test regeneration
- Test editor export/import

### 7.2 Manual Testing Scenarios

1. **Arabic text overlay:**

   - Input: "صورة جميلة، اكتب 'مرحباً' باللون الأزرق في الأعلى"
   - Expected: Blue Arabic text at top, proper RTL rendering

2. **Screen mockup composition:**

   - Input: "رجل يشير إلى شاشة كمبيوتر، في الشاشة dashboard"
   - Expected: Person + computer scene, dashboard UI on screen

3. **Complex multi-layer:**

   - Input: "مطعم، لوجو في الزاوية، نص 'افتتاح قريب' بالأحمر أسفل الصورة"
   - Expected: Restaurant scene, logo overlay, red Arabic text at bottom

## Phase 8: Standalone Projects (Future)

### 8.1 Schema Extension

**Migration:** `2025_11_15_000001_add_standalone_project_support.php`

```php
Schema::table('campaign_posts', function (Blueprint $table) {
    $table->boolean('is_standalone')->default(false);
    $table->string('project_name')->nullable();
    // campaign_id becomes nullable for standalone posts
});
```

### 8.2 UI Updates

- Add "Create Design" button in dashboard
- Create `StandaloneProjectWizard.vue` (similar to CampaignWizard but simpler)
- Reuse same editor, services, and APIs

## Implementation Order & Estimates

### Week 1-2: Backend Foundation

- [x] Add dependencies (opencv, arabic-reshaper)
- [x] Build CompositionAnalyzerAgent
- [x] Build ImageCompositor service
- [x] Add fonts and test Arabic rendering
- [x] Update ImageGeneratorAgent
- [x] Create API endpoints

### Week 3: Database & Laravel Integration

- [x] Create migration
- [x] Update models
- [x] Update PythonAIService
- [x] Update CampaignPostController
- [x] Add routes
- [x] Update GenerateCampaignPosts job

### Week 4-5: Frontend Editor

- [x] Install Fabric.js
- [x] Build PostEditor component
- [x] Build editor store
- [x] Create editor view
- [x] Integrate with CampaignDetails

### Week 6: Testing & Polish

- [x] Write automated tests
- [x] Manual testing with real scenarios
- [x] Fix Arabic rendering issues
- [x] Optimize performance
- [x] Documentation

### Future (Week 7+): Standalone Projects

- [x] Schema updates
- [x] Standalone UI
- [x] Reuse existing editor

## Success Metrics

- ✅ Posts with Arabic text render correctly (RTL)
- ✅ Complex descriptions parse accurately (>90%)
- ✅ Editor saves changes without data loss
- ✅ Image composition completes in <30 seconds
- ✅ Users can edit posts without re-generating from scratch
- ✅ JSON layers export/import works flawlessly

## Risk Mitigation

1. **Arabic font rendering issues:**

   - Solution: Test with multiple fonts, include fallbacks

2. **Perspective transform accuracy:**

   - Solution: Allow manual adjustment in editor

3. **Large file sizes (layers + final image):**

   - Solution: Compress JSON, use WebP for images, CDN caching

4. **Editor performance with many layers:**

   - Solution: Limit max layers (20), optimize Fabric.js rendering

## Notes

- All image URLs stored will be CDN-ready (S3/similar)
- Fonts licensed for commercial use (Cairo, Roboto = Open Font License)
- Canvas exports at 2x resolution for Retina displays
- Composition analysis cached for 1 hour per unique description