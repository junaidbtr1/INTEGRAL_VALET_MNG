<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useAuthStore } from '@/stores/authStore'
import { useRouter, useRoute } from 'vue-router'

const authStore = useAuthStore()
const router = useRouter()
const route = useRoute()
const sidebarOpen = ref(true)
const sidebarHover = ref(false)
const isMobile = ref(false)
const mobileMenuOpen = ref(false)

function checkMobile() {
  isMobile.value = window.innerWidth < 1024
  if (isMobile.value) sidebarOpen.value = false
}

onMounted(() => { checkMobile(); window.addEventListener('resize', checkMobile) })
onUnmounted(() => window.removeEventListener('resize', checkMobile))

const tenantColor = computed(() => {
  if (authStore.isBuildingOwner) return authStore.ownedBuildings[0]?.primary_color ?? '#6366F1'
  return authStore.user?.tenant?.primary_color ?? '#6366F1'
})

const appTitle = computed(() => {
  if (authStore.isSuperAdmin) return 'Integral Valet Management'
  if (authStore.isBuildingOwner) return 'Integral Valet Management'
  return authStore.user?.tenant?.name ?? 'Dashboard'
})

const appSubtitle = computed(() => {
  if (authStore.isSuperAdmin) return 'Admin Console'
  if (authStore.isBuildingOwner) return 'Building Owner'
  return 'Parking Management'
})

const userRole = computed(() => {
  if (authStore.isSuperAdmin) return 'Super Admin'
  if (authStore.isBuildingOwner) return 'Building Owner'
  const r = authStore.roles[0]
  return r ? r.replace(/_/g, ' ').replace(/\b\w/g, (c: string) => c.toUpperCase()) : 'User'
})


const userInitials = computed(() => {
  const parts = (authStore.user?.name ?? 'U').split(' ')
  return parts.length > 1 ? (parts[0][0] + parts[1][0]).toUpperCase() : parts[0].substring(0, 2).toUpperCase()
})

interface MenuItem { label: string; icon: string; route: string; perm?: string | null }
interface MenuSection { title: string; items: MenuItem[] }

const menuSections = computed<MenuSection[]>(() => {
  if (authStore.isSuperAdmin) {
    return [
      { title: 'Overview', items: [{ label: 'Dashboard', icon: 'pi pi-objects-column', route: 'app.dashboard' }] },
      { title: 'Management', items: [
        { label: 'Tenants', icon: 'pi pi-building', route: 'app.tenants' },
        { label: 'Plans', icon: 'pi pi-box', route: 'app.plans' },
        { label: 'All Users', icon: 'pi pi-users', route: 'app.users' },
      ]},
    ]
  }

  if (authStore.isBuildingOwner) {
    return [
      { title: 'Overview', items: [
        { label: 'My Buildings', icon: 'pi pi-building', route: 'owner.buildings' },
      ]},
      { title: 'Operations', items: [
        { label: 'Parking', icon: 'pi pi-car', route: 'app.tickets' },
        { label: 'Parking Slots', icon: 'pi pi-th-large', route: 'app.slots' },
        { label: 'Vehicles', icon: 'pi pi-id-card', route: 'app.vehicles' },
      ]},
      { title: 'Finance', items: [
        { label: 'Payments', icon: 'pi pi-wallet', route: 'app.payments' },
        { label: 'Coupons', icon: 'pi pi-tag', route: 'app.coupons' },
      ]},
      { title: 'Team', items: [
        { label: 'Staff', icon: 'pi pi-users', route: 'app.staff' },
        { label: 'Shifts', icon: 'pi pi-clock', route: 'app.shifts' },
        { label: 'Roles', icon: 'pi pi-shield', route: 'app.roles' },
      ]},
      { title: 'Insights', items: [
        { label: 'Reports', icon: 'pi pi-chart-bar', route: 'app.reports' },
        { label: 'Shift Reports', icon: 'pi pi-id-card', route: 'app.shift-reports' },
        { label: 'Settings', icon: 'pi pi-cog', route: 'app.settings' },
      ]},
    ]
  }

  const sections: MenuSection[] = [
    { title: 'Overview', items: [
      { label: 'Dashboard', icon: 'pi pi-objects-column', route: 'app.dashboard' },
    ] },
    { title: 'Operations', items: [
      { label: 'Parking', icon: 'pi pi-car', route: 'app.tickets', perm: 'tickets.view' },
      { label: 'Parking Slots', icon: 'pi pi-th-large', route: 'app.slots', perm: 'slots.view' },
      { label: 'Vehicles', icon: 'pi pi-id-card', route: 'app.vehicles', perm: 'vehicles.view' },
    ].filter(i => !i.perm || authStore.can(i.perm)) },
    { title: 'Finance', items: [
      { label: 'Payments', icon: 'pi pi-wallet', route: 'app.payments', perm: 'payments.view' },
      { label: 'Coupons', icon: 'pi pi-tag', route: 'app.coupons', perm: 'coupons.view' },
    ].filter(i => !i.perm || authStore.can(i.perm)) },
    { title: 'Team', items: [
      { label: 'Staff', icon: 'pi pi-users', route: 'app.staff', perm: 'users.view' },
      { label: 'Shifts', icon: 'pi pi-clock', route: 'app.shifts', perm: 'users.view' },
      { label: 'Roles', icon: 'pi pi-shield', route: 'app.roles', perm: 'users.assign_role' },
    ].filter(i => !i.perm || authStore.can(i.perm)) },
    { title: 'Insights', items: [
      { label: 'Reports', icon: 'pi pi-chart-bar', route: 'app.reports', perm: 'reports.view' },
      { label: 'Shift Reports', icon: 'pi pi-id-card', route: 'app.shift-reports', perm: 'reports.view' },
    ].filter(i => !i.perm || authStore.can(i.perm)) },
    { title: 'System', items: [
      { label: 'Settings', icon: 'pi pi-cog', route: 'app.settings', perm: 'settings.view' },
    ].filter(i => !i.perm || authStore.can(i.perm)) },
  ]
  return sections.filter(s => s.items.length > 0)
})

const currentRouteName = computed(() => route.name as string)
const expanded = computed(() => sidebarOpen.value || sidebarHover.value)
const pageNameMap: Record<string, string> = {
  'app.tickets': 'Parking',
  'app.slots': 'Parking Slots',
  'app.staff': 'Staff',
  'app.shifts': 'Shifts',
  'app.roles': 'Roles',
  'app.payments': 'Payments',
  'app.coupons': 'Coupons',
  'app.reports': 'Reports',
  'app.shift-reports': 'Shift Reports',
  'app.settings': 'Settings',
  'app.vehicles': 'Vehicles',
  'app.dashboard': 'Dashboard',
  'app.tenants': 'Tenants',
  'app.plans': 'Plans',
  'app.users': 'Building Owners',
  'owner.buildings': 'My Buildings',
}
const pageName = computed(() => pageNameMap[String(route.name)] ?? String(route.name).split('.').pop()?.replace(/-/g, ' ') ?? '')

function isActive(routeName: string): boolean {
  return currentRouteName.value === routeName || currentRouteName.value?.startsWith(routeName + '.')
}

function navigateTo(routeName: string) {
  router.push({ name: routeName })
  if (isMobile.value) mobileMenuOpen.value = false
}

async function handleLogout() {
  await authStore.logout()
}

</script>

<template>
  <div class="flex h-screen bg-slate-50">

    <!-- Mobile Overlay -->
    <transition
      enter-active-class="transition-opacity duration-300"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition-opacity duration-300"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div
        v-if="isMobile && mobileMenuOpen"
        class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-40 lg:hidden"
        @click="mobileMenuOpen = false"
      />
    </transition>

    <!-- ═══════ Sidebar ═══════ -->
    <aside
      class="fixed lg:relative z-50 h-full flex flex-col transition-all duration-300 ease-in-out border-r border-white/[0.06]"
      :class="[
        expanded ? 'w-[260px]' : 'w-[72px]',
        isMobile && !mobileMenuOpen ? '-translate-x-full' : 'translate-x-0',
        'lg:translate-x-0',
      ]"
      :style="{
        background: authStore.isSuperAdmin
          ? 'linear-gradient(180deg, #0F172A 0%, #1E293B 100%)'
          : `linear-gradient(180deg, ${tenantColor} 0%, ${tenantColor}cc 100%)`
      }"
      @mouseenter="!isMobile && !sidebarOpen && (sidebarHover = true)"
      @mouseleave="sidebarHover = false"
    >

      <!-- Logo -->
      <div class="h-[68px] flex items-center px-5 border-b border-white/[0.08] shrink-0">
        <div class="flex items-center gap-3 min-w-0">
          <div class="w-9 h-9 rounded-lg bg-white flex items-center justify-center shrink-0 ring-1 ring-white/[0.08] overflow-hidden">
            <img src="/logo.png" alt="Logo" class="w-8 h-8 object-contain" />
          </div>
          <div v-if="expanded" class="min-w-0">
            <p class="text-[13px] font-semibold text-white truncate leading-tight">Integral Valet</p>
            <p class="text-[10px] text-white/40 uppercase tracking-[0.1em] font-medium">Management</p>
          </div>
        </div>
      </div>

      <!-- Navigation -->
      <nav class="flex-1 overflow-y-auto py-5 px-3 space-y-6 sidebar-scroll">
        <div v-for="section in menuSections" :key="section.title">
          <!-- Section label -->
          <p v-if="expanded" class="text-[10px] font-semibold uppercase tracking-[0.12em] text-white/30 px-3 mb-2">
            {{ section.title }}
          </p>
          <div v-else class="w-5 h-px bg-white/[0.08] mx-auto mb-3 mt-1" />

          <!-- Items -->
          <div class="space-y-1">
            <button
              v-for="item in section.items"
              :key="item.route"
              class="group relative flex items-center w-full rounded-lg transition-all duration-150"
              :class="[
                expanded ? 'px-3 py-[9px]' : 'px-0 py-[9px] justify-center',
                isActive(item.route)
                  ? 'bg-white/[0.14] text-white'
                  : 'text-white/55 hover:text-white/90 hover:bg-white/[0.07]',
              ]"
              @click="navigateTo(item.route)"
            >
              <!-- Active indicator bar -->
              <div
                v-if="isActive(item.route)"
                class="absolute left-0 top-1/2 -translate-y-1/2 w-[3px] h-5 rounded-r-full bg-white/70"
                :class="expanded ? '' : 'hidden'"
              />

              <div class="flex items-center justify-center w-7 h-7 rounded-md shrink-0">
                <i :class="item.icon" class="text-[14px]" />
              </div>
              <span v-if="expanded" class="ml-3 text-[13px] font-medium truncate">{{ item.label }}</span>

              <!-- Tooltip when collapsed -->
              <div
                v-if="!expanded"
                class="absolute left-full ml-3 px-2.5 py-1.5 bg-slate-800 text-white text-xs font-medium rounded-md opacity-0 group-hover:opacity-100 pointer-events-none transition-opacity duration-150 whitespace-nowrap shadow-lg z-50"
              >
                {{ item.label }}
              </div>
            </button>
          </div>
        </div>
      </nav>

      <!-- User -->
      <div class="border-t border-white/[0.08] p-3 shrink-0">
        <div
          class="flex items-center rounded-lg p-2.5 transition-colors hover:bg-white/[0.07] cursor-default"
          :class="expanded ? 'gap-3' : 'justify-center'"
        >
          <div class="w-8 h-8 rounded-full bg-white/[0.15] ring-1 ring-white/[0.1] flex items-center justify-center text-[11px] font-bold text-white shrink-0">
            {{ userInitials }}
          </div>
          <div v-if="expanded" class="flex-1 min-w-0">
            <p class="text-[13px] font-medium text-white/90 truncate">{{ authStore.user?.name }}</p>
            <p class="text-[11px] text-white/35 truncate">{{ userRole }}</p>
          </div>
          <button
            v-if="expanded"
            class="text-white/30 hover:text-white/80 transition-colors p-1.5 rounded-md hover:bg-white/[0.08]"
            title="Logout"
            @click="handleLogout"
          >
            <i class="pi pi-sign-out text-[13px]" />
          </button>
        </div>
      </div>
    </aside>

    <!-- ═══════ Main Content ═══════ -->
    <div class="flex-1 flex flex-col overflow-hidden min-w-0">

      <!-- Header -->
      <header class="h-[68px] bg-white border-b border-slate-200/80 flex items-center justify-between px-5 lg:px-8 shrink-0">
        <div class="flex items-center gap-3">
          <!-- Mobile toggle -->
          <button
            class="lg:hidden text-slate-400 hover:text-slate-600 p-2 -ml-2 rounded-lg hover:bg-slate-100 transition-colors"
            @click="mobileMenuOpen = !mobileMenuOpen"
          >
            <i class="pi pi-bars text-base" />
          </button>
          <!-- Desktop toggle -->
          <button
            class="hidden lg:flex text-slate-300 hover:text-slate-500 p-2 -ml-2 rounded-lg hover:bg-slate-50 transition-colors"
            @click="sidebarOpen = !sidebarOpen"
          >
            <i :class="sidebarOpen ? 'pi pi-chevron-left' : 'pi pi-chevron-right'" class="text-xs" />
          </button>
          <!-- Breadcrumb -->
          <div class="hidden sm:flex items-center gap-2 text-sm">
            <span class="text-slate-400 font-medium">{{ appTitle }}</span>
            <i class="pi pi-chevron-right text-[9px] text-slate-300" />
            <span class="text-slate-700 font-semibold capitalize">{{ pageName }}</span>
          </div>
        </div>

        <div class="flex items-center gap-2">
          <!-- Notifications -->
          <button class="relative text-slate-400 hover:text-slate-600 p-2 rounded-lg hover:bg-slate-50 transition-colors">
            <i class="pi pi-bell text-base" />
            <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-indigo-500 rounded-full ring-2 ring-white" />
          </button>

          <!-- Divider -->
          <div class="hidden sm:block w-px h-8 bg-slate-200 mx-1" />

          <!-- User -->
          <div class="hidden sm:flex items-center gap-3 pl-1">
            <div class="text-right">
              <p class="text-[13px] font-semibold text-slate-700 leading-tight">{{ authStore.user?.name }}</p>
              <p class="text-[11px] text-slate-400 font-medium">{{ userRole }}</p>
            </div>
            <div
              class="w-9 h-9 rounded-full flex items-center justify-center text-[11px] font-bold text-white ring-2 ring-slate-100"
              :style="{ backgroundColor: authStore.isSuperAdmin ? '#1E293B' : tenantColor }"
            >
              {{ userInitials }}
            </div>
          </div>
        </div>
      </header>

      <!-- Page -->
      <main class="flex-1 overflow-auto bg-slate-50">
        <div class="p-5 lg:p-8 max-w-[1440px] mx-auto">
          <router-view />
        </div>
      </main>
    </div>
  </div>
</template>

<style scoped>
.sidebar-scroll::-webkit-scrollbar {
  width: 3px;
}
.sidebar-scroll::-webkit-scrollbar-track {
  background: transparent;
}
.sidebar-scroll::-webkit-scrollbar-thumb {
  background: rgba(255, 255, 255, 0.08);
  border-radius: 3px;
}
</style>
