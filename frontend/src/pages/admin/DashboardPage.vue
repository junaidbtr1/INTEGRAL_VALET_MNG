<script setup lang="ts">
import { computed } from 'vue'
import { useAuthStore } from '@/stores/authStore'

const authStore = useAuthStore()
const tenant = computed(() => authStore.user?.tenant)
const userRole = computed(() => authStore.roles[0]?.replace(/_/g, ' ') ?? 'User')
</script>

<template>
  <div>
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Welcome, {{ authStore.user?.name }}</h1>

    <!-- Tenant Info Card -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
      <div class="flex items-center gap-4">
        <div
          class="w-14 h-14 rounded-xl flex items-center justify-center text-white text-xl font-bold"
          :style="{ backgroundColor: tenant?.primary_color ?? '#3B82F6' }"
        >
          {{ tenant?.name?.charAt(0) ?? 'T' }}
        </div>
        <div>
          <h2 class="text-xl font-semibold text-gray-900">{{ tenant?.name }}</h2>
          <p class="text-sm text-gray-500">{{ tenant?.email }} &middot; Role: <strong>{{ userRole }}</strong></p>
        </div>
        <div class="ml-auto">
          <span
            class="inline-flex px-3 py-1 rounded-full text-sm font-medium"
            :class="{
              'bg-green-100 text-green-700': tenant?.status === 'active',
              'bg-yellow-100 text-yellow-700': tenant?.status === 'trial',
              'bg-red-100 text-red-700': tenant?.status === 'suspended',
            }"
          >
            {{ tenant?.status }}
          </span>
        </div>
      </div>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
      <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-gray-500">Tickets Today</p>
            <p class="text-3xl font-bold text-gray-900 mt-1">0</p>
          </div>
          <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center">
            <i class="pi pi-ticket text-blue-600 text-xl" />
          </div>
        </div>
      </div>

      <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-gray-500">Active Vehicles</p>
            <p class="text-3xl font-bold text-gray-900 mt-1">0</p>
          </div>
          <div class="w-12 h-12 rounded-full bg-green-100 flex items-center justify-center">
            <i class="pi pi-car text-green-600 text-xl" />
          </div>
        </div>
      </div>

      <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-gray-500">Available Slots</p>
            <p class="text-3xl font-bold text-gray-900 mt-1">0</p>
          </div>
          <div class="w-12 h-12 rounded-full bg-purple-100 flex items-center justify-center">
            <i class="pi pi-th-large text-purple-600 text-xl" />
          </div>
        </div>
      </div>

      <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-gray-500">Revenue Today</p>
            <p class="text-3xl font-bold text-gray-900 mt-1">$0.00</p>
          </div>
          <div class="w-12 h-12 rounded-full bg-amber-100 flex items-center justify-center">
            <i class="pi pi-wallet text-amber-600 text-xl" />
          </div>
        </div>
      </div>
    </div>

    <!-- Coming Soon -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-12 text-center">
      <i class="pi pi-wrench text-5xl text-gray-300 mb-4" />
      <h3 class="text-lg font-semibold text-gray-700">Tenant Dashboard Coming Soon</h3>
      <p class="text-sm text-gray-500 mt-2">
        Ticket management, parking slots, payments, and reports will be available here.
      </p>
    </div>
  </div>
</template>
