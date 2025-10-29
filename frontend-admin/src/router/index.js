import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      redirect: '/login',
    },
    {
      path: '/login',
      name: 'login',
      component: () => import('../views/auth/Login.vue'),
      meta: { 
        requiresAuth: false,
        layout: 'guest'
      },
    },
    {
      path: "/app",
      name: "app",
      redirect: "/dashboard",
      component: () => import("../components/AppLayout.vue"),
      meta: {
        requiresAuth: true,
        layout: 'app'
      },
      children: [
        {
          path: "/dashboard",
          name: "dashboard",
          component: () => import('../views/DashboardView.vue'),
          meta: { 
            requiresAuth: true,
            title: 'Dashboard'
          },
        },
        {
          path: "/admins",
          name: "admins",
          component: () => import('../views/admins/Admins.vue'),
          meta: { 
            requiresAuth: true,
            title: 'Admins'
          },
        },
        {
          path: "/admins/create",
          name: "admins.create",
          component: () => import('../views/admins/AdminForm.vue'),
          meta: { 
            requiresAuth: true,
            title: 'Create Admin'
          },
        },
        {
          path: "/admins/:uuid/edit",
          name: "admins.edit",
          component: () => import('../views/admins/AdminForm.vue'),
          meta: { 
            requiresAuth: true,
            title: 'Edit Admin'
          },
        },
        {
          path: "/users",
          name: "users",
          component: () => import('../views/users/Users.vue'),
          meta: { 
            requiresAuth: true,
            title: 'Users'
          },
        },
        {
          path: "/users/create",
          name: "users.create",
          component: () => import('../views/users/UserForm.vue'),
          meta: { 
            requiresAuth: true,
            title: 'Create User'
          },
        },
        {
          path: "/users/:uuid/edit",
          name: "users.edit",
          component: () => import('../views/users/UserForm.vue'),
          meta: { 
            requiresAuth: true,
            title: 'Edit User'
          },
        },
        {
          path: "/roles",
          name: "roles",
          component: () => import('../views/roles/Roles.vue'),
          meta: { 
            requiresAuth: true,
            title: 'Roles'
          },
        },
        {
          path: "/roles/create",
          name: "roles.create",
          component: () => import('../views/roles/RoleForm.vue'),
          meta: { 
            requiresAuth: true,
            title: 'Create Role'
          },
        },
        {
          path: "/roles/:id/edit",
          name: "roles.edit",
          component: () => import('../views/roles/RoleForm.vue'),
          meta: { 
            requiresAuth: true,
            title: 'Edit Role'
          },
        },
        {
          path: "/customers",
          name: "customers",
          component: () => import('../views/customers/Customers.vue'),
          meta: { 
            requiresAuth: true,
            title: 'Customers'
          },
        },
        {
          path: "/customers/create",
          name: "customers.create",
          component: () => import('../views/customers/CustomerForm.vue'),
          meta: { 
            requiresAuth: true,
            title: 'Create Customer'
          },
        },
        {
          path: "/customers/:uuid/edit",
          name: "customers.edit",
          component: () => import('../views/customers/CustomerForm.vue'),
          meta: { 
            requiresAuth: true,
            title: 'Edit Customer'
          },
        },
        {
          path: "/customers/:uuid/details",
          name: "customers.details",
          component: () => import('../views/customers/CustomerDetails.vue'),
          meta: { 
            requiresAuth: true,
            title: 'Customer Details'
          },
        },
        {
          path: "/plans",
          name: "plans",
          component: () => import('../views/plans/Plans.vue'),
          meta: { 
            requiresAuth: true,
            title: 'Plans'
          },
        },
        {
          path: "/plans/create",
          name: "plans.create",
          component: () => import('../views/plans/PlanForm.vue'),
          meta: { 
            requiresAuth: true,
            title: 'Create Plan'
          },
        },
        {
          path: "/plans/:id/edit",
          name: "plans.edit",
          component: () => import('../views/plans/PlanForm.vue'),
          meta: { 
            requiresAuth: true,
            title: 'Edit Plan'
          },
        },
        {
          path: "/organizations",
          name: "organizations",
          component: () => import('../views/organizations/Organizations.vue'),
          meta: { 
            requiresAuth: true,
            title: 'Organizations'
          },
        },
        {
          path: "/organizations/create",
          name: "organizations.create",
          component: () => import('../views/organizations/OrganizationForm.vue'),
          meta: { 
            requiresAuth: true,
            title: 'Create Organization'
          },
        },
        {
          path: "/organizations/:uuid/edit",
          name: "organizations.edit",
          component: () => import('../views/organizations/OrganizationForm.vue'),
          meta: { 
            requiresAuth: true,
            title: 'Edit Organization'
          },
        },
        // CMS Routes
        {
          path: "/admin.cms/pages",
          name: "admin.cms.pages",
          component: () => import('../views/cms/Pages.vue'),
          meta: { 
            requiresAuth: true,
            title: 'Pages Management'
          },
        },
        {
          path: "/admin.cms/sections",
          name: "admin.cms.sections",
          component: () => import('../views/cms/Sections.vue'),
          meta: { 
            requiresAuth: true,
            title: 'Sections Management'
          },
        },
        {
          path: "/admin.testimonials",
          name: "admin.testimonials",
          component: () => import('../views/testimonials/Testimonials.vue'),
          meta: { 
            requiresAuth: true,
            title: 'Testimonials'
          },
        },
        {
          path: "/admin.faqs",
          name: "admin.faqs",
          component: () => import('../views/faqs/Faqs.vue'),
          meta: { 
            requiresAuth: true,
            title: 'FAQs'
          },
        },
      ],
    },
    {
      path: "/:pathMatch(.*)",
      name: "notfound",
      component: () => import('../views/NotFound.vue'),
    },
  ],
})

// Navigation guards
router.beforeEach((to, from, next) => {
  const authStore = useAuthStore()
  const requiresAuth = to.meta.requiresAuth

  if (requiresAuth && !authStore.isAuthenticated) {
    next('/login')
  } else if (to.path === '/login' && authStore.isAuthenticated) {
    next('/dashboard')
  } else {
    next()
  }
})

export default router
