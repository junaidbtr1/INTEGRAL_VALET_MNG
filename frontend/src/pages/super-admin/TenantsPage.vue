<script setup lang="ts">
import { ref, onMounted, watch } from 'vue'
import { debounce } from 'lodash-es'
import AppTable from '@/components/ui/AppTable.vue'
import AppConfirmDialog from '@/components/ui/AppConfirmDialog.vue'
import { adminService } from '@/services/adminService'
import { useToast } from 'primevue/usetoast'
import { useForm, useField } from 'vee-validate'
import * as yup from 'yup'
import { isAxiosError } from 'axios'
import type { ITenant, IPlan, IUser } from '@/types'

const toast = useToast()
const tenants = ref<ITenant[]>([])
const plans = ref<IPlan[]>([])
const buildingOwners = ref<IUser[]>([])
const isLoading = ref(true)
const search = ref('')
const statusFilter = ref('')
const showCreateDialog = ref(false)
const showDeleteDialog = ref(false)
const pendingDeleteId = ref<number | null>(null)
const meta = ref<{ current_page: number; last_page: number; total: number } | null>(null)
const currentPage = ref(1)

// Form
const createSchema = yup.object({
  name: yup.string().required('Tenant name is required').max(255),
  phone: yup.string().nullable(),
  address: yup.string().nullable(),
  plan_id: yup.number().nullable(),
  owner_id: yup.number().nullable(),
  trial_days: yup.number().min(1).max(90).default(14),
})

const { handleSubmit, setErrors, isSubmitting, resetForm } = useForm({ validationSchema: createSchema })
const { value: name, errorMessage: nameError } = useField('name')
const { value: phone, errorMessage: phoneError } = useField('phone')
const { value: address, errorMessage: addressError } = useField('address')
const { value: plan_id } = useField('plan_id')
const { value: owner_id } = useField('owner_id')
const { value: trial_days } = useField('trial_days')

async function fetchTenants(page = 1) {
  isLoading.value = true
  try {
    const response = await adminService.getTenants({
      page,
      per_page: 15,
      search: search.value || undefined,
      status: statusFilter.value || undefined,
      sort: '-created_at',
    })
    tenants.value = response.data
    meta.value = response.meta as typeof meta.value
    currentPage.value = page
  } finally {
    isLoading.value = false
  }
}

async function fetchPlans() {
  const response = await adminService.getPlans()
  plans.value = response.data
}

async function fetchBuildingOwners() {
  const response = await adminService.getUsers({ is_building_owner: true, per_page: 100 })
  buildingOwners.value = response.data
}

const debouncedSearch = debounce(() => fetchTenants(1), 300)
watch(search, () => debouncedSearch())
watch(statusFilter, () => fetchTenants(1))

const onCreateSubmit = handleSubmit(async (values) => {
  try {
    await adminService.createTenant(values as Parameters<typeof adminService.createTenant>[0])
    toast.add({ severity: 'success', summary: 'Success', detail: 'Tenant created successfully', life: 3000 })
    showCreateDialog.value = false
    resetForm()
    await Promise.all([fetchTenants(1), fetchBuildingOwners()])
  } catch (error) {
    if (isAxiosError(error) && error.response?.status === 422) {
      setErrors(error.response.data.errors ?? {})
    } else {
      toast.add({ severity: 'error', summary: 'Error', detail: 'Failed to create tenant', life: 5000 })
    }
  }
})

function openDeleteDialog(id: number) {
  pendingDeleteId.value = id
  showDeleteDialog.value = true
}

async function confirmDeleteTenant() {
  if (!pendingDeleteId.value) return
  try {
    await adminService.deleteTenant(pendingDeleteId.value)
    toast.add({ severity: 'success', summary: 'Deleted', detail: 'Tenant deleted', life: 3000 })
    await fetchTenants(currentPage.value)
  } catch {
    toast.add({ severity: 'error', summary: 'Error', detail: 'Failed to delete tenant', life: 5000 })
  } finally {
    pendingDeleteId.value = null
  }
}

onMounted(() => {
  fetchTenants()
  fetchPlans()
  fetchBuildingOwners()
})
</script>

<template>
  <div>
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
      <h1 class="text-2xl font-bold text-gray-900">Tenants</h1>
      <button
        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium flex items-center gap-2"
        @click="showCreateDialog = true"
      >
        <i class="pi pi-plus" />
        Add Tenant
      </button>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6 flex gap-4">
      <div class="flex-1">
        <input
          v-model="search"
          type="text"
          placeholder="Search tenants..."
          class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
        />
      </div>
      <select
        v-model="statusFilter"
        class="border border-gray-300 rounded-lg px-4 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none"
      >
        <option value="">All Statuses</option>
        <option value="trial">Trial</option>
        <option value="active">Active</option>
        <option value="suspended">Suspended</option>
        <option value="cancelled">Cancelled</option>
      </select>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
      <div v-if="isLoading" class="p-12 text-center">
        <i class="pi pi-spinner pi-spin text-3xl text-gray-400" />
      </div>
      <div v-else-if="tenants.length === 0" class="p-12 text-center">
        <i class="pi pi-building text-4xl text-gray-300 mb-3" />
        <p class="text-gray-500">No tenants found</p>
      </div>
      <AppTable v-else min-width="min-w-[540px]">
        <thead class="bg-gray-50 border-b border-gray-100">
          <tr>
            <th class="text-left text-xs font-medium text-gray-500 uppercase px-6 py-3">Tenant</th>
            <th class="hidden sm:table-cell text-left text-xs font-medium text-gray-500 uppercase px-6 py-3">Plan</th>
            <th class="hidden lg:table-cell text-left text-xs font-medium text-gray-500 uppercase px-6 py-3">Owner</th>
            <th class="hidden md:table-cell text-left text-xs font-medium text-gray-500 uppercase px-6 py-3">Users</th>
            <th class="hidden md:table-cell text-left text-xs font-medium text-gray-500 uppercase px-6 py-3">Tickets</th>
            <th class="hidden lg:table-cell text-left text-xs font-medium text-gray-500 uppercase px-6 py-3">Created</th>
            <th class="text-right text-xs font-medium text-gray-500 uppercase px-6 py-3">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <tr v-for="tenant in tenants" :key="tenant.id" class="hover:bg-gray-50">
            <td class="px-6 py-4">
              <div class="flex items-center gap-3">
                <div>
                  <p class="font-medium text-gray-900">{{ tenant.name }}</p>
                  <p v-if="tenant.email" class="text-sm text-gray-500">{{ tenant.email }}</p>
                </div>
                <span
                  class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium shrink-0"
                  :class="{
                    'bg-green-100 text-green-700': tenant.status === 'active',
                    'bg-yellow-100 text-yellow-700': tenant.status === 'trial',
                    'bg-red-100 text-red-700': tenant.status === 'suspended',
                    'bg-gray-100 text-gray-700': tenant.status === 'cancelled',
                  }"
                >
                  {{ tenant.status }}
                </span>
              </div>
            </td>
            <td class="hidden sm:table-cell px-6 py-4 text-sm text-gray-600">{{ tenant.plan?.name ?? 'No plan' }}</td>
            <td class="hidden lg:table-cell px-6 py-4 text-sm text-gray-500">
              {{ tenant.owner ? tenant.owner.name : (tenant.owner_id ? `#${tenant.owner_id}` : '—') }}
            </td>
            <td class="hidden md:table-cell px-6 py-4 text-sm text-gray-600">{{ tenant.users_count ?? 0 }}</td>
            <td class="hidden md:table-cell px-6 py-4 text-sm text-gray-600">{{ tenant.tickets_count ?? 0 }}</td>
            <td class="hidden lg:table-cell px-6 py-4 text-sm text-gray-500">
              {{ new Date(tenant.created_at).toLocaleDateString() }}
            </td>
            <td class="px-6 py-4 text-right">
              <div class="flex items-center justify-end gap-2">
                <router-link
                  :to="{ name: 'app.tenants.show', params: { id: tenant.id } }"
                  class="text-blue-600 hover:text-blue-700 text-sm"
                >
                  <i class="pi pi-eye" />
                </router-link>
                <button class="text-red-600 hover:text-red-700 text-sm" @click="openDeleteDialog(tenant.id)">
                  <i class="pi pi-trash" />
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </AppTable>

      <!-- Pagination -->
      <div v-if="meta && meta.last_page > 1" class="flex items-center justify-between px-6 py-4 border-t border-gray-100">
        <p class="text-sm text-gray-500">
          Showing {{ tenants.length }} of {{ meta.total }} tenants
        </p>
        <div class="flex gap-2">
          <button
            class="px-3 py-1.5 text-sm border rounded-lg disabled:opacity-50"
            :disabled="currentPage === 1"
            @click="fetchTenants(currentPage - 1)"
          >
            Previous
          </button>
          <button
            class="px-3 py-1.5 text-sm border rounded-lg disabled:opacity-50"
            :disabled="currentPage === meta.last_page"
            @click="fetchTenants(currentPage + 1)"
          >
            Next
          </button>
        </div>
      </div>
    </div>

    <!-- Create Dialog -->
    <div v-if="showCreateDialog" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
      <div class="bg-white rounded-2xl w-full max-w-lg mx-4 max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b border-gray-100 flex items-center justify-between">
          <h2 class="text-lg font-semibold">Create New Tenant</h2>
          <button class="text-gray-400 hover:text-gray-600" @click="showCreateDialog = false">
            <i class="pi pi-times" />
          </button>
        </div>
        <form class="p-6 space-y-4" @submit.prevent="onCreateSubmit" novalidate>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Name <span class="text-red-500">*</span></label>
            <input v-model="name" type="text" class="w-full border rounded-lg px-4 py-2 text-sm" :class="nameError ? 'border-red-500' : 'border-gray-300'" />
            <p v-if="nameError" class="mt-1 text-sm text-red-500">{{ nameError }}</p>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
            <input v-model="phone" type="text" class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm" />
            <p v-if="phoneError" class="mt-1 text-sm text-red-500">{{ phoneError }}</p>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
            <textarea v-model="address" class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm" rows="2" />
            <p v-if="addressError" class="mt-1 text-sm text-red-500">{{ addressError }}</p>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Plan</label>
            <select v-model="plan_id" class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm">
              <option :value="null">No plan</option>
              <option v-for="plan in plans" :key="plan.id" :value="plan.id">{{ plan.name }}</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Owner</label>
            <select v-model="owner_id" class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm">
              <option :value="null">No owner</option>
              <option v-for="bo in buildingOwners" :key="bo.id" :value="bo.id">
                {{ bo.name }} ({{ bo.email }}){{ bo.owned_buildings?.length ? ` — ${bo.owned_buildings.length} building${bo.owned_buildings.length > 1 ? 's' : ''}` : '' }}
              </option>
            </select>
            <p class="mt-1 text-xs text-gray-400">All building owner users — one owner can manage multiple buildings</p>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Trial Days</label>
            <input v-model="trial_days" type="number" class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm" placeholder="14" />
          </div>
          <div class="flex justify-end gap-3 pt-4">
            <button type="button" class="px-4 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50" @click="showCreateDialog = false">
              Cancel
            </button>
            <button type="submit" :disabled="isSubmitting" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 flex items-center gap-2">
              <i v-if="isSubmitting" class="pi pi-spinner pi-spin" />
              Create Tenant
            </button>
          </div>
        </form>
      </div>
    </div>

    <AppConfirmDialog
      v-model="showDeleteDialog"
      title="Delete Tenant"
      message="Are you sure you want to delete this tenant? All data will be permanently removed."
      confirm-label="Delete Tenant"
      :danger="true"
      @confirm="confirmDeleteTenant"
    />
  </div>
</template>
