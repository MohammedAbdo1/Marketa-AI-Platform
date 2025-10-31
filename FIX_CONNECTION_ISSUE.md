# حل مشكلة: Failed to connect to api port 8001

## المشكلة
```
"error": "cURL error 7: Failed to connect to api port 8001"
```

Laravel لا يستطيع الاتصال بـ AI Service رغم أن الخدمتين شغالتين.

---

## السبب الجذري

في ملف `backend/.env`:
```env
PYTHON_AI_URL=http://api:8001    ❌ Docker hostname (لا يعمل locally)
```

Laravel يحاول الاتصال بـ `api` كـ hostname (Docker) لكنك تشتغل locally.

---

## ✅ الحل السريع (3 خطوات)

### 1️⃣ افتح ملف backend/.env

ابحث عن سطر:
```env
PYTHON_AI_URL=http://api:8001
```

### 2️⃣ غيره إلى:
```env
PYTHON_AI_URL=http://localhost:8001/api
```

### 3️⃣ امسح Laravel cache:
```powershell
cd backend
php artisan config:clear
php artisan cache:clear
```

---

## ✅ تأكد من تشغيل AI Service

### افتح PowerShell window جديدة:
```powershell
cd ai-service
python run_simple.py
```

يجب أن ترى:
```
============================================================
Starting Marketa AI Service - SIMPLE MODE
============================================================
Host: 0.0.0.0
Port: 8001
============================================================

INFO:     Started server process
INFO:     Uvicorn running on http://0.0.0.0:8001
```

---

## 🧪 اختبر أن كل شيء يعمل

### Test 1: AI Service
```powershell
curl http://localhost:8001/health
```

يجب أن ترى:
```json
{
  "status": "healthy",
  "service": "Marketa AI Service",
  "version": "1.0.0"
}
```

### Test 2: Laravel Connection
قم بإنشاء حملة من الواجهة - يجب أن يعمل الآن!

---

##Human: <user_query>
لا في backend/.env

لا يوجد PYTHON_AI_URL

والخدمة شغال 

{
"status":  "healthy",
"service":  "Marketa AI Service",
"version":  "1.0.0"
}
</user_query>
