<script setup lang="ts">
import { ref, computed } from 'vue'
import { useAuthStore } from '@/stores/authStore'
import { useRouter, useRoute } from 'vue-router'

const authStore = useAuthStore()
const router = useRouter()
const route = useRoute()
const sidebarOpen = ref(true)

const menuItems = [
  { label: 'Dashboard', icon: 'pi pi-home', route: 'app.dashboard' },
  { label: 'Tenants', icon: 'pi pi-building', route: 'app.tenants' },
  { label: 'Plans', icon: 'pi pi-credit-card', route: 'app.plans' },
  { label: 'Users', icon: 'pi pi-users', route: 'app.users' },
]

const currentRoute = computed(() => route.name as string)

function navigateTo(routeName: string) {
  router.push({ name: routeName })
}

async function handleLogout() {
  await authStore.logout()
}
</script>

<template>
  <div class="flex h-screen bg-gray-50">
    <!-- Sidebar -->
    <aside
      class="bg-gray-900 text-white transition-all duration-300 flex flex-col"
      :class="sidebarOpen ? 'w-64' : 'w-16'"
    >
      <!-- Logo -->
      <div class="flex items-center gap-3 h-16 px-4 border-b border-gray-700">
        <div class="w-8 h-8 rounded-lg bg-white flex items-center justify-center shrink-0 overflow-hidden">
          <img src="/logo.png" alt="Logo" class="w-7 h-7 object-contain" />
        </div>
        <span v-if="sidebarOpen" class="text-sm font-bold text-white leading-tight">Integral Valet<br/><span class="text-blue-400 font-medium text-xs">Management</span></span>
      </div>

      <!-- Menu -->
      <nav class="flex-1 py-4 space-y-1">
        <button
          v-for="item in menuItems"
          :key="item.route"
          class="flex items-center w-full px-4 py-3 text-left transition-colors"
          :class="
            currentRoute === item.route
              ? 'bg-blue-600 text-white'
              : 'text-gray-300 hover:bg-gray-800 hover:text-white'
          "
          @click="navigateTo(item.route)"
        >
          <i :class="item.icon" class="text-lg" />
          <span v-if="sidebarOpen" class="ml-3">{{ item.label }}</span>
        </button>
      </nav>

      <!-- User -->
      <div class="border-t border-gray-700 p-4">
        <div v-if="sidebarOpen" class="flex items-center gap-3">
          <div class="w-8 h-8 rounded-full bg-blue-500 flex items-center justify-center text-sm font-bold">
            {{ authStore.user?.name?.charAt(0) ?? 'A' }}
          </div>
          <div class="flex-1 min-w-0">
            <p class="text-sm font-medium truncate">{{ authStore.user?.name }}</p>
            <p class="text-xs text-gray-400 truncate">Super Admin</p>
          </div>
          <button class="text-gray-400 hover:text-white" @click="handleLogout">
            <i class="pi pi-sign-out" />
          </button>
        </div>
      </div>
    </aside>

    <!-- Main -->
    <div class="flex-1 flex flex-col overflow-hidden">
      <!-- Header -->
      <header class="bg-white shadow-sm h-16 flex items-center justify-between px-6">
        <button class="text-gray-500 hover:text-gray-700" @click="sidebarOpen = !sidebarOpen">
          <i class="pi pi-bars text-lg" />
        </button>
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
