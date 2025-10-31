# تشغيل AI Service - تعليمات للمستخدم

## ⚠️ المطلوب منك الآن

افتح **PowerShell** window جديدة وشغل:

```powershell
cd D:\oriteche\Marketa-ai-platform\ai-service
python run_simple.py
```

يجب أن ترى:
```
============================================================
Starting Marketa AI Service - SIMPLE MODE
============================================================
Host: 0.0.0.0
Port: 8001

INFO:     Started server process
INFO:     Uvicorn running on http://0.0.0.0:8001
```

**اترك هذه النافذة مفتوحة** طوال فترة العمل!

---

## ✅ بعدها اختبر

في PowerShell آخر:
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

---

## 🎯 ثم جرب إنشاء حملة

افتح الواجهة وجرب إنشاء حملة - يجب أن يعمل الآن!

---

**ملاحظة**: لقد حدثت `backend/.env` و `config/services.php` - الآن Laravel يتصل بـ `http://localhost:8001/api` بشكل صحيح.

