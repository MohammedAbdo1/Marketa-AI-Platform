<!-- 1b1f6683-f8ad-467c-84e7-2fff4dcbb091 f54a9906-7bc9-4ac2-ad59-99dd31981e14 -->
# خطة إعادة بناء نظام التصميم الشامل

## المرحلة 1: إنشاء البنية التحتية للـ Design System

### 1.1 إنشاء هيكل المجلدات

```
frontend-user/src/
├── design-system/
│   ├── index.css                 (نقطة الدخول الرئيسية)
│   ├── tokens/
│   │   ├── colors.css           (ألوان Notion)
│   │   ├── typography.css       (Cairo font + أحجام)
│   │   ├── spacing.css          (نظام 4px)
│   │   ├── shadows.css          (ظلال خفيفة)
│   │   ├── radius.css           (border radius)
│   │   ├── animations.css       (transitions & keyframes)
│   │   └── z-index.css          (layers)
│   ├── components/
│   │   ├── buttons.css          (جميع أنواع الأزرار)
│   │   ├── forms.css            (inputs, selects, textarea)
│   │   ├── cards.css            (card variants)
│   │   ├── modals.css           (modal system)
│   │   ├── dropdowns.css        (dropdown menus)
│   │   ├── badges.css           (status badges)
│   │   ├── tooltips.css         (tooltips)
│   │   ├── sidebars.css         (sidebar layouts)
│   │   └── tables.css           (table styles)
│   ├── layouts/
│   │   ├── grid.css             (wrapper for Bootstrap)
│   │   ├── containers.css       (page containers)
│   │   └── rtl.css              (RTL overrides)
│   └── utilities/
│       ├── helpers.css          (flex, text, spacing)
│       └── responsive.css       (breakpoints)
```

### 1.2 إنشاء ملف الألوان (Notion Style)

**`design-system/tokens/colors.css`**

- Primary: #37352F (Notion's dark gray)
- Background: #FFFFFF, #F7F6F3, #ECEBE8
- Text: #37352F, #787774, #9B9A97
- Accents: #0F7B6C (teal), #CB912F (amber), #E03E3E (red)
- Sidebar: #F7F6F3 (light mode), #2F2F2F (dark mode)

### 1.3 إنشاء نظام Typography مع Cairo

**`design-system/tokens/typography.css`**

- استيراد Cairo من Google Fonts
- أحجام صغيرة: base = 14px (مثل Notion)
- Font weights: 400, 500, 600, 700
- Line heights: tight (1.25), normal (1.5)

### 1.4 إنشاء نظام Animations

**`design-system/tokens/animations.css`**

- Smooth transitions (150ms, 200ms, 300ms)
- Fade in/out animations
- Slide animations
- Scale animations
- Page transitions

### 1.5 إنشاء RTL System

**`design-system/layouts/rtl.css`**

- Sidebar positioning (right for Arabic, left for English)
- Text alignment
- Padding/margin flip
- Icon directions
- Dropdown positioning

## المرحلة 2: إنشاء مكونات UI القابلة لإعادة الاستخدام

### 2.1 نظام الأزرار

**`design-system/components/buttons.css`**

- `.btn-primary`, `.btn-secondary`, `.btn-ghost`
- أحجام: `.btn-xs`, `.btn-sm`, `.btn-md`
- `.btn-icon` للأزرار المربعة
- حالات: hover, active, disabled, loading

### 2.2 نظام Forms

**`design-system/components/forms.css`**

- Inputs صغيرة (height: 32px)
- Focus states مع outline خفيف
- Error states
- Label styles
- Checkbox & Radio (custom styled)

### 2.3 نظام Cards

**`design-system/components/cards.css`**

- `.card` مع hover effects
- `.card-header`, `.card-body`, `.card-footer`
- Shadow transitions

### 2.4 نظام Modals

**`design-system/components/modals.css`**

- Backdrop with blur
- Centered positioning
- Slide-up animation
- RTL support

### 2.5 نظام Dropdowns

**`design-system/components/dropdowns.css`**

- Position aware (RTL)
- Shadow & border
- Item hover states
- Dividers

## المرحلة 3: تحديث main.js وإزالة التعارضات

### 3.1 تحديث ترتيب الاستيراد

```javascript
// 1. Bootstrap Grid فقط
import 'bootstrap/dist/css/bootstrap-grid.min.css'

// 2. Boxicons
import 'boxicons/css/boxicons.min.css'

// 3. Toast
import 'vue-toastification/dist/index.css'

// 4. Design System (يتحكم بكل شيء)
import './design-system/index.css'
```

### 3.2 تحديث i18n لدعم RTL

- إضافة `dir` attribute للـ HTML
- تبديل CSS بناءً على اللغة

## المرحلة 4: إنشاء Documentation لـ Cursor

### 4.1 إنشاء `.cursorrules` في root

دليل شامل لكيفية استخدام Design System

### 4.2 إنشاء `DESIGN_SYSTEM.md`

**Sections:**

- Colors & Usage
- Typography Guidelines
- Component Library
- Spacing System
- Animation Guidelines
- RTL Best Practices
- Responsive Breakpoints
- Code Examples

### 4.3 إنشاء `COMPONENT_EXAMPLES.md`

أمثلة عملية لكل مكون مع الكود

## المرحلة 5: إعادة هيكلة الملفات الموجودة

### 5.1 تحديث Layouts (4 ملفات)

- `EditorLayout.vue`: إزالة inline styles، استخدام classes
- `DashboardLayout.vue`: RTL sidebar positioning
- `AuthLayout.vue`: استخدام design system
- `PublicLayout.vue`: استخدام design system

### 5.2 تحديث Editor Components (13 ملف)

- `EditorTopBar.vue`: compact buttons، design system colors
- `EditorLeftSidebar.vue`: أحجام صغيرة، RTL support
- `EditorRightSidebar.vue`: RTL positioning
- `MainCanvas.vue`: design system colors
- `ExportModal.vue`: modal system
- جميع Panels (6 ملفات): unified styling

### 5.3 تحديث Dashboard Components (2 ملف)

- `DashboardHeader.vue`: compact header، RTL
- `DashboardSidebar.vue`: RTL positioning، Notion colors

### 5.4 تحديث Views - Dashboard (10 ملفات)

- `Home.vue`: cards & stats
- `designs/DesignsList.vue`: grid layout
- `designs/DesignCard.vue`: card component
- `campaigns/*`: جميع صفحات الـ Campaigns
- `brands/*`: صفحات الـ Brands
- `Settings.vue`, `Profile.vue`, `Usage.vue`

### 5.5 تحديث Views - Auth (7 ملفات)

- `Login.vue`, `Register.vue`: forms
- `ForgotPassword.vue`, `ResetPassword.vue`: forms
- باقي صفحات Auth

### 5.6 تحديث Views - Public (4 ملفات)

- `Home.vue`: landing page
- `Pricing.vue`, `About.vue`, `Faq.vue`

### 5.7 تحديث Public Components (5 ملفات)

- `PublicHeader.vue`: compact navbar
- `PublicFooter.vue`
- جميع Landing sections

### 5.8 تحديث Shared Components (3 ملفات)

- `GeneratedDesignsGrid.vue`
- `DesignLoadingCards.vue`
- `DesignCard.vue`

### 5.9 تحديث Campaign Components (2 ملف)

- `CampaignCard.vue`
- `CampaignKanban.vue`

## المرحلة 6: Testing & QA

### 6.1 اختبار RTL

- تبديل اللغة في جميع الصفحات
- Sidebar positioning
- Text alignment
- Dropdowns & Modals

### 6.2 اختبار Responsive

- Mobile (< 768px)
- Tablet (768px - 1024px)
- Desktop (> 1024px)

### 6.3 اختبار Animations

- Page transitions
- Button hovers
- Modal open/close
- Dropdown animations

### 6.4 اختبار Consistency

- الألوان موحدة في كل مكان
- الأحجام consistent
- Spacing متسق

## الملفات الرئيسية

- `frontend-user/src/design-system/index.css` (الملف الرئيسي)
- `frontend-user/src/main.js` (تحديث imports)
- `frontend-user/src/App.vue` (RTL support)
- `.cursorrules` (Documentation)
- `DESIGN_SYSTEM.md` (الدليل الشامل)

## النتيجة النهائية

- نظام تصميم موحد 100%
- صفر تكرار في الأنماط
- دعم RTL كامل
- Animations سلسة
- أحجام صغيرة Notion-style
- خط Cairo للعربية
- 63 ملف Vue منظم ومرتب
- Documentation كامل لـ Cursor

### To-dos

- [ ] إنشاء هيكل مجلد design-system بجميع المجلدات الفرعية
- [ ] إنشاء نظام الألوان بأسلوب Notion في tokens/colors.css
- [ ] إنشاء نظام Typography مع خط Cairo في tokens/typography.css
- [ ] إنشاء نظام المسافات في tokens/spacing.css
- [ ] إنشاء نظام Animations في tokens/animations.css
- [ ] إنشاء نظام الظلال في tokens/shadows.css
- [ ] إنشاء نظام RTL كامل في layouts/rtl.css
- [ ] إنشاء نظام الأزرار الموحد في components/buttons.css
- [ ] إنشاء نظام Forms في components/forms.css
- [ ] إنشاء نظام Cards في components/cards.css
- [ ] إنشاء نظام Modals في components/modals.css
- [ ] إنشاء نظام Dropdowns في components/dropdowns.css
- [ ] إنشاء Utility classes في utilities/helpers.css
- [ ] إنشاء ملف design-system/index.css الرئيسي
- [ ] تحديث main.js وترتيب الاستيرادات
- [ ] تحديث App.vue لدعم RTL
- [ ] إنشاء .cursorrules مع documentation كامل
- [ ] إنشاء DESIGN_SYSTEM.md مع الدليل الشامل
- [ ] تحديث جميع Layout files (4 ملفات) لاستخدام Design System
- [ ] تحديث جميع Editor components (13 ملف)
- [ ] تحديث Dashboard components (2 ملف)
- [ ] تحديث جميع Dashboard views (10 ملفات)
- [ ] تحديث جميع Auth views (7 ملفات)
- [ ] تحديث جميع Public views (4 ملفات)
- [ ] تحديث Shared components (3 ملفات)
- [ ] اختبار RTL في جميع الصفحات والتأكد من صحة الـ positioning
- [ ] اختبار Responsive في جميع breakpoints
- [ ] اختبار جميع Animations والتأكد من السلاسة