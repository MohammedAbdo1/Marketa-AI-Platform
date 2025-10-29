# Frontend User - Setup Guide

## 1. تثبيت Dependencies

```bash
cd frontend-user
npm install
```

---

## 2. إنشاء `.env` file

أنشئ ملف `.env` في مجلد `frontend-user`:

```env
VITE_API_URL=http://localhost:8000/api
```

---

## 3. تشغيل المشروع

```bash
npm run dev
```

المشروع سيعمل على: `http://localhost:5173` (أو port آخر إذا كان مشغولاً)

---

## 4. الصفحات المتاحة

### Public Pages (بدون تسجيل دخول):
- `http://localhost:5173/` - Landing Page
- `http://localhost:5173/about` - About
- `http://localhost:5173/pricing` - Pricing
- `http://localhost:5173/faq` - FAQ

### Auth Pages:
- `http://localhost:5173/auth/login` - Login
- `http://localhost:5173/auth/register` - Register
- `http://localhost:5173/auth/forgot-password` - Forgot Password

### Dashboard (بعد تسجيل الدخول):
- `http://localhost:5173/dashboard` - Dashboard Home
- `http://localhost:5173/dashboard/profile` - Profile
- `http://localhost:5173/dashboard/brands` - Brands
- `http://localhost:5173/dashboard/campaigns` - Campaigns
- `http://localhost:5173/dashboard/usage` - Usage Stats

---

## 5. ربط مع Backend

تأكد من تشغيل Backend:

```bash
cd backend
php artisan serve
```

Backend سيعمل على: `http://localhost:8000`

---

## 6. Google OAuth

راجع ملف `backend/GOOGLE_OAUTH_SETUP.md` لإعداد Google OAuth.

---

## 7. Features

✅ **3 Layouts احترافية:**
- PublicLayout - للصفحات العامة
- AuthLayout - لصفحات التسجيل والدخول
- DashboardLayout - للوحة التحكم

✅ **Multilingual (عربي + إنجليزي)**
✅ **RTL/LTR Support**
✅ **Responsive Design**
✅ **Google OAuth**
✅ **Email Verification**
✅ **Auto-subscribe to Free Plan**
✅ **Daily Usage Limits (20 requests/day)**

