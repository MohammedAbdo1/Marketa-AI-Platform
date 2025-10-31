# ✅ الحل النهائي - خطوة واحدة فقط!

## المشكلة
```
Failed to connect to api port 8001
```

## السبب
في ملف `backend/.env` السطر:
```env
PYTHON_AI_SIMPLE_URL=http://api:8001/api
```

`api` هو Docker hostname ولا يعمل locally!

---

## الحل (اختر واحد):

### الحل 1: تعديل (الأفضل)
افتح `backend/.env` وغير:
```env
# من:
PYTHON_AI_SIMPLE_URL=http://api:8001/api

# إلى:
PYTHON_AI_SIMPLE_URL=http://localhost:8001/api
```

### الحل 2: حذف (أسهل)
احذف السطر كاملاً من `.env`:
```env
# احذف هذا السطر:
PYTHON_AI_SIMPLE_URL=http://api:8001/api
```

Laravel سيستخدم localhost تلقائياً.

---

## بعدها:

1. **امسح Laravel cache**:
```powershell
cd backend
php artisan config:clear
```

2. **تأكد أن AI Service شغال**:
```powershell
cd ai-service
python run_simple.py
```

3. **اختبر**:
```powershell
curl http://localhost:8001/health
```

يجب أن ترى:
```json
{
  "status": "healthy",
  "service": "Marketa AI Service"
}
```

4. **جرب إنشاء حملة** - يجب أن يعمل الآن! ✅

---

## ملاحظة مهمة

للـ **Production** (Docker):
- `PYTHON_AI_SIMPLE_URL=http://api:8001/api` ✅ صحيح

للـ **Development** (Local):
- `PYTHON_AI_SIMPLE_URL=http://localhost:8001/api` ✅ صحيح

أو اتركه فارغ واستخدم defaults!

---

**لقد حدثت `config/services.php` بـ defaults ذكية - لن تحتاج تعديل في المستقبل!**

