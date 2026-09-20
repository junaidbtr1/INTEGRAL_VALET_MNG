import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/authStore'

const router = createRouter({
  history: createWebHistory(),
  routes: [
    // Login
    {
      path: '/login',
      name: 'auth.login',
      component: () => import('@/pages/auth/LoginPage.vue'),
      meta: { guest: true },
    },

    // App (single layout — sidebar changes by role)
    {
      path: '/',
      component: () => import('@/layouts/AppLayout.vue'),
      meta: { requiresAuth: true },
      children: [
        // Dashboard (everyone)
        {
          path: '',
          name: 'app.dashboard',
          component: () => import('@/pages/DashboardPage.vue'),
        },

        // ── Building Owner pages ──
        {
          path: 'buildings',
          name: 'owner.buildings',
          component: () => import('@/pages/owner/BuildingsPage.vue'),
          meta: { buildingOwner: true },
        },

        // ── Super Admin pages ──
        {
          path: 'tenants',
          name: 'app.tenants',
          component: () => import('@/pages/super-admin/TenantsPage.vue'),
          meta: { superAdmin: true },
        },
        {
          path: 'tenants/:id',
          name: 'app.tenants.show',
          component: () => import('@/pages/super-admin/TenantDetailPage.vue'),
          meta: { superAdmin: true },
          props: true,
        },
        {
          path: 'plans',
          name: 'app.plans',
          component: () => import('@/pages/super-admin/PlansPage.vue'),
          meta: { superAdmin: true },
        },
        {
          path: 'users',
          name: 'app.users',
          component: () => import('@/pages/super-admin/UsersPage.vue'),
          meta: { superAdmin: true },
        },

        // ── Tenant pages (permission-based) ──
        {
          path: 'tickets',
          name: 'app.tickets',
          component: () => import('@/pages/admin/TicketsPage.vue'),
          meta: { permission: 'tickets.view' },
        },
        {
          path: 'slots',
          name: 'app.slots',
          component: () => import('@/pages/admin/SlotsPage.vue'),
          meta: { permission: 'slots.view' },
        },
        {
          path: 'vehicles',
          name: 'app.vehicles',
          component: () => import('@/pages/admin/VehiclesPage.vue'),
          meta: { permission: 'vehicles.view' },
        },
        {
          path: 'payments',
          name: 'app.payments',
          component: () => import('@/pages/admin/PaymentsPage.vue'),
          meta: { permission: 'payments.view' },
        },
        {
          path: 'coupons',
          name: 'app.coupons',
          component: () => import('@/pages/admin/CouponsPage.vue'),
          meta: { permission: 'coupons.view' },
        },
        {
          path: 'roles',
          name: 'app.roles',
          component: () => import('@/pages/admin/RolesPage.vue'),
          meta: { permission: 'users.assign_role' },
        },
        {
          path: 'staff',
          name: 'app.staff',
          component: () => import('@/pages/admin/StaffPage.vue'),
          meta: { permission: 'users.view' },
        },
        {
          path: 'shifts',
          name: 'app.shifts',
          component: () => import('@/pages/admin/ShiftsPage.vue'),
          meta: { permission: 'users.view' },
        },
        {
          path: 'reports',
          name: 'app.reports',
          component: () => import('@/pages/admin/ReportsPage.vue'),
          meta: { permission: 'reports.view' },
        },
        {
          path: 'shift-reports',
          name: 'app.shift-reports',
          component: () => import('@/pages/admin/ShiftReportsPage.vue'),
          meta: { permission: 'reports.view' },
        },
        {
          path: 'settings',
          name: 'app.settings',
          component: () => import('@/pages/admin/SettingsPage.vue'),
          meta: { permission: 'settings.view' },
        },
      ],
    },

    // Catch all
    {
      path: '/:pathMatch(.*)*',
      redirect: '/login',
    },
  ],
})

router.beforeEach(async (to) => {
  const authStore = useAuthStore()

  // Auth required
  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    return { name: 'auth.login' }
  }

  // Guest page but already logged in → go to dashboard
  if (to.meta.guest && authStore.isAuthenticated) {
    return { name: 'app.dashboard' }
  }

  // Fetch user data if needed
  if (authStore.isAuthenticated && !authStore.user) {
    await authStore.fetchMe()
  }

  // Super admin only pages
  if (to.meta.superAdmin && !authStore.isSuperAdmin) {
    return { name: 'app.dashboard' }
  }

  // Building owner pages
  if (to.meta.buildingOwner && !authStore.isBuildingOwner && !authStore.isSuperAdmin) {
    return { name: 'app.dashboard' }
  }

  // Permission check
  if (to.meta.permission && !authStore.can(to.meta.permission as string)) {
    return { name: 'app.dashboard' }
  }
})

export default router
