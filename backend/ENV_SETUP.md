# إعداد ملف .env

## المشكلة:
Google OAuth يعطي خطأ "Missing required parameter: client_id" لأن ملف `.env` غير موجود.

## الحل:

### 1. أنشئ ملف `.env` في مجلد `backend`:

```env
APP_NAME=Marketa
APP_ENV=local
APP_KEY=base64:your-app-key-here
APP_DEBUG=true
APP_TIMEZONE=UTC
APP_URL=http://localhost:8000
FRONTEND_URL=http://localhost:5173

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=marketa
DB_USERNAME=root
DB_PASSWORD=

# Email Configuration
MAIL_MAILER=log
MAIL_HOST=127.0.0.1
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="noreply@marketa.ai"
MAIL_FROM_NAME="${APP_NAME}"

# Google OAuth - يجب تحديث هذه القيم
GOOGLE_CLIENT_ID=your-google-client-id-here
GOOGLE_CLIENT_SECRET=your-google-client-secret-here
GOOGLE_REDIRECT_URI="${APP_URL}/api/auth/google/callback"

# Queue
QUEUE_CONNECTION=database

# Session
SESSION_DRIVER=database
SESSION_LIFETIME=120

# Cache
CACHE_STORE=database

# Sanctum
SANCTUM_STATEFUL_DOMAINS=localhost,localhost:5173,127.0.0.1,127.0.0.1:5175
```

### 2. توليد APP_KEY:

```bash
cd backend
php artisan key:generate
```

### 3. إعداد Google OAuth:

1. اذهب إلى: https://console.cloud.google.com/
2. أنشئ مشروع جديد
3. **APIs & Services** → **Credentials**
4. **Create Credentials** → **OAuth 2.0 Client ID**
5. اختر **Web application**
6. أضف:
   ```
   Authorized JavaScript origins:
   - http://localhost:8000
   
   Authorized redirect URIs:
   - http://localhost:8000/api/auth/google/callback
   ```
7. احفظ `Client ID` و `Client Secret`
8. ضعهما في ملف `.env`:
   ```env
   GOOGLE_CLIENT_ID=your-actual-client-id-here
   GOOGLE_CLIENT_SECRET=your-actual-client-secret-here
   ```

### 4. إعادة تشغيل Backend:

```bash
cd backend
php artisan serve
```

### 5. اختبار Google OAuth:

1. اذهب إلى: http://localhost:5173/auth/login
2. اضغط "Continue with Google"
3. يجب أن يعمل الآن!

---

## ملاحظة:
إذا لم تريد إعداد Google OAuth الآن، يمكنك استخدام Email/Password registration العادي:
- Email: test@test.com
- Password: 123456
