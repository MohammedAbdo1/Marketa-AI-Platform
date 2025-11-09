# 🎨 دليل المحرر الاحترافي - Marketa AI Platform

## 📖 نظرة عامة

محرر صور احترافي مشابه لـ Canva مع حفظ تلقائي وتكامل كامل مع نظام Marketa AI.

---

## 🚀 البدء السريع

### 1. التثبيت والإعداد

```bash
# تثبيت Fabric.js (تم بالفعل في package.json)
npm install

# تشغيل Frontend
npm run dev
```

### 2. الوصول للمحرر

```
1. افتح http://localhost:3000
2. سجّل الدخول
3. اذهب للحملات → افتح حملة → اضغط "تحرير" على أي منشور
```

---

## 🎯 الميزات الرئيسية

### ✅ **1. Toolbar الشامل**

#### أدوات النص:
- إضافة نص جديد
- تعديل المحتوى
- تغيير حجم الخط (12px - 120px)
- تغيير اللون
- الشفافية

#### أدوات الأشكال:
- مربعات
- دوائر
- لون التعبئة
- سماكة الحدود (0-20px)
- الشفافية

#### أدوات الصور:
- رفع من الجهاز
- تحجيم تلقائي
- drag & drop على Canvas

---

### ✅ **2. نظام الطبقات**

```
الطبقات (Layers)
├─ صورة 1 👁️ 🗑️
├─ نص 1   👁️ 🗑️
├─ مربع 1 👁️ 🗑️
└─ دائرة 1 👁️ 🗑️
```

**الميزات:**
- ✅ Drag & Drop لإعادة الترتيب
- ✅ إخفاء/إظهار (👁️)
- ✅ حذف (🗑️)
- ✅ تحديد سريع

---

### ✅ **3. الحفظ التلقائي**

```javascript
// نظام ذكي:
تغيير → انتظر 1.5 ثانية → حفظ تلقائي

// مؤشرات:
"جاري الحفظ..." → "✅ آخر حفظ: الآن"
```

**السلوك:**
- حفظ بعد كل تعديل (1.5s delay)
- إلغاء الحفظ إذا حدث تعديل جديد
- حفظ فوري: Ctrl/Cmd + S

---

### ✅ **4. Undo/Redo**

| الإجراء | الاختصار |
|---------|----------|
| Undo    | Ctrl/Cmd + Z |
| Redo    | Ctrl/Cmd + Shift + Z |
| Delete  | Delete / Backspace |
| Save    | Ctrl/Cmd + S |

**التخزين:**
- آخر 15 حالة
- Memory efficient
- تحديث تلقائي للأزرار

---

## 🛠️ الاستخدام التفصيلي

### إضافة نص:

```javascript
1. اضغط "نص" في Toolbar
2. اكتب المحتوى المطلوب
3. اضبط حجم الخط (Slider)
4. اختر اللون (Color Picker)
5. اضبط الشفافية إذا لزم
```

### إضافة شكل:

```javascript
1. اضغط "مربع" أو "دائرة"
2. اسحب الشكل للموقع المطلوب
3. غيّر الحجم من نقاط التحكم
4. غيّر اللون من Properties Panel
```

### رفع صورة:

```javascript
1. اضغط "صورة"
2. اختر الصورة من جهازك
3. سيتم تحجيمها تلقائياً
4. اسحبها وعدّل حجمها
```

### إعادة ترتيب الطبقات:

```javascript
1. افتح Layers Panel (يسار)
2. اسحب الطبقة للأعلى/الأسفل
3. سيتم إعادة الترتيب على Canvas مباشرة
```

---

## 🔧 البنية التقنية

### Pinia Store (`postEditor.js`):

```javascript
State:
- currentPost     // البيانات الأساسية
- layers[]        // جميع الطبقات
- history[]       // سجل التعديلات
- historyIndex    // الموقع الحالي
- isDirty         // هل توجد تغييرات؟

Actions:
- loadPost()      // تحميل من Backend
- savePost()      // حفظ للـ Backend
- addLayer()      // إضافة طبقة
- updateLayer()   // تحديث طبقة
- deleteLayer()   // حذف طبقة
- undo()          // التراجع
- redo()          // الإعادة
```

### Component (`PostEditor.vue`):

```javascript
Features:
- Fabric.js Canvas initialization
- Event handlers (modified, added, removed)
- Selection management
- Properties sync
- Autosave trigger
- Keyboard shortcuts
```

---

## 📡 API Endpoints

```php
// تم إنشاؤها مسبقاً في Backend

GET    /creative-assets/{uuid}            // Fetch composition data
PUT    /creative-assets/{uuid}            // Persist composition updates
```

---

## 🎨 التخصيص

### إضافة شكل جديد:

```vue
<!-- في PostEditor.vue -->
<button @click="addShape('triangle')" class="btn btn-sm btn-outline-primary">
  <i class="fas fa-triangle"></i>
</button>

<script>
function addShape(type) {
  if (type === 'triangle') {
    const triangle = new fabric.Triangle({
      left: 150,
      top: 150,
      width: 100,
      height: 100,
      fill: '#2ecc71'
    });
    canvas.add(triangle);
  }
}
</script>
```

### إضافة فونت عربي:

```javascript
fabric.Text.prototype.fontFamily = 'Cairo'; // أو أي خط
```

---

## 🐛 استكشاف الأخطاء

### المحرر لا يُحمّل؟
```bash
# تحقق من Console:
F12 → Console → ابحث عن أخطاء

# تحقق من API:
Network → ابحث عن /creative-assets/{uuid}
```

### الحفظ التلقائي لا يعمل؟
```javascript
// تحقق من Console logs:
"[Editor] Post saved successfully" ✅
أو
"[Editor] Save failed: ..." ❌
```

### الصور لا تُرفع؟
```javascript
// تحقق من:
1. حجم الصورة (< 5MB)
2. الصيغة (PNG, JPG, JPEG, WEBP)
3. Browser console للأخطاء
```

---

## 📊 الأداء

### تحسينات مطبقة:
- ✅ Lazy loading للصور
- ✅ Debounce للـ Autosave (1.5s)
- ✅ History limit (15 states)
- ✅ Efficient re-renders

### معايير الأداء:
- Canvas: 1080x1080px
- Max layers: ~50 طبقة (recommended)
- Max image size: 5MB
- Autosave delay: 1.5 seconds

---

## 🔐 الأمان

- ✅ تحقق من صيغ الصور المرفوعة
- ✅ حد أقصى لحجم الملف
- ✅ تنظيف الـ Input بعد الرفع
- ✅ Sanitization للـ JSON المحفوظ

---

## 📞 الدعم

للأسئلة والمشاكل:
1. راجع هذا الملف
2. راجع `EDITOR_FEATURES.md`
3. راجع Console logs
4. اتصل بالدعم التقني

---

**آخر تحديث**: 2025-11-01  
**الإصدار**: 1.0.0  
**الحالة**: ✅ Production Ready

