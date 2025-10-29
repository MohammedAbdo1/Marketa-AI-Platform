<!-- af3eb92a-2ee7-46cd-b4bf-7e8da0c3a530 f72e3ee3-c3e9-49aa-8bb7-3a39a22edb3c -->
# خطة AI Stack لمنصة Marketa

## المتطلبات الأساسية

منصة Marketa تحتاج إلى 4 مكونات رئيسية:

1. **Text Generation** - توليد النصوص التسويقية (عربي/إنجليزي)
2. **Image Generation** - تصميم الصور والمنشورات
3. **AI Agent System** - تنسيق المهام ودمج النصوص مع الصور
4. **Video Generation** - إنشاء فيديوهات قصيرة (مستقبلاً)

---

## 1. Text Generation Models

### الخيارات المتاحة:

#### أ) OpenAI GPT-4o

- **المميزات:**
  - أفضل جودة للغة العربية حالياً
  - سرعة عالية (GPT-4o faster than GPT-4)
  - دعم 128K tokens context
  - API مستقرة ومدعومة جيداً
- **التكلفة:**
  - Input: $2.50 / 1M tokens
  - Output: $10.00 / 1M tokens
  - مثال: منشور 500 كلمة (~700 tokens) = $0.007
- **التقييم:** ⭐⭐⭐⭐⭐ (الأفضل للإنتاج)

#### ب) Claude 3.5 Sonnet (Anthropic)

- **المميزات:**
  - جودة عالية جداً في الكتابة الإبداعية
  - 200K tokens context
  - أفضل في فهم التعليمات المعقدة
  - دعم جيد للعربية (لكن أقل من GPT-4)
- **التكلفة:**
  - Input: $3.00 / 1M tokens
  - Output: $15.00 / 1M tokens
  - مثال: نفس المنشور = $0.0105
- **التقييم:** ⭐⭐⭐⭐ (ممتاز لكن أغلى)

#### ج) Google Gemini 2.0 Flash

- **المميزات:**
  - **مجاني** حتى 1500 requests/day
  - سريع جداً
  - دعم جيد للعربية
  - 1M tokens context
- **التكلفة:**
  - Free tier: 1500 requests/day
  - Paid: $0.075 / 1M input tokens, $0.30 / 1M output tokens
  - مثال: نفس المنشور = $0.0002 (أرخص بـ 35x من GPT-4)
- **التقييم:** ⭐⭐⭐⭐⭐ (أفضل للبداية - مجاني!)

#### د) Open Source (LLaMA 3.1 / Mixtral)

- **المميزات:**
  - تكلفة صفر (self-hosted)
  - خصوصية كاملة
- **العيوب:**
  - يحتاج GPU servers (مكلف)
  - دعم ضعيف للعربية
  - صيانة معقدة
- **التقييم:** ⭐⭐ (غير مناسب الآن)

### التوصية:

**Gemini 2.0 Flash للبداية** → ثم **GPT-4o عند الحاجة** للجودة العالية

---

## 2. Image Generation APIs

### الخيارات المتاحة:

#### أ) DALL-E 3 (OpenAI)

- **المميزات:**
  - جودة ممتازة
  - فهم جيد للـ Arabic prompts
  - دقة عالية في التفاصيل
- **التكلفة:**
  - Standard (1024x1024): $0.040 / image
  - HD (1024x1792): $0.080 / image
- **السرعة:** 10-20 ثانية/صورة
- **التقييم:** ⭐⭐⭐⭐

#### ب) Stability AI (Stable Diffusion 3)

- **المميزات:**
  - أرخص من DALL-E
  - جودة عالية
  - API سريعة
- **التكلفة:**
  - Core (1024x1024): $0.03 / image
  - Ultra (1024x1024): $0.08 / image
- **السرعة:** 5-10 ثوانٍ/صورة
- **التقييم:** ⭐⭐⭐⭐⭐ (أفضل قيمة مقابل السعر)

#### ج) Midjourney

- **المميزات:**
  - أفضل جودة فنية
  - نتائج إبداعية
- **العيوب:**
  - لا يوجد API رسمي (فقط Discord bot)
  - صعوبة التكامل
- **التكلفة:** $10-60/month (اشتراك شهري)
- **التقييم:** ⭐⭐ (غير عملي للإنتاج)

#### د) Flux (Black Forest Labs)

- **المميزات:**
  - Open source
  - جودة ممتازة
  - يمكن self-hosting
- **التكلفة:**
  - API: $0.025 / image (Replicate)
  - Self-hosted: مجاني (لكن يحتاج GPU)
- **التقييم:** ⭐⭐⭐⭐

### التوصية:

**Stability AI** (أفضل توازن بين السعر والجودة)

---

## 3. AI Agent Framework

### الخيارات المتاحة:

#### أ) LangGraph (LangChain)

- **المميزات:**
  - Framework قوي للـ Multi-agent systems
  - دعم State management
  - مجاني ومفتوح المصدر
  - مجتمع كبير
- **التكلفة:** مجاني (تدفع فقط لـ LLM APIs)
- **Use Case:**
  - Agent 1: يكتب النص
  - Agent 2: يولد الصورة
  - Agent 3: يدمج النص + الصورة
  - Agent 4: يراجع ويحسن
- **التقييم:** ⭐⭐⭐⭐⭐

#### ب) AutoGPT / CrewAI

- **المميزات:**
  - سهل الاستخدام
  - مناسب للمهام البسيطة
- **العيوب:**
  - أقل مرونة من LangGraph
  - استهلاك عالي للـ tokens
- **التقييم:** ⭐⭐⭐

#### ج) Custom Agent System (Laravel + Python)

- **المميزات:**
  - تحكم كامل
  - تكامل مباشر مع Backend
- **العيوب:**
  - يحتاج وقت تطوير
  - maintenance overhead
- **التقييم:** ⭐⭐⭐

### التوصية:

**LangGraph** (الأفضل للتطوير السريع والمرونة)

---

## 4. Prompt Optimization Tools

### الخيارات:

#### أ) LangSmith (من LangChain)

- **المميزات:**
  - مراقبة وتحليل Prompts
  - A/B testing
  - تتبع التكاليف
- **التكلفة:**
  - Free: 5K traces/month
  - Paid: $39/month (50K traces)
- **التقييم:** ⭐⭐⭐⭐⭐

#### ب) PromptLayer

- **المميزات:**
  - تتبع جميع API calls
  - Version control للـ prompts
- **التكلفة:**
  - Free: 1K requests/month
  - Paid: $29/month
- **التقييم:** ⭐⭐⭐⭐

#### ج) Manual Prompt Engineering

- **المميزات:**
  - مجاني تماماً
  - تحكم كامل
- **العيوب:**
  - يحتاج خبرة
  - صعوبة التتبع
- **التقييم:** ⭐⭐⭐

### التوصية:

**LangSmith Free tier** للبداية

---

## 5. Video Generation (Future)

### الخيارات (للمرحلة القادمة):

#### أ) Runway Gen-2

- **التكلفة:** $12/month (125 credits)
- **الجودة:** ممتازة
- **السرعة:** 30-60 ثانية لفيديو 4 ثوانٍ

#### ب) Synthesia

- **التكلفة:** $29/month (120 minutes/year)
- **مناسب للـ:** Talking head videos

#### ج) Lumen5

- **التكلفة:** $19/month
- **مناسب للـ:** Text-to-video (slides style)

### التوصية:

**تأجيل Video Generation** للمرحلة الثانية (بعد 3-6 أشهر)

---

## التكلفة المتوقعة (Monthly Budget)

### Scenario 1: Startup (100 campaigns/month)

- Text (Gemini Free): $0
- Images (Stability AI): $0.03 × 500 images = $15
- LangGraph: $0
- LangSmith: $0 (free tier)
- **Total: $15/month** ✅

### Scenario 2: Growth (1000 campaigns/month)

- Text (GPT-4o): ~$200
- Images (Stability AI): $0.03 × 5000 images = $150
- LangSmith: $39
- **Total: $389/month**

### Scenario 3: Scale (10K campaigns/month)

- Text (GPT-4o): ~$2000
- Images (Stability AI): $1500
- LangSmith: $99
- CDN/Storage: $100
- **Total: $3699/month**

---

## Architecture المقترح

```
User Request → Campaign Controller
    ↓
LangGraph Agent System:
    ↓
┌─────────────────────────────────────┐
│ Agent 1: Campaign Planner           │
│ - Uses: Gemini/GPT-4o              │
│ - Output: Campaign structure       │
└─────────────────────────────────────┘
    ↓
┌─────────────────────────────────────┐
│ Agent 2: Content Writer             │
│ - Uses: GPT-4o for quality         │
│ - Output: Post texts (AR/EN)       │
└─────────────────────────────────────┘
    ↓
┌─────────────────────────────────────┐
│ Agent 3: Image Designer             │
│ - Uses: Stability AI               │
│ - Output: Generated images         │
└─────────────────────────────────────┘
    ↓
┌─────────────────────────────────────┐
│ Agent 4: Content Merger             │
│ - Combines text + images           │
│ - Uses: Canva API / Custom         │
└─────────────────────────────────────┘
    ↓
┌─────────────────────────────────────┐
│ Agent 5: Quality Reviewer           │
│ - Uses: GPT-4o                     │
│ - Checks: Brand alignment          │
└─────────────────────────────────────┘
    ↓
Store in DB → Show to User
```

---

## Implementation Plan

### Phase 1: Core Setup (Week 1-2)

- تثبيت LangChain/LangGraph
- إعداد Gemini API (free)
- إعداد Stability AI API
- بناء Agent 1 (Campaign Planner)
- بناء Agent 2 (Content Writer)

### Phase 2: Image Integration (Week 3)

- بناء Agent 3 (Image Generator)
- تجربة Prompts للصور
- حفظ الصور في Storage

### Phase 3: Advanced Features (Week 4)

- بناء Agent 4 (Content Merger)
- بناء Agent 5 (Quality Reviewer)
- إضافة LangSmith monitoring

### Phase 4: Testing & Optimization (Week 5-6)

- اختبار مع حملات حقيقية
- تحسين Prompts
- قياس التكاليف
- A/B testing

---

## الخلاصة والتوصيات

### الـ AI Stack المقترح للبداية:

| المكون | الأداة المختارة | السبب | التكلفة |

|--------|-----------------|-------|---------|

| Text LLM | Gemini 2.0 Flash | مجاني + جودة جيدة | $0 |

| Text LLM (Premium) | GPT-4o | للجودة العالية عند الحاجة | Pay as you go |

| Image Generation | Stability AI | أفضل قيمة | $0.03/image |

| Agent Framework | LangGraph | مفتوح المصدر + قوي | $0 |

| Monitoring | LangSmith | تتبع وتحسين | Free tier |

| Video (Later) | TBD | بعد 6 أشهر | - |

### ملاحظات مهمة:

1. **نبدأ بـ Gemini Free** لتقليل التكاليف في البداية
2. **نستخدم GPT-4o فقط** للحملات المدفوعة أو Premium users
3. **Stability AI** أفضل من DALL-E من حيث السعر
4. **LangGraph** يعطينا مرونة كاملة لبناء Multi-agent system
5. **Video Generation** نؤجلها للمرحلة الثانية

### الخطوة التالية:

هل تريد البدء في تنفيذ هذا الـ Stack، أم لديك تعديلات على الخطة؟

### To-dos

- [ ] Create database migrations for brands, campaigns, campaign_posts, and campaign_analytics tables
- [ ] Create Eloquent models (Brand, Campaign, CampaignPost) with relationships and fillable attributes
- [ ] Create service classes (BrandService, CampaignService, AIContentGeneratorService)
- [ ] Create form request validation classes for brands and campaigns
- [ ] Create API resource classes (BrandResource, CampaignResource, CampaignPostResource)
- [ ] Create controllers (BrandController, CampaignController, CampaignPostController) with CRUD operations
- [ ] Add API routes for brands, campaigns, and campaign posts with admin prefix
- [ ] Add translations for brands and campaigns modules to ar.json and en.json
- [ ] Create API service files (brandService.js, campaignService.js, campaignPostService.js)
- [ ] Create Pinia stores (brand.js, campaign.js) for state management
- [ ] Create shared components (ColorPicker, ImageUploader, WizardSteps, CampaignCard, PostCard, CalendarView)
- [ ] Create brand management views (Brands.vue, BrandTable.vue, BrandForm.vue)
- [ ] Create campaign wizard with multi-step form (CampaignWizard.vue and step components)
- [ ] Create campaign management views (Campaigns.vue, CampaignTable.vue, CampaignDetails.vue, CampaignPosts.vue, CampaignCalendar.vue)
- [ ] Add routes for brands and campaigns to router/index.js, update Sidebar.vue navigation
- [ ] Test all CRUD operations, wizard flows, and responsive design across both languages