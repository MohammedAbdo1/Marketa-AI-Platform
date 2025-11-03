# دليل ألوان Notion الرسمية

## 🎨 نظام الألوان المطابق 100% لـ Notion

### الألوان الأساسية

#### النصوص (Text Colors)
```css
--color-text-primary: #37352F    /* النص الأساسي (Notion Default) */
--color-text-secondary: #787774  /* النص الثانوي */
--color-text-tertiary: #9B9A97   /* النص الخفيف */
```

#### الخلفيات (Background Colors)
```css
--color-bg-primary: #FFFFFF      /* الخلفية الرئيسية (المحتوى) */
--color-bg-secondary: #F7F6F3    /* الخلفية الثانوية (Sidebar) */
--color-bg-tertiary: #ECEBE8     /* الخلفية الثالثة */
```

---

## 🔵 ألوان الـ Brand (الأزرار والعناصر النشطة)

### اللون الأساسي (Primary Brand)
```css
--color-brand-primary: #0B6E99   /* الأزرق - لون Notion الأساسي */
```

**الاستخدام:**
- ✅ الأزرار الرئيسية (Primary Buttons)
- ✅ الروابط (Links)
- ✅ العناصر التفاعلية المهمة

**مثال:**
```vue
<button class="btn btn-primary">إنشاء تصميم جديد</button>
```

### اللون الأسود (Alternative Primary)
```css
--color-black: #000000           /* الأسود - للعناصر المهمة جداً */
```

**الاستخدام:**
- ✅ أزرار الإجراءات الحاسمة
- ✅ عناوين مهمة

---

## 🎨 ألوان التمييز (Highlight Colors)

**⚠️ هام:** هذه الألوان **للنصوص والخلفيات الملونة فقط**، وليست للأزرار!

### الأزرق (Blue)
```css
--color-blue-text: #0B6E99
--color-blue-bg: #DDEBF1
```

### الأخضر/Teal (Green)
```css
--color-green-text: #0F7B6C
--color-green-bg: #DDEDEA
```

### البرتقالي (Orange)
```css
--color-orange-text: #D9730D
--color-orange-bg: #FAEBDD
```

### الأحمر (Red)
```css
--color-red-text: #E03E3E
--color-red-bg: #FBE4E4
```

### البنفسجي (Purple)
```css
--color-purple-text: #6940A5
--color-purple-bg: #EAE4F2
```

### الرمادي (Gray)
```css
--color-gray-text: #787774
--color-gray-bg: #EBECED
```

---

## 📋 أمثلة الاستخدام

### ✅ الاستخدام الصحيح

#### الأزرار الرئيسية
```vue
<!-- الزر الأساسي - أزرق -->
<button class="btn btn-primary">حفظ</button>

<!-- زر ثانوي - رمادي -->
<button class="btn btn-secondary">إلغاء</button>

<!-- زر خطر - أحمر -->
<button class="btn btn-danger">حذف</button>
```

#### النصوص الملونة
```vue
<!-- نص أخضر مع خلفية -->
<span style="color: var(--color-green-text); background: var(--color-green-bg);">
  مكتمل
</span>

<!-- Badge أخضر -->
<span class="badge badge-success">نشط</span>
```

#### Sidebar النشط
```css
.nav-item.active {
  background: #E3E2E0;        /* خلفية رمادية أغمق */
  color: #37352F;             /* نص أسود (ليس أخضر!) */
  font-weight: 600;
}
```

### ❌ الاستخدام الخاطئ

```vue
<!-- ❌ خطأ: استخدام الأخضر للأزرار الرئيسية -->
<button style="background: #0F7B6C;">حفظ</button>

<!-- ✅ صحيح: استخدام الأزرق -->
<button class="btn btn-primary">حفظ</button>
```

```css
/* ❌ خطأ: Sidebar نشط بلون أخضر */
.nav-item.active {
  background: #0F7B6C;
  color: white;
}

/* ✅ صحيح: Sidebar نشط بخلفية رمادية */
.nav-item.active {
  background: #E3E2E0;
  color: #37352F;
}
```

---

## 🎯 قواعد أساسية

### 1. الأزرار الرئيسية = أزرق (#0B6E99)
في Notion، الأزرار الرئيسية **دائماً زرقاء**، وليست خضراء!

### 2. اللون الأخضر = للنصوص فقط
`#0F7B6C` يُستخدم فقط:
- ✅ للنصوص الملونة
- ✅ للخلفيات الملونة
- ✅ للـ Badges والـ Status
- ❌ **ليس** للأزرار الرئيسية
- ❌ **ليس** للعناصر النشطة في الـ Sidebar

### 3. Sidebar النشط = رمادي غامق
العناصر النشطة في Sidebar تكون:
- خلفية: `#E3E2E0` (رمادي أغمق قليلاً)
- نص: `#37352F` (أسود Notion)
- **ليس** أخضر أو أزرق!

### 4. الخلفية الرئيسية = أبيض نقي
- المحتوى الرئيسي: `#FFFFFF` (أبيض)
- الـ Sidebar والمناطق الثانوية: `#F7F6F3` (رمادي فاتح)

---

## 📊 مقارنة: قبل وبعد

### قبل التحديث ❌
```css
--color-brand-primary: #0F7B6C;  /* أخضر - خطأ! */

.nav-item.active {
  background: #0F7B6C;           /* أخضر - خطأ! */
  color: white;
}
```

### بعد التحديث ✅
```css
--color-brand-primary: #0B6E99;  /* أزرق - صحيح! */

.nav-item.active {
  background: #E3E2E0;           /* رمادي - صحيح! */
  color: #37352F;                /* أسود - صحيح! */
}
```

---

## 🔍 المرجع الكامل

| الاسم | لون النص | لون الخلفية | الاستخدام |
|-------|----------|-------------|-----------|
| رمادي | `#787774` | `#EBECED` | نصوص ثانوية |
| أزرق | `#0B6E99` | `#DDEBF1` | **أزرار + نصوص** |
| أخضر | `#0F7B6C` | `#DDEDEA` | نصوص ملونة فقط |
| برتقالي | `#D9730D` | `#FAEBDD` | تحذيرات |
| أحمر | `#E03E3E` | `#FBE4E4` | أخطاء |
| بنفسجي | `#6940A5` | `#EAE4F2` | مميزات Pro |

---

## ✅ الخلاصة

الآن نظام الألوان **مطابق 100% لـ Notion**:
- ✅ الأزرار الرئيسية **زرقاء** `#0B6E99`
- ✅ Sidebar النشط **رمادي غامق** `#E3E2E0`
- ✅ اللون الأخضر `#0F7B6C` **للنصوص فقط**
- ✅ الخلفية الرئيسية **أبيض نقي** `#FFFFFF`
- ✅ جميع الألوان من جدول Notion الرسمي

**المصدر:** جدول الألوان الرسمي من Notion

