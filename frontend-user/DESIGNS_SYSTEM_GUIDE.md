# 🎨 Advanced Designs Management System - دليل النظام الجديد

## 📋 نظرة عامة (Overview)

تم بناء نظام متقدم لإدارة التصاميم بأسلوب **Canva** احترافي، يشمل:

✅ **Infinite Scrolling** - تحميل تدريجي للتصاميم  
✅ **Favorites with Sections** - نظام مفضلة متقدم مع أقسام  
✅ **Drag & Drop** - سحب وإفلات بين الأقسام  
✅ **Inline Editing** - تعديل الأسماء مباشرة مع auto-save  
✅ **Trash System** - سلة مهملات مع restore وحذف نهائي  
✅ **Context Menus** - قوائم شاملة لكل تصميم  

---

## 🗄️ Database Structure

### جداول جديدة:

#### 1. `designs` (تحديث)
```sql
trashed_at TIMESTAMP NULL  -- للـ Trash (30 يوم ثم حذف نهائي)
```

#### 2. `favorite_sections` (جديد)
```sql
id, uuid, user_id, organization_id, name, emoji, order, created_at, updated_at
```

#### 3. `user_favorites` (جديد)
```sql
id, user_id, creative_asset_id, section_id, order, created_at
```

---

## 🚀 API Endpoints

### Designs APIs:
- `GET /api/designs` - List designs (with pagination)
- `GET /api/designs/trash` - Trashed designs
- `PATCH /api/designs/{uuid}/title` - Update title only (auto-save)
- `POST /api/designs/{uuid}/trash` - Move to trash
- `POST /api/designs/{uuid}/restore` - Restore from trash
- `DELETE /api/designs/{uuid}/force` - Permanent delete

### Favorites APIs:
- `GET /api/favorites` - Get favorites with sections
- `POST /api/favorites` - Add to favorites
- `DELETE /api/favorites/{creative_asset_id}` - Remove from favorites
- `PATCH /api/favorites/{creative_asset_id}` - Move to section

### Sections APIs:
- `GET /api/favorite-sections` - List sections
- `POST /api/favorite-sections` - Create section
- `PATCH /api/favorite-sections/{uuid}` - Update section
- `DELETE /api/favorite-sections/{uuid}` - Delete section
- `POST /api/favorite-sections/reorder` - Reorder sections

---

## 📁 Frontend Structure

```
frontend-user/src/
├── components/designs/
│   ├── InlineEditableName.vue      # مكون إعادة الاستخدام للتعديل المباشر
│   ├── DesignContextMenu.vue       # قائمة السياق (Context Menu)
│   └── (shared components)
├── views/dashboard/designs/
│   ├── DesignsList.vue             # قائمة التصاميم مع Infinite Scroll
│   ├── DesignCard.vue              # Canva-style card مع hover actions
│   ├── FavoritesPage.vue           # صفحة المفضلة مع Sections
│   └── TrashPage.vue               # سلة المهملات
├── stores/
│   ├── design.js                   # محدّث مع trash & favorites
│   └── favorites.js                # Store جديد للمفضلة
└── design-system/components/
    └── design-cards.css            # Canva-style components

```

---

## 🎯 Features الرئيسية

### 1. **Infinite Scrolling**

```javascript
// Intersection Observer API
const observer = new IntersectionObserver((entries) => {
  if (entries[0].isIntersecting && hasMore && !loading) {
    loadMore() // جلب الصفحة التالية
  }
}, { threshold: 0.5 })
```

**كيف يعمل:**
- يحمل 20 تصميم في البداية
- عندما يسكرول للأسفل، يحمل 20 آخرين
- يستمر حتى ينتهي من جميع التصاميم

---

### 2. **Inline Name Editing**

```vue
<InlineEditableName
  v-model="design.title"
  @save="updateTitle"
/>
```

**Features:**
- ✅ Hover → يظهر أيقونة قلم
- ✅ Click → Input field selected
- ✅ Auto-save بعد 500ms من التوقف عن الكتابة
- ✅ Escape للإلغاء
- ✅ يعمل في الكارد والـ Context Menu

---

### 3. **Context Menu (قائمة السياق)**

**الخيارات:**
1. افتح في تبويب جديد
2. التفاصيل
3. إعادة التسمية
4. ---
5. عمل نسخة
6. تنزيل
7. مشاركة
8. نسخ الرابط
9. ---
10. إضافة لحملة / إلغاء التمييز بنجمة
11. ---
12. النقل إلى سلة المهملات

---

### 4. **Favorites System (نظام المفضلة)**

#### **إنشاء قسم:**
```javascript
// 1. المستخدم ينقر زر "+"
createSection()

// 2. يتم إنشاء قسم جديد باسم "قسم بدون عنوان"
// 3. النص يصبح selected تلقائياً للتعديل
```

#### **Drag & Drop:**
```vue
<Draggable 
  v-model="section.designs"
  group="designs"
  @end="reorderDesigns"
>
  <DesignCard ... />
</Draggable>
```

**يمكن:**
- ✅ سحب تصميم من قسم لآخر
- ✅ سحب تصميم من "بدون قسم" لقسم
- ✅ إعادة ترتيب التصاميم داخل القسم
- ✅ إعادة ترتيب الأقسام نفسها

---

### 5. **Trash System (سلة المهملات)**

#### **Flow:**
```
Design → Move to Trash → (30 days) → Auto-Delete
         ↓
         Restore (استعادة)
         OR
         Delete Forever (حذف نهائي)
```

#### **Auto-Cleanup:**
```bash
# Cron Job يعمل يومياً
php artisan schedule:run
# OR
php artisan designs:cleanup-trash
```

---

## 🎨 Styling (Canva-style)

### Design Card:
```css
/* Aspect ratio 1:1 for thumbnails */
.card-thumbnail {
  aspect-ratio: 1;
  background: var(--color-bg-secondary);
}

/* Hover effects */
.design-card:hover {
  transform: translateY(-2px);
  box-shadow: var(--shadow-card-hover);
}

/* Actions visible on hover */
.card-actions {
  opacity: 0;
}
.design-card:hover .card-actions {
  opacity: 1;
}
```

### Responsive Grid:
```css
.designs-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
  gap: var(--space-4);
}

/* Tablet: 200px */
/* Mobile: 160px */
```

---

## 🔧 Usage Examples

### استخدام في Component:

```vue
<script setup>
import { useDesignStore } from '@/stores/design'
import { useFavoritesStore } from '@/stores/favorites'

const designStore = useDesignStore()
const favoritesStore = useFavoritesStore()

// Load designs with infinite scroll
await designStore.fetchDesigns(page)

// Toggle favorite
await designStore.toggleFavorite(designId, isFavorited)

// Create section
await favoritesStore.createSection('اسم القسم', '📁')

// Move to trash
await designStore.moveToTrash(uuid)

// Restore from trash
await designStore.restoreDesign(uuid)
</script>
```

---

## 🌐 Routes

```javascript
/dashboard/designs              → DesignsList (Infinite Scroll)
/dashboard/designs/favorites    → FavoritesPage (Sections + Drag-Drop)
/dashboard/trash                → TrashPage (Restore + Delete Forever)
```

---

## ✅ Testing Checklist

### Designs List:
- [ ] Infinite scroll loads more designs
- [ ] Star icon toggles favorite
- [ ] Three-dots menu opens correctly
- [ ] All menu actions work
- [ ] Inline editing saves automatically
- [ ] Move to trash removes from list

### Favorites Page:
- [ ] Create section button works
- [ ] Section name editable inline
- [ ] Drag design between sections works
- [ ] Reorder sections works
- [ ] Remove from favorites works
- [ ] Unsectioned designs area works

### Trash Page:
- [ ] Shows trashed designs
- [ ] Restore returns to designs list
- [ ] Delete forever shows warning
- [ ] Permanent delete works
- [ ] Shows days since trashed

### Backend:
- [ ] Migrations run successfully
- [ ] API endpoints return correct data
- [ ] Pagination works
- [ ] Soft deletes work correctly
- [ ] Cron job scheduled

---

## 🐛 Troubleshooting

### Database not connected:
```bash
# تأكد من تشغيل Docker containers
docker-compose up -d

# Run migrations
php artisan migrate
```

### Vuedraggable not working:
```bash
# Reinstall package
cd frontend-user
npm install vuedraggable@next
```

### Infinite scroll not triggering:
```javascript
// Check if observer is setup
console.log(observer.value) // should not be null

// Check threshold
console.log(entries[0].isIntersecting) // should be true at bottom
```

---

## 🎓 ما تعلمناه

1. **Infinite Scrolling** مع Intersection Observer API
2. **SaaS-grade Favorites** مع جداول منفصلة
3. **Soft Deletes** مع auto-cleanup
4. **Drag & Drop** مع vuedraggable
5. **Inline Editing** مع debounced auto-save
6. **Context Menus** المتقدمة
7. **Responsive Grids** مع CSS Grid

---

## 🚀 Next Steps (اختياري)

1. **Folders System** - نظام مجلدات للتنظيم
2. **Sharing** - مشاركة التصاميم مع الفرق
3. **Versions** - تتبع إصدارات التصميم
4. **Comments** - تعليقات على التصاميم
5. **Templates Gallery** - معرض قوالب عامة

---

**تم بناؤه بحب لمنصة Marketa AI 💚**

