<script setup lang="ts">
import { ref, computed } from 'vue'
import { useAuthStore } from '@/stores/authStore'
import { useRouter, useRoute } from 'vue-router'

const authStore = useAuthStore()
const router = useRouter()
const route = useRoute()
const sidebarOpen = ref(true)

const tenantName = computed(() => authStore.user?.tenant?.name ?? 'Dashboard')
const tenantColor = computed(() => authStore.user?.tenant?.primary_color ?? '#3B82F6')

const menuItems = computed(() => {
  const items = [
    { label: 'Dashboard', icon: 'pi pi-home', route: 'admin.dashboard', permission: null },
    { label: 'Tickets', icon: 'pi pi-ticket', route: 'admin.tickets', permission: 'tickets.view' },
    { label: 'Parking Slots', icon: 'pi pi-th-large', route: 'admin.slots', permission: 'slots.view' },
    { label: 'Vehicles', icon: 'pi pi-car', route: 'admin.vehicles', permission: 'vehicles.view' },
    { label: 'Payments', icon: 'pi pi-wallet', route: 'admin.payments', permission: 'payments.view' },
    { label: 'Coupons', icon: 'pi pi-tag', route: 'admin.coupons', permission: 'coupons.view' },
    { label: 'Users', icon: 'pi pi-users', route: 'admin.users', permission: 'users.view' },
    { label: 'Reports', icon: 'pi pi-chart-bar', route: 'admin.reports', permission: 'reports.view' },
    { label: 'Settings', icon: 'pi pi-cog', route: 'admin.settings', permission: 'settings.view' },
  ]
  return items.filter(item => !item.permission || authStore.can(item.permission))
})

const currentRoute = computed(() => route.name as string)

function navigateTo(routeName: string) {
  router.push({ name: routeName })
}

async function handleLogout() {
  await authStore.logout()
}

const userRole = computed(() => {
  const roles = authStore.roles
  if (roles.includes('tenant_admin')) return 'Admin'
  if (roles.includes('supervisor')) return 'Supervisor'
  if (roles.includes('valet_staff')) return 'Valet'
  if (roles.includes('cashier')) return 'Cashier'
  if (roles.includes('viewer')) return 'Viewer'
  return 'User'
})
</script>

<template>
  <div class="flex h-screen bg-gray-50">
    <!-- Sidebar -->
    <aside
      class="text-white transition-all duration-300 flex flex-col"
      :style="{ backgroundColor: tenantColor }"
      :class="sidebarOpen ? 'w-64' : 'w-16'"
    >
      <!-- Logo -->
      <div class="flex items-center h-16 px-4 border-b border-white/20">
        <span v-if="sidebarOpen" class="text-lg font-bold truncate">{{ tenantName }}</span>
        <span v-else class="text-lg font-bold">{{ tenantName.charAt(0) }}</span>
      </div>

      <!-- Menu -->
      <nav class="flex-1 py-4 space-y-1 overflow-y-auto">
        <button
          v-for="item in menuItems"
          :key="item.route"
          class="flex items-center w-full px-4 py-3 text-left transition-colors"
          :class="
            currentRoute === item.route
              ? 'bg-white/20 text-white'
              : 'text-white/80 hover:bg-white/10 hover:text-white'
          "
          @click="navigateTo(item.route)"
        >
          <i :class="item.icon" class="text-lg" />
          <span v-if="sidebarOpen" class="ml-3 text-sm">{{ item.label }}</span>
        </button>
      </nav>

      <!-- User -->
      <div class="border-t border-white/20 p-4">
        <div v-if="sidebarOpen" class="flex items-center gap-3">
          <div class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center text-sm font-bold">
            {{ authStore.user?.name?.charAt(0) ?? 'U' }}
          </div>
          <div class="flex-1 min-w-0">
            <p class="text-sm font-medium truncate">{{ authStore.user?.name }}</p>
            <p class="text-xs text-white/60 truncate">{{ userRole }}</p>
          </div>
          <button class="text-white/60 hover:text-white" @click="handleLogout">
            <i class="pi pi-sign-out" />
          </button>
        </div>
      </div>
    </aside>

    <!-- Main -->
    <div class="flex-1 flex flex-col overflow-hidden">
      <!-- Header -->
      <header class="bg-white shadow-sm h-16 flex items-center justify-between px-6">
        <div class="flex items-center gap-4">
          <button class="text-gray-500 hover:text-gray-700" @click="sidebarOpen = !sidebarOpen">
            <i class="pi pi-bars text-lg" />
          </button>
          <h2 class="text-lg font-semibold text-gray-800">{{ tenantName }}</h2>
        </div>
        <div class="flex items-center gap-4">
          <span class="text-sm text-gray-500">{{ authStore.user?.email }}</span>
        </div>
      </header>

      <!-- Content -->
      <main class="flex-1 overflow-auto p-6">
        <router-view />
      </main>
    </div>
  </div>
</template>
