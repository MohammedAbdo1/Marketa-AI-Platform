# Admin Frontend - SaaS Marketing Platform

## 🚀 Quick Start

```bash
# Install dependencies
npm install

# Start development server
npm run dev

# Build for production
npm run build

# Preview production build
npm run preview
```

## 📁 Project Structure

```
src/
├── axios.js                 # Global axios client
├── services/                # API services
│   ├── auth.service.js
│   ├── user.service.js
│   ├── plan.service.js
│   └── organization.service.js
├── stores/                  # Pinia stores
│   ├── auth.js
│   ├── user.js
│   ├── plan.js
│   └── organization.js
├── views/                   # Vue pages
│   ├── LoginView.vue
│   └── DashboardView.vue
├── router/                  # Vue Router
│   └── index.js
└── assets/                  # Static assets
    └── main.css
```

## 🔧 Environment Variables

Create `.env` file:

```env
VITE_API_BASE_URL=http://127.0.0.1:8000
```

## 🏗️ Architecture

### Services Layer
- **auth.service.js** - Authentication API calls
- **user.service.js** - User management API calls
- **plan.service.js** - Plan management API calls
- **organization.service.js** - Organization API calls

### Stores Layer
- **auth.js** - Authentication state & actions
- **user.js** - User management state & actions
- **plan.js** - Plan management state & actions
- **organization.js** - Organization state & actions

### Global Axios
- **axios.js** - Global axios client with interceptors
- Automatic token injection
- Error handling & logout on 401

## 🎯 Features

- ✅ **Authentication** - Login/Logout with Sanctum
- ✅ **State Management** - Pinia with persistence
- ✅ **API Services** - Organized service layer
- ✅ **Error Handling** - Global error management
- ✅ **Multi-Language** - Arabic & English (i18n)
- ✅ **RTL/LTR Support** - Dynamic direction switching
- ✅ **Responsive** - Mobile-friendly design
- ✅ **Professional Theme** - Clean admin interface

## 🔐 Authentication Flow

1. User enters credentials
2. AuthService calls `/admin/login`
3. Token stored in localStorage
4. Axios interceptor adds token to requests
5. 401 errors trigger automatic logout

## 📱 Usage

```javascript
// In Vue components
import { useAuthStore } from '@/stores/auth'
import { useUserStore } from '@/stores/user'

const authStore = useAuthStore()
const userStore = useUserStore()

// Login
await authStore.login({ email, password })

// Fetch users
await userStore.fetchUsers()
```

## 🚀 Production

```bash
# Build
npm run build

# Serve
npm run serve
```

## 🔧 Development

```bash
# Start backend
cd ../backend
php artisan serve

# Start frontend
npm run dev
```

## 📝 Notes

- Uses UUID for public API routes
- Hybrid ID strategy (id + uuid)
- Soft deletes for all main tables
- PostgreSQL with JSONB support
- Laravel Sanctum for authentication