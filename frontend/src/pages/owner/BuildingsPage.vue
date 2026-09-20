<script setup lang="ts">
import { computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/authStore'
import type { ITenant } from '@/types'

const authStore = useAuthStore()
const router = useRouter()

const buildings = computed(() => authStore.ownedBuildings)

function usagePercent(used: number | undefined, max: number | null | undefined): number {
  if (!max || max <= 0) return 0
  return Math.min(100, Math.round(((used ?? 0) / max) * 100))
}

function usageColor(percent: number): string {
  if (percent >= 90) return 'bg-red-500'
  if (percent >= 70) return 'bg-yellow-500'
  return 'bg-green-500'
}

function manage(building: ITenant) {
  authStore.switchBuilding(building.id)
  router.push({ name: 'app.reports' })
}

function statusBadgeClass(status: string): string {
  const map: Record<string, string> = {
    active: 'bg-green-100 text-green-700',
    trial: 'bg-blue-100 text-blue-700',
    suspended: 'bg-red-100 text-red-700',
    cancelled: 'bg-gray-100 text-gray-600',
  }
  return map[status] ?? 'bg-gray-100 text-gray-600'
}
</script>

<template>
  <div class="p-6">
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-gray-900">My Buildings</h1>
      <p class="text-sm text-gray-500 mt-1">Select a building to manage its dashboard</p>
    </div>

    <!-- Empty state -->
    <div
      v-if="buildings.length === 0"
      class="flex flex-col items-center justify-center py-20 text-center"
    >
      <div class="w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center mb-4">
        <i class="pi pi-building text-2xl text-gray-400" />
      </div>
      <h3 class="text-lg font-semibold text-gray-700 mb-1">No buildings assigned yet</h3>
      <p class="text-sm text-gray-500">Contact your administrator to get buildings assigned to your account.</p>
    </div>

    <!-- Buildings grid -->
    <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
      <div
        v-for="building in buildings"
        :key="building.id"
        class="bg-white rounded-xl shadow-sm overflow-hidden transition-all duration-150 hover:shadow-md"
        :class="authStore.activeTenantId === building.id
          ? 'border-2 border-indigo-500 ring-2 ring-indigo-100'
          : 'border border-gray-200'"
      >
        <!-- Card header -->
        <div class="px-5 pt-5 pb-4 border-b border-gray-100">
          <div class="flex items-start justify-between gap-2">
            <div class="min-w-0">
              <h2 class="text-base font-semibold text-gray-900 truncate">{{ building.name }}</h2>
              <p class="text-xs text-gray-400 truncate mt-0.5">{{ building.plan?.name ?? 'No plan' }}</p>
            </div>
            <span
              class="shrink-0 text-xs font-medium px-2 py-0.5 rounded-full capitalize"
              :class="statusBadgeClass(building.status)"
            >
              {{ building.status }}
            </span>
          </div>
        </div>

        <!-- Plan usage bars -->
        <div class="px-5 py-4 space-y-3">
          <!-- Slots -->
          <div v-if="building.plan?.max_slots">
            <div class="flex justify-between text-xs text-gray-500 mb-1">
              <span>Parking Slots</span>
              <span>{{ building.parking_slots_count ?? 0 }} / {{ building.plan.max_slots }}</span>
            </div>
            <div class="w-full bg-gray-100 rounded-full h-1.5">
              <div
                class="h-1.5 rounded-full transition-all"
                :class="usageColor(usagePercent(building.parking_slots_count, building.plan.max_slots))"
                :style="{ width: usagePercent(building.parking_slots_count, building.plan.max_slots) + '%' }"
              />
            </div>
          </div>

          <!-- Staff -->
          <div v-if="building.plan?.max_staff_users">
            <div class="flex justify-between text-xs text-gray-500 mb-1">
              <span>Staff Users</span>
              <span>{{ building.users_count ?? 0 }} / {{ building.plan.max_staff_users }}</span>
            </div>
            <div class="w-full bg-gray-100 rounded-full h-1.5">
              <div
                class="h-1.5 rounded-full transition-all"
                :class="usageColor(usagePercent(building.users_count, building.plan.max_staff_users))"
                :style="{ width: usagePercent(building.users_count, building.plan.max_staff_users) + '%' }"
              />
            </div>
          </div>

          <!-- Tickets per day -->
          <div v-if="building.plan?.max_tickets_per_day">
            <div class="flex justify-between text-xs text-gray-500 mb-1">
              <span>Daily Ticket Limit</span>
              <span>{{ building.plan.max_tickets_per_day }} / day</span>
            </div>
            <div class="w-full bg-gray-100 rounded-full h-1.5">
              <div class="h-1.5 rounded-full bg-blue-400" style="width: 0%" />
            </div>
          </div>
        </div>

        <!-- Manage button -->
        <div class="px-5 pb-5">
          <button
            class="w-full text-sm font-medium py-2 rounded-lg text-center transition-colors"
            :class="authStore.activeTenantId === building.id
              ? 'bg-indigo-600 hover:bg-indigo-700 text-white'
              : 'bg-indigo-50 hover:bg-indigo-100 text-indigo-700'"
            @click="manage(building)"
          >
            <span v-if="authStore.activeTenantId === building.id">
              <i class="pi pi-check mr-1.5" />Managing Now — View Reports
            </span>
            <span v-else>
              <i class="pi pi-sign-in mr-1.5" />Manage This Building
            </span>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
