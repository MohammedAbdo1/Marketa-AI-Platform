# دليل التشغيل السريع - Marketa AI Platform

## 🚀 تشغيل النظام في وضع التطوير

### الخطوة 1: تشغيل AI Service (Python)

```powershell
# الطريقة السهلة
.\start_ai_service.ps1

# أو يدوياً
cd ai-service
python run.py
```

**الخدمة ستعمل على**: http://localhost:8001

---

### الخطوة 2: تشغيل Laravel Backend

```powershell
cd backend
php artisan serve
```

**الخدمة ستعمل على**: http://localhost:8000

---

### الخطوة 3: تشغيل Frontend (Vue 3)

```powershell
cd frontend-user
npm run dev
```

**الخدمة ستعمل على**: http://localhost:5173

---

## ✅ التحقق من التشغيل

### فحص AI Service
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

### فحص Laravel Backend
```powershell
curl http://localhost:8000/api/health
```

---

## 🔧 حل المشاكل الشائعة

### مشكلة: "Failed to connect to api port 8001"

**السبب**: AI Service غير شغال أو Laravel يحاول الاتصال بـ Docker hostname

**الحل**:
1. تأكد أن AI Service شغال: `.\start_ai_service.ps1`
2. امسح Laravel cache: `php artisan config:clear`
3. تأكد أن PYTHON_AI_URL غير محدد في .env (يستخدم localhost تلقائياً)

---

### مشكلة: "Port 8001 already in use"

**الحل**:
```powershell
# إيقاف جميع Python processes
Get-Process -Name python | Stop-Process -Force

# ثم أعد تشغيل AI Service
.\start_ai_service.ps1
```

---

### مشكلة: Gemini API Rate Limit (429)

**السبب**: تجاوز الحد اليومي المجاني (1500 request/day)

**الحل**:
- انتظر 24 ساعة
- أو استخدم API key آخر
- أو انتقل لـ paid tier

---

### مشكلة: Stability AI Credits

**السبب**: Credits منتهية

**الحل**:
- اشحن credits من https://platform.stability.ai/account/credits
- أو استخدم mock images للتطوير

---

## 📝 متطلبات التشغيل

### Python (AI Service)
- Python 3.11+
- جميع المكتبات في `ai-service/requirements.txt`
- API Keys في `ai-service/.env`:
  - GOOGLE_API_KEY
  - STABILITY_API_KEY (optional)

### Laravel (Backend)
- PHP 8.2+
- Composer
- Database (PostgreSQL/MySQL)

### Vue 3 (Frontend)
- Node.js 18+
- npm

---

## 🎯 الخدمات المطلوبة

| الخدمة | Port | Status Check |
|--------|------|--------------|
| AI Service | 8001 | http://localhost:8001/health |
| Laravel Backend | 8000 | http://localhost:8000/api/health |
| Vue Frontend | 5173 | http://localhost:5173 |

---

## 💡 نصائح التطوير

1. **اترك AI Service شغال** طوال فترة التطوير
2. **استخدم نافذة منفصلة** لكل خدمة
3. **راقب اللوجات** لاكتشاف الأخطاء مبكراً
4. **امسح cache** بعد تغيير config:
   ```bash
   php artisan config:clear
   php artisan cache:clear
   ```

---

## 🔄 إعادة التشغيل الكامل

```powershell
# 1. إيقاف كل شيء
Get-Process -Name python | Stop-Process -Force

# 2. بدء AI Service
.\start_ai_service.ps1

# 3. في نافذة جديدة: Laravel
cd backend
php artisan serve

# 4. في نافذة ثالثة: Frontend  
cd frontend-user
npm run dev
```

---

## 📞 الدعم

إذا واجهت مشاكل:
1. تحقق من اللوجات في كل نافذة
2. تأكد من API Keys صحيحة
3. تحقق أن الـ ports غير مستخدمة
4. امسح cache و أعد التشغيل

---

**ملاحظة**: هذا الدليل للتطوير المحلي فقط. للـ Production راجع docker-compose.yml

