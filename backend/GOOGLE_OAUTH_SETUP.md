# Google OAuth Setup Guide

## 1. إنشاء Google OAuth Credentials

### الخطوات:

1. اذهب إلى [Google Cloud Console](https://console.cloud.google.com/)
2. أنشئ مشروع جديد أو استخدم مشروع موجود
3. انتقل إلى **APIs & Services** → **Credentials**
4. اضغط **Create Credentials** → **OAuth 2.0 Client ID**
5. اختر **Web application**
6. أضف:
   - **Authorized JavaScript origins**: `http://localhost:8000`
   - **Authorized redirect URIs**: `http://localhost:8000/api/auth/google/callback`
7. احفظ `Client ID` و `Client Secret`

---

## 2. تحديث `.env` في Backend

أضف هذه الأسطر في ملف `.env`:

```env
# Google OAuth
GOOGLE_CLIENT_ID=your-google-client-id-here
GOOGLE_CLIENT_SECRET=your-google-client-secret-here
GOOGLE_REDIRECT_URI=http://localhost:8000/api/auth/google/callback

# Frontend URL (للتوجيه بعد OAuth)
FRONTEND_URL=http://localhost:5173

# Sanctum (للـ CORS)
SANCTUM_STATEFUL_DOMAINS=localhost,localhost:5173,127.0.0.1,127.0.0.1:5175

# Mail Configuration (للـ Email Verification)
MAIL_MAILER=log
MAIL_FROM_ADDRESS="noreply@marketa.ai"
MAIL_FROM_NAME="Marketa"
```

---

## 3. اختبار Google OAuth

### في المتصفح:

1. افتح: `http://localhost:5173/auth/login`
2. اضغط على زر "تسجيل الدخول عبر Google"
3. سيتم توجيهك إلى صفحة Google للمصادقة
4. اختر حسابك في Google
5. سيتم توجيهك إلى Dashboard تلقائياً

---

## 4. ما يحدث خلف الكواليس:

```
المستخدم يضغط "Google Login"
    ↓
Frontend: يوجه إلى → http://localhost:8000/api/auth/google
    ↓
Backend: يوجه إلى صفحة Google OAuth
    ↓
المستخدم يختار حسابه في Google
    ↓
Google: يرجع إلى → http://localhost:8000/api/auth/google/callback
    ↓
Backend (SocialAuthController):
  1. يستقبل بيانات Google User
  2. يبحث عن User بنفس الـ email
  3. إذا موجود: Login فقط
  4. إذا جديد:
     - ينشئ User
     - ينشئ Organization
     - Auto-subscribe للباقة المجانية
     - ينشئ DailyUsage
  5. ينشئ Token
  6. يوجه إلى → http://localhost:5173/auth/google/callback?token=xxx
    ↓
Frontend (GoogleCallback.vue):
  1. يستقبل Token
  2. يحفظه في localStorage
  3. يحمل بيانات User
  4. يوجه للـ Dashboard
```

---

## 5. إعدادات Production

عند النشر على الإنترنت، حدّث:

```env
APP_URL=https://yourdomain.com
FRONTEND_URL=https://app.yourdomain.com

GOOGLE_REDIRECT_URI=https://yourdomain.com/api/auth/google/callback

SANCTUM_STATEFUL_DOMAINS=yourdomain.com,app.yourdomain.com
```

وأضف نفس URLs في Google Console:
- Authorized JavaScript origins: `https://yourdomain.com`
- Authorized redirect URIs: `https://yourdomain.com/api/auth/google/callback`

---

## 6. Troubleshooting

### المشكلة: "Redirect URI mismatch"
**الحل**: تأكد من أن الـ URI في Google Console مطابق تماماً للـ URI في `.env`

### المشكلة: "Invalid client"
**الحل**: تحقق من `GOOGLE_CLIENT_ID` و `GOOGLE_CLIENT_SECRET`

### المشكلة: CORS errors
**الحل**: تأكد من `SANCTUM_STATEFUL_DOMAINS` يتضمن frontend domain

