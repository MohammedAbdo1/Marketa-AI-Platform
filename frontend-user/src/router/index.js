import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '../stores/auth'

// Layouts
import PublicLayout from '../layouts/PublicLayout.vue'
import AuthLayout from '../layouts/AuthLayout.vue'
import DashboardLayout from '../layouts/DashboardLayout.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    // Public Routes
    {
      path: '/',
      component: PublicLayout,
      children: [
        {
          path: '',
          name: 'home',
          component: () => import('../views/public/Home.vue'),
        },
        {
          path: 'about',
          name: 'about',
          component: () => import('../views/public/About.vue'),
        },
        {
          path: 'pricing',
          name: 'pricing',
          component: () => import('../views/public/Pricing.vue'),
        },
        {
          path: 'faq',
          name: 'faq',
          component: () => import('../views/public/Faq.vue'),
        },
      ],
    },
    
    // Auth Routes
    {
      path: '/auth',
      component: AuthLayout,
      children: [
        {
          path: 'login',
          name: 'login',
          component: () => import('../views/auth/Login.vue'),
          meta: { guest: true }
        },
        {
          path: 'register',
          name: 'register',
          component: () => import('../views/auth/Register.vue'),
          meta: { guest: true }
        },
        {
          path: 'forgot-password',
          name: 'forgot-password',
          component: () => import('../views/auth/ForgotPassword.vue'),
          meta: { guest: true }
        },
        {
          path: 'reset-password/:token',
          name: 'reset-password',
          component: () => import('../views/auth/ResetPassword.vue'),
          meta: { guest: true }
        },
        {
          path: 'verify-email/:id/:hash',
          name: 'verify-email',
          component: () => import('../views/auth/VerifyEmail.vue'),
        },
        {
          path: 'verify-email-notice',
          name: 'verify-email-notice',
          component: () => import('../views/auth/EmailVerificationNotice.vue'),
        },
        {
          path: 'google/callback',
          name: 'google-callback',
          component: () => import('../views/auth/GoogleCallback.vue'),
        },
      ],
    },
    
    // Dashboard Routes (Protected)
    {
      path: '/dashboard',
      component: DashboardLayout,
      meta: { requiresAuth: true },
      children: [
        {
          path: '',
          name: 'dashboard',
          component: () => import('../views/dashboard/Home.vue'),
        },
        {
          path: 'profile',
          name: 'profile',
          component: () => import('../views/dashboard/Profile.vue'),
        },
        {
          path: 'brands',
          name: 'brands',
          component: () => import('../views/dashboard/brands/BrandList.vue'),
        },
        {
          path: 'brands/create',
          name: 'brands.create',
          component: () => import('../views/dashboard/brands/BrandForm.vue'),
        },
        {
          path: 'campaigns',
          name: 'campaigns',
          component: () => import('../views/dashboard/campaigns/Campaigns.vue'),
        },
        {
          path: 'campaigns/wizard',
          name: 'campaign-wizard',
          component: () => import('../views/dashboard/campaigns/CampaignWizard.vue'),
        },
        {
          path: 'campaigns/create',
          name: 'campaigns.create',
          component: () => import('../views/dashboard/campaigns/CampaignWizard.vue'),
        },
        {
          path: 'campaigns/:uuid',
          name: 'campaigns.show',
          component: () => import('../views/dashboard/campaigns/CampaignDetails.vue'),
        },
        {
          path: 'usage',
          name: 'usage',
          component: () => import('../views/dashboard/Usage.vue'),
        },
        {
          path: 'settings',
          name: 'settings',
          component: () => import('../views/dashboard/Settings.vue'),
        },
      ],
    },
    
    // 404 Not Found
    {
      path: '/:pathMatch(.*)*',
      name: 'not-found',
      component: () => import('../views/NotFound.vue'),
    },
  ],
  scrollBehavior(to, from, savedPosition) {
    if (savedPosition) {
      return savedPosition
    } else {
      return { top: 0 }
    }
  },
})

// Navigation Guards
router.beforeEach(async (to, from, next) => {
  const authStore = useAuthStore()
  const requiresAuth = to.meta.requiresAuth
  const isGuest = to.meta.guest

  // If route requires auth and user is not authenticated
  if (requiresAuth && !authStore.isAuthenticated) {
    next({ name: 'login', query: { redirect: to.fullPath } })
  }
  // If route is for guests only (login, register) and user is authenticated
  else if (isGuest && authStore.isAuthenticated) {
    next({ name: 'dashboard' })
  }
  // Otherwise, proceed
  else {
    next()
  }
})

export default router
