<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { adminService } from '@/services/adminService'
import type { ITenant } from '@/types'

const route = useRoute()
const tenant = ref<ITenant | null>(null)
const isLoading = ref(true)

onMounted(async () => {
  try {
    const id = Number(route.params.id)
    const response = await adminService.getTenant(id)
    tenant.value = response.data
  } finally {
    isLoading.value = false
  }
})
</script>

<template>
  <div>
    <div v-if="isLoading" class="flex items-center justify-center py-20">
      <i class="pi pi-spinner pi-spin text-3xl text-gray-400" />
    </div>
    <div v-else-if="tenant">
      <div class="flex items-center gap-4 mb-6">
        <router-link :to="{ name: 'app.tenants' }" class="text-gray-500 hover:text-gray-700">
          <i class="pi pi-arrow-left" />
        </router-link>
        <h1 class="text-2xl font-bold text-gray-900">{{ tenant.name }}</h1>
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
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Info -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
          <h2 class="text-lg font-semibold mb-4">Tenant Information</h2>
          <dl class="space-y-3">
            <div class="flex justify-between">
              <dt class="text-sm text-gray-500">Email</dt>
              <dd class="text-sm font-medium">{{ tenant.email }}</dd>
            </div>
            <div class="flex justify-between">
              <dt class="text-sm text-gray-500">Phone</dt>
              <dd class="text-sm font-medium">{{ tenant.phone ?? '-' }}</dd>
            </div>
            <div class="flex justify-between">
              <dt class="text-sm text-gray-500">Plan</dt>
              <dd class="text-sm font-medium">{{ tenant.plan?.name ?? 'No plan' }}</dd>
            </div>
            <div class="flex justify-between">
              <dt class="text-sm text-gray-500">Users</dt>
              <dd class="text-sm font-medium">{{ tenant.users_count ?? 0 }}</dd>
            </div>
            <div class="flex justify-between">
              <dt class="text-sm text-gray-500">Tickets</dt>
              <dd class="text-sm font-medium">{{ tenant.tickets_count ?? 0 }}</dd>
            </div>
            <div class="flex justify-between">
              <dt class="text-sm text-gray-500">Parking Slots</dt>
              <dd class="text-sm font-medium">{{ tenant.parking_slots_count ?? 0 }}</dd>
            </div>
            <div class="flex justify-between">
              <dt class="text-sm text-gray-500">Created</dt>
              <dd class="text-sm font-medium">{{ new Date(tenant.created_at).toLocaleString() }}</dd>
            </div>
          </dl>
        </div>

        <!-- Branding -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
          <h2 class="text-lg font-semibold mb-4">Branding</h2>
          <div class="space-y-3">
            <div class="flex items-center gap-3">
              <span class="text-sm text-gray-500 w-32">Primary Color</span>
              <div class="w-8 h-8 rounded border" :style="{ backgroundColor: tenant.primary_color }" />
              <span class="text-sm font-mono">{{ tenant.primary_color }}</span>
            </div>
            <div class="flex items-center gap-3">
              <span class="text-sm text-gray-500 w-32">Secondary Color</span>
              <div class="w-8 h-8 rounded border" :style="{ backgroundColor: tenant.secondary_color }" />
              <span class="text-sm font-mono">{{ tenant.secondary_color }}</span>
            </div>
            <div class="flex items-center gap-3">
              <span class="text-sm text-gray-500 w-32">Accent Color</span>
              <div class="w-8 h-8 rounded border" :style="{ backgroundColor: tenant.accent_color }" />
              <span class="text-sm font-mono">{{ tenant.accent_color }}</span>
            </div>
          </div>
        </div>

        <!-- Features -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 lg:col-span-2">
          <h2 class="text-lg font-semibold mb-4">Features</h2>
          <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
            <div
              v-for="(enabled, feature) in tenant.features ?? {}"
              :key="String(feature)"
              class="flex items-center gap-2 py-2"
            >
              <i
                :class="enabled ? 'pi pi-check-circle text-green-500' : 'pi pi-times-circle text-gray-300'"
              />
              <span class="text-sm" :class="enabled ? 'text-gray-900' : 'text-gray-400'">
                {{ String(feature).replace(/_/g, ' ') }}
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
