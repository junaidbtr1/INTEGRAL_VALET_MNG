<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { adminService } from '@/services/adminService'
import type { IDashboardStats, ITenant } from '@/types'

const stats = ref<IDashboardStats | null>(null)
const recentTenants = ref<ITenant[]>([])
const isLoading = ref(true)

onMounted(async () => {
  try {
    const response = await adminService.getDashboard()
    stats.value = response.data.stats
    recentTenants.value = response.data.recent_tenants
  } finally {
    isLoading.value = false
  }
})


</script>

<template>
  <div>
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Dashboard</h1>

    <!-- Loading -->
    <div v-if="isLoading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
      <div v-for="i in 4" :key="i" class="bg-white rounded-xl p-6 shadow-sm animate-pulse">
        <div class="h-4 bg-gray-200 rounded w-24 mb-3" />
        <div class="h-8 bg-gray-200 rounded w-16" />
      </div>
    </div>

    <!-- Stats -->
    <div v-else-if="stats" class="space-y-6">
      <!-- Stat Cards -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-gray-500">Total Tenants</p>
              <p class="text-3xl font-bold text-gray-900 mt-1">{{ stats.total_tenants }}</p>
            </div>
            <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center">
              <i class="pi pi-building text-blue-600 text-xl" />
            </div>
          </div>
          <div class="mt-3 flex gap-3 text-xs">
            <span class="text-green-600">{{ stats.active_tenants }} active</span>
            <span class="text-yellow-600">{{ stats.trial_tenants }} trial</span>
            <span class="text-red-600">{{ stats.suspended_tenants }} suspended</span>
          </div>
        </div>

        <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-gray-500">Tenant Admins</p>
              <p class="text-3xl font-bold text-gray-900 mt-1">{{ stats.total_users }}</p>
            </div>
            <div class="w-12 h-12 rounded-full bg-green-100 flex items-center justify-center">
              <i class="pi pi-users text-green-600 text-xl" />
            </div>
          </div>
          <p class="mt-3 text-xs text-gray-500">Across all tenants</p>
        </div>

        <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-gray-500">Active Plans</p>
              <p class="text-3xl font-bold text-gray-900 mt-1">{{ stats.total_plans }}</p>
            </div>
            <div class="w-12 h-12 rounded-full bg-purple-100 flex items-center justify-center">
              <i class="pi pi-tags text-purple-600 text-xl" />
            </div>
          </div>
          <p class="mt-3 text-xs text-gray-500">Available subscription plans</p>
        </div>

        <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-gray-500">New This Month</p>
              <p class="text-3xl font-bold text-gray-900 mt-1">{{ stats.new_tenants_this_month }}</p>
            </div>
            <div class="w-12 h-12 rounded-full bg-amber-100 flex items-center justify-center">
              <i class="pi pi-chart-line text-amber-600 text-xl" />
            </div>
          </div>
          <p class="mt-3 text-xs text-gray-500">Tenants joined this month</p>
        </div>
      </div>

      <!-- Recent Tenants -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <div class="p-6 border-b border-gray-100">
          <div class="flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-900">Recent Tenants</h2>
            <router-link
              :to="{ name: 'app.tenants' }"
              class="text-sm text-blue-600 hover:text-blue-700"
            >
              View all
            </router-link>
          </div>
        </div>
        <div v-if="recentTenants.length === 0" class="p-12 text-center">
          <i class="pi pi-building text-4xl text-gray-300 mb-3" />
          <p class="text-gray-500">No tenants yet</p>
          <router-link
            :to="{ name: 'app.tenants' }"
            class="inline-block mt-3 text-sm text-blue-600 hover:text-blue-700"
          >
            Create your first tenant
          </router-link>
        </div>
        <div v-else class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-gray-50">
              <tr>
                <th class="text-left text-xs font-medium text-gray-500 uppercase px-6 py-3">Name</th>
                <th class="text-left text-xs font-medium text-gray-500 uppercase px-6 py-3">Status</th>
                <th class="text-left text-xs font-medium text-gray-500 uppercase px-6 py-3">Users</th>
                <th class="text-left text-xs font-medium text-gray-500 uppercase px-6 py-3">Tickets</th>
                <th class="text-left text-xs font-medium text-gray-500 uppercase px-6 py-3">Created</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <tr v-for="tenant in recentTenants" :key="tenant.id" class="hover:bg-gray-50">
                <td class="px-6 py-4">
                  <div>
                    <p class="font-medium text-gray-900">{{ tenant.name }}</p>
                    <p v-if="tenant.email" class="text-sm text-gray-500">{{ tenant.email }}</p>
                  </div>
                </td>
                <td class="px-6 py-4">
                  <span
                    class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium"
                    :class="{
                      'bg-green-100 text-green-700': tenant.status === 'active',
                      'bg-yellow-100 text-yellow-700': tenant.status === 'trial',
                      'bg-red-100 text-red-700': tenant.status === 'suspended',
                    }"
                  >
                    {{ tenant.status }}
                  </span>
                </td>
                <td class="px-6 py-4 text-sm text-gray-600">{{ tenant.users_count ?? 0 }}</td>
                <td class="px-6 py-4 text-sm text-gray-600">{{ tenant.tickets_count ?? 0 }}</td>
                <td class="px-6 py-4 text-sm text-gray-500">
                  {{ new Date(tenant.created_at).toLocaleDateString() }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>
