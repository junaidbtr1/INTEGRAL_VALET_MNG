<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useAuthStore } from '@/stores/authStore'
import { adminService } from '@/services/adminService'
import { tenantService } from '@/services/tenantService'
import type { IDashboardStats, ITenant } from '@/types'
import type { ITenantDashboard, ITicket } from '@/services/tenantService'

const authStore = useAuthStore()
const isLoading = ref(true)
const isSuperAdmin = computed(() => authStore.isSuperAdmin)
const tenant = computed(() => authStore.user?.tenant)
const userRole = computed(() => {
  if (authStore.isSuperAdmin) return 'Super Admin'
  const r = authStore.roles[0]
  return r ? r.replace(/_/g, ' ').replace(/\b\w/g, (c: string) => c.toUpperCase()) : 'User'
})

// Super admin
const adminStats = ref<IDashboardStats | null>(null)
const recentTenants = ref<ITenant[]>([])

// Tenant
const tenantDash = ref<ITenantDashboard | null>(null)

onMounted(async () => {
  try {
    if (isSuperAdmin.value) {
      const response = await adminService.getDashboard()
      adminStats.value = response.data.stats
      recentTenants.value = response.data.recent_tenants
    } else {
      const response = await tenantService.getDashboard()
      tenantDash.value = response.data
    }
  } finally {
    isLoading.value = false
  }
})

function formatCurrency(amount: number): string {
  return `$${(amount / 100).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`
}

function formatDate(date: string): string {
  return new Date(date).toLocaleString('en-US', { timeStyle: 'short' })
}

function getStatusClass(status: string): string {
  return { active: 'bg-emerald-50 text-emerald-700', created: 'bg-blue-50 text-blue-700', completed: 'bg-indigo-50 text-indigo-700', closed: 'bg-slate-100 text-slate-600', cancelled: 'bg-red-50 text-red-700' }[status] ?? 'bg-slate-100 text-slate-600'
}
</script>

<template>
  <div>
    <h1 class="text-2xl font-bold text-slate-900 mb-6">
      {{ isSuperAdmin ? 'System Dashboard' : `Welcome, ${authStore.user?.name}` }}
    </h1>

    <!-- Loading -->
    <div v-if="isLoading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
      <div v-for="i in 4" :key="i" class="bg-white rounded-xl border border-slate-200 p-5 animate-pulse">
        <div class="h-3 bg-slate-100 rounded w-20 mb-3" />
        <div class="h-7 bg-slate-100 rounded w-14" />
      </div>
    </div>

    <!-- ═══ SUPER ADMIN ═══ -->
    <template v-else-if="isSuperAdmin && adminStats">
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
        <div class="bg-white rounded-xl border border-slate-200 p-5">
          <div class="flex items-center justify-between">
            <div><p class="text-xs text-slate-400 font-medium uppercase tracking-wide">Tenants</p><p class="text-3xl font-bold text-slate-900 mt-1">{{ adminStats.total_tenants }}</p></div>
            <div class="w-11 h-11 rounded-lg bg-indigo-50 flex items-center justify-center"><i class="pi pi-building text-indigo-600" /></div>
          </div>
          <div class="mt-3 flex gap-3 text-xs"><span class="text-emerald-600">{{ adminStats.active_tenants }} active</span><span class="text-blue-600">{{ adminStats.trial_tenants }} trial</span></div>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-5">
          <div class="flex items-center justify-between">
            <div><p class="text-xs text-slate-400 font-medium uppercase tracking-wide">Users</p><p class="text-3xl font-bold text-slate-900 mt-1">{{ adminStats.total_users }}</p></div>
            <div class="w-11 h-11 rounded-lg bg-emerald-50 flex items-center justify-center"><i class="pi pi-users text-emerald-600" /></div>
          </div>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-5">
          <div class="flex items-center justify-between">
            <div><p class="text-xs text-slate-400 font-medium uppercase tracking-wide">Tickets Today</p><p class="text-3xl font-bold text-slate-900 mt-1">{{ adminStats.total_tickets_today }}</p></div>
            <div class="w-11 h-11 rounded-lg bg-purple-50 flex items-center justify-center"><i class="pi pi-ticket text-purple-600" /></div>
          </div>
          <p class="mt-3 text-xs text-slate-400">{{ adminStats.total_tickets_month }} this month</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-5">
          <div class="flex items-center justify-between">
            <div><p class="text-xs text-slate-400 font-medium uppercase tracking-wide">Revenue Today</p><p class="text-3xl font-bold text-slate-900 mt-1">{{ formatCurrency(adminStats.total_revenue_today) }}</p></div>
            <div class="w-11 h-11 rounded-lg bg-amber-50 flex items-center justify-center"><i class="pi pi-wallet text-amber-600" /></div>
          </div>
          <p class="mt-3 text-xs text-slate-400">{{ formatCurrency(adminStats.total_revenue_month) }} this month</p>
        </div>
      </div>

      <div class="bg-white rounded-xl border border-slate-200">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
          <h2 class="text-base font-semibold text-slate-900">Recent Tenants</h2>
          <router-link :to="{ name: 'app.tenants' }" class="text-sm text-indigo-600 hover:text-indigo-700 font-medium">View all</router-link>
        </div>
        <div v-if="recentTenants.length === 0" class="p-12 text-center"><p class="text-slate-400">No tenants yet</p></div>
        <table v-else class="w-full">
          <thead><tr class="border-b border-slate-100"><th class="text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider px-5 py-3">Name</th><th class="text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider px-5 py-3">Status</th><th class="text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider px-5 py-3">Users</th><th class="text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider px-5 py-3">Created</th></tr></thead>
          <tbody class="divide-y divide-slate-100">
            <tr v-for="t in recentTenants" :key="t.id" class="hover:bg-slate-50/50">
              <td class="px-5 py-3"><p class="text-sm font-medium text-slate-800">{{ t.name }}</p><p class="text-xs text-slate-400">{{ t.email }}</p></td>
              <td class="px-5 py-3"><span class="inline-flex px-2 py-0.5 rounded text-[10px] font-semibold" :class="{ 'bg-emerald-50 text-emerald-700': t.status === 'active', 'bg-blue-50 text-blue-700': t.status === 'trial', 'bg-red-50 text-red-700': t.status === 'suspended' }">{{ t.status }}</span></td>
              <td class="px-5 py-3 text-sm text-slate-500">{{ t.users_count ?? 0 }}</td>
              <td class="px-5 py-3 text-sm text-slate-400">{{ new Date(t.created_at).toLocaleDateString() }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </template>

    <!-- ═══ TENANT DASHBOARD ═══ -->
    <template v-else-if="tenantDash">
      <!-- Tenant Header -->
      <div class="bg-white rounded-xl border border-slate-200 p-5 mb-6 flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl flex items-center justify-center text-white text-lg font-bold" :style="{ backgroundColor: tenant?.primary_color ?? '#6366F1' }">{{ tenant?.name?.charAt(0) ?? 'T' }}</div>
        <div class="flex-1"><h2 class="text-lg font-semibold text-slate-900">{{ tenant?.name }}</h2><p class="text-sm text-slate-500">{{ userRole }}</p></div>
        <span class="inline-flex px-3 py-1 rounded-md text-xs font-semibold" :class="{ 'bg-emerald-50 text-emerald-700': tenant?.status === 'active', 'bg-blue-50 text-blue-700': tenant?.status === 'trial' }">{{ tenant?.status }}</span>
      </div>

      <!-- Stats -->
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
        <div class="bg-white rounded-xl border border-slate-200 p-5">
          <p class="text-xs text-slate-400 font-medium uppercase tracking-wide">Tickets Today</p>
          <p class="text-3xl font-bold text-slate-900 mt-1">{{ tenantDash.stats.tickets_today }}</p>
          <p class="text-xs text-slate-400 mt-2">{{ tenantDash.stats.tickets_month }} this month</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-5">
          <p class="text-xs text-slate-400 font-medium uppercase tracking-wide">Active Vehicles</p>
          <p class="text-3xl font-bold text-emerald-600 mt-1">{{ tenantDash.stats.active_vehicles }}</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-5">
          <p class="text-xs text-slate-400 font-medium uppercase tracking-wide">Occupancy</p>
          <p class="text-3xl font-bold text-slate-900 mt-1">{{ tenantDash.stats.occupancy_percent }}%</p>
          <div class="mt-2 h-1.5 bg-slate-100 rounded-full overflow-hidden"><div class="h-full rounded-full transition-all" :class="tenantDash.stats.occupancy_percent > 90 ? 'bg-red-500' : tenantDash.stats.occupancy_percent > 70 ? 'bg-amber-500' : 'bg-emerald-500'" :style="{ width: tenantDash.stats.occupancy_percent + '%' }" /></div>
          <p class="text-xs text-slate-400 mt-1">{{ tenantDash.stats.available_slots }} / {{ tenantDash.stats.total_slots }} available</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-5">
          <p class="text-xs text-slate-400 font-medium uppercase tracking-wide">Revenue Today</p>
          <p class="text-3xl font-bold text-slate-900 mt-1">{{ formatCurrency(tenantDash.stats.revenue_today) }}</p>
          <p class="text-xs text-slate-400 mt-2">{{ formatCurrency(tenantDash.stats.revenue_month) }} this month</p>
        </div>
      </div>

      <!-- Recent Tickets -->
      <div class="bg-white rounded-xl border border-slate-200">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
          <h2 class="text-base font-semibold text-slate-900">Recent Tickets</h2>
          <router-link :to="{ name: 'app.tickets' }" class="text-sm text-indigo-600 hover:text-indigo-700 font-medium">View all</router-link>
        </div>
        <div v-if="tenantDash.recent_tickets.length === 0" class="p-12 text-center"><p class="text-slate-400">No tickets yet</p></div>
        <table v-else class="w-full">
          <thead><tr class="border-b border-slate-100"><th class="text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider px-5 py-3">Ticket</th><th class="text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider px-5 py-3">Vehicle</th><th class="text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider px-5 py-3">Status</th><th class="text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider px-5 py-3">Entry</th></tr></thead>
          <tbody class="divide-y divide-slate-100">
            <tr v-for="t in tenantDash.recent_tickets" :key="t.id" class="hover:bg-slate-50/50">
              <td class="px-5 py-3 text-sm font-semibold text-slate-800">{{ t.ticket_number }}</td>
              <td class="px-5 py-3"><p class="text-sm text-slate-700">{{ t.vehicle_plate }}</p><p class="text-xs text-slate-400 capitalize">{{ t.vehicle_type }}</p></td>
              <td class="px-5 py-3"><span class="inline-flex px-2 py-0.5 rounded text-[10px] font-semibold capitalize" :class="getStatusClass(t.status)">{{ t.status }}</span></td>
              <td class="px-5 py-3 text-sm text-slate-400">{{ formatDate(t.entry_at) }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </template>
  </div>
</template>
