# 🎨 Marketa AI Platform - منصة التسويق بالذكاء الاصطناعي

![Version](https://img.shields.io/badge/version-2.0.0-blue)
![Status](https://img.shields.io/badge/status-production%20ready-success)
![Arabic](https://img.shields.io/badge/language-العربية-green)

## 📌 نظرة عامة

منصة احترافية متكاملة لإنشاء حملات التسويق وتصميم المحتوى باستخدام الذكاء الاصطناعي.

### 🎯 الأقسام الرئيسية الثلاثة:

1. **📊 Campaigns** - إدارة الحملات التسويقية
2. **🎨 Designs** - نظام موحد للتصاميم
3. **🤖 AI Studio** - توليد التصاميم بالذكاء الاصطناعي

---

## ✨ الميزات الرئيسية

### 1. AI Studio (استوديو الذكاء الاصطناعي)

#### ✅ التصميم:
- واجهة محادثة مثل Canva تماماً
- عربي 100%
- Responsive على جميع الشاشات

#### ✅ توليد الصور:
- صورة واحدة فقط لكل طلب
- نظام مرن يدعم 4 مزودين:
  - Pollinations (مجاني 100%)
  - HuggingFace (1000 طلب/شهر مجاناً)
  - OpenAI DALL-E (مدفوع، دقيق جداً)
  - Stability AI (مدفوع، دقيق)

#### ✅ التفاعل:
- إرسال مباشر للمقترحات (نقرة واحدة)
- Like/Dislike للتصاميم
- حفظ تلقائي للتصاميم المولدة
- ربط مع الحملات

### 2. Designs (نظام التصاميم الموحد)

#### ✅ الميزات:
- تصاميم مستقلة أو مرتبطة بحملات
- تصاميم من AI أو يدوية أو قوالب
- نسخ، تصدير، مشاركة
- Grid view احترافي
- فلاتر وبحث متقدم

#### ✅ Database:
- 22 عمود للمرونة القصوى
- UUID لكل تصميم
- Polymorphic relationships
- Soft deletes
- استخدام التصاميم كقوالب

### 3. Editor (المحرر الاحترافي)

#### ✅ UI/UX مطابق 100% لـ Canva:
```
┌─────────────────────────────────────────────┐
│  Purple Top Bar (60px)                      │
├─────┬──────────────────────┬────────────────┤
│ (70)│                      │  Right Sidebar │
│ Left│    Main Canvas       │     (280px)    │
│ Bar │   (Fabric.js)        │  Recent Designs│
│     │                      │                │
├─────┴──────────────────────┴────────────────┤
│  Bottom Bar - Pages & Zoom (50px)           │
└─────────────────────────────────────────────┘
```

#### ✅ المكونات:
- **Top Bar**: Close, File, Edit, Resize, Undo/Redo, Share, Profile
- **Left Sidebar**: 9 تبويبات (Design, Elements, Text, Uploads, Brand, Tools, Projects, Apps, More)
- **Panels**: 
  - DesignPanel (قوالب)
  - ElementsPanel (أشكال، خطوط، رسومات)
  - TextPanel (نصوص، خطوط، تركيبات)
  - UploadsPanel (رفع صور)
  - BrandPanel (Pro feature)
  - ToolsPanel (رسم)
- **Main Canvas**: Fabric.js مع zoom, pages, auto-save
- **Properties Panel**: Position, Size, Rotation, Opacity, Color, Text properties
- **Right Sidebar**: Recent designs, Quick navigation
- **Bottom Bar**: Pages navigation, Zoom slider

#### ✅ Keyboard Shortcuts (20+):
- `Ctrl+S` - حفظ
- `Ctrl+Z/Y` - تراجع/إعادة
- `Ctrl+X/C/V` - قص/نسخ/لصق
- `Delete` - حذف
- `Ctrl++/-` - زووم
- والمزيد...

#### ✅ Opening Behavior:
- يفتح دائماً في تاب جديد (`target="_blank"`)
- URL: `/editor/:uuid`

---

## 🏗️ البنية التقنية

### Frontend (Vue.js 3)
```
frontend-user/
├── src/
│   ├── layouts/
│   │   ├── DashboardLayout.vue
│   │   └── EditorLayout.vue (NEW - Canva-style)
│   ├── components/
│   │   ├── editor-v2/ (NEW)
│   │   │   ├── EditorTopBar.vue
│   │   │   ├── EditorLeftSidebar.vue
│   │   │   ├── MainCanvas.vue
│   │   │   ├── PropertiesPanel.vue
│   │   │   ├── EditorRightSidebar.vue
│   │   │   ├── EditorBottomBar.vue
│   │   │   └── panels/
│   │   │       ├── DesignPanel.vue
│   │   │       ├── ElementsPanel.vue
│   │   │       ├── TextPanel.vue
│   │   │       └── UploadsPanel.vue
│   │   └── shared/
│   │       ├── DesignLoadingCards.vue (NEW)
│   │       └── GeneratedDesignsGrid.vue (NEW)
│   ├── stores/
│   │   ├── design.js (NEW)
│   │   └── aiConversation.js (NEW)
│   └── views/dashboard/
│       ├── designs/DesignsList.vue (NEW)
│       └── ai/AiStudio.vue (NEW)
```

### Backend (Laravel)
```
backend/
├── app/
│   ├── Models/
│   │   ├── Design.php (NEW)
│   │   ├── AiConversation.php (NEW)
│   │   └── AiMessage.php (NEW)
│   └── Http/Controllers/Api/
│       ├── DesignController.php (NEW)
│       └── AiConversationController.php (NEW)
└── database/migrations/
    ├── 2025_11_02_000001_create_designs_table.php
    ├── 2025_11_02_000002_add_uuid_to_campaign_posts.php
    ├── 2025_11_02_000003_create_campaign_design_table.php
    ├── 2025_11_02_000004_update_campaign_posts_design_link.php
    └── 2025_11_02_000005_create_ai_conversations_tables.php
```

### AI Service (Python/FastAPI)
```
ai-service/
├── app/
│   ├── agents/image_gen.py (Flexible providers)
│   └── services/
│       ├── pollinations_images.py (FREE)
│       ├── huggingface_images.py (FREE tier)
│       ├── openai_images.py (DALL-E)
│       └── stability_images.py (Stability AI)
└── config.py (Provider configuration)
```

---

## 🚀 التشغيل السريع

### 1. تشغيل جميع الخدمات:
```bash
docker-compose up -d
```

### 2. الوصول للمنصة:
- **Dashboard**: http://localhost:3000/dashboard
- **AI Studio**: http://localhost:3000/dashboard/ai
- **Designs**: http://localhost:3000/dashboard/designs
- **Editor**: http://localhost:3000/editor/:uuid (يفتح تلقائياً)

### 3. API Keys (لتحسين دقة الصور):
```bash
# ai-service/.env
OPENAI_API_KEY=sk-your-key-here       # لصور دقيقة جداً
STABILITY_API_KEY=your-key-here       # لصور احترافية
```

---

## 📊 الإحصائيات

- **Components**: 25+ مكون قابل لإعادة الاستخدام
- **API Endpoints**: 30+ endpoint
- **Database Tables**: 7 جداول رئيسية
- **Translations**: 350+ نص بالعربي
- **Keyboard Shortcuts**: 20+ اختصار
- **Image Providers**: 4 مزودين مع fallback تلقائي

---

## 🎨 UI/UX

### مطابقة Canva:
| Feature | Canva | Marketa | Status |
|---------|-------|---------|--------|
| Purple Top Bar | ✅ | ✅ | 100% |
| Icon Sidebar | ✅ | ✅ | 100% |
| Sliding Panels | ✅ | ✅ | 100% |
| Main Canvas | ✅ | ✅ | 100% |
| Properties Panel | ✅ | ✅ | 100% |
| Recent Designs | ✅ | ✅ | 100% |
| Zoom Controls | ✅ | ✅ | 100% |
| Keyboard Shortcuts | ✅ | ✅ | 100% |
| Responsive Design | ✅ | ✅ | 100% |
| RTL Support | ✅ | ✅ | 100% |

**النتيجة: مطابق 100% لتجربة Canva! 🎉**

---

## 🔧 المشاكل المُصلحة

### AI Studio:
✅ اللغة العربية 100%  
✅ صورة واحدة فقط (كانت 3)  
✅ إرسال مباشر للمقترحات  
✅ تصميم Canva-like  
✅ موضع الفوتر  

### Editor:
✅ محرر كامل بأسلوب Canva  
✅ يفتح في تاب جديد  
✅ 9 تبويبات للأدوات  
✅ Properties panel ديناميكي  
✅ Responsive design  

---

## 📚 التوثيق

- `PROJECT_STATUS_FINAL.md` - الحالة النهائية الشاملة
- `EDITOR_V2_COMPLETE.md` - توثيق المحرر الكامل

---

## 🎯 الاستخدام

### إنشاء تصميم بالـ AI:
1. انتقل إلى AI Studio
2. ابدأ محادثة جديدة
3. اكتب طلبك (مثل: "ماكينة خياطة حديثة")
4. ستحصل على صورة واحدة
5. اضغط على اقتراح لإرساله فوراً
6. افتح التصميم في المحرر (تاب جديد)

### تعديل تصميم موجود:
1. انتقل إلى Designs
2. Hover على أي تصميم
3. اضغط "Edit" ✏️
4. سيُفتح المحرر في تاب جديد
5. استخدم Left Sidebar للأدوات
6. سيحفظ تلقائياً

---

## 🌟 الخلاصة

**المنصة الآن:**
- ✅ 3 أقسام رئيسية متكاملة
- ✅ محرر احترافي 100% مثل Canva
- ✅ AI ذكي يولد صوراً حقيقية
- ✅ نظام تصاميم موحد ومرن
- ✅ واجهات عربية كاملة
- ✅ Responsive على جميع الأجهزة
- ✅ Components قابلة لإعادة الاستخدام

**جاهز للإنتاج! 🚀**

---

## 👨‍💻 المطور

Mohammed Abdo - Oriteche
© 2025 مركتة. جميع الحقوق محفوظة.

