<script setup lang="ts">
import { ref, onMounted, watch } from 'vue'
import { debounce } from 'lodash-es'
import AppTable from '@/components/ui/AppTable.vue'
import AppConfirmDialog from '@/components/ui/AppConfirmDialog.vue'
import BuildingSelector from '@/components/ui/BuildingSelector.vue'
import { tenantService } from '@/services/tenantService'
import type { IRole, IShift } from '@/services/tenantService'
import type { IUser } from '@/types'
import { useToast } from 'primevue/usetoast'
import { isAxiosError } from 'axios'
import { useBuildingGuard } from '@/composables/useBuildingGuard'
import { usePermission } from '@/composables/usePermission'

const { validateBuilding, isBuildingOwner, activeTenantId } = useBuildingGuard()
const { can } = usePermission()
const toast = useToast()
const staff = ref<IUser[]>([])
const roles = ref<IRole[]>([])
const shifts = ref<IShift[]>([])
const isLoading = ref(true)
const search = ref('')
const roleFilter = ref('')
const meta = ref<{ current_page: number; last_page: number; total: number } | null>(null)
const currentPage = ref(1)

// Dialog
const showDialog = ref(false)
const isEditing = ref(false)
const editingUser = ref<IUser | null>(null)
const showDeleteDialog = ref(false)
const pendingDeleteUser = ref<IUser | null>(null)
const isSaving = ref(false)

// Form
const formName = ref('')
const formEmail = ref('')
const formPhone = ref('')
const formPassword = ref('')
const formRole = ref('')
const formShiftId = ref<number | null>(null)
const formIsActive = ref(true)
const formErrors = ref<Record<string, string>>({})

async function fetchStaff(page = 1) {
  isLoading.value = true
  try {
    const params: Record<string, unknown> = { page, per_page: 15 }
    if (search.value) params.search = search.value
    if (roleFilter.value) params.role = roleFilter.value
    const response = await tenantService.getStaff(params)
    staff.value = response.data
    meta.value = response.meta as typeof meta.value
    currentPage.value = page
  } finally {
    isLoading.value = false
  }
}

async function fetchRoles() {
  const response = await tenantService.getRoles()
  roles.value = [
    ...response.data.system_roles,
    ...response.data.custom_roles,
  ]
}

async function fetchShifts() {
  const response = await tenantService.getShifts()
  shifts.value = response.data
}

const debouncedSearch = debounce(() => fetchStaff(1), 300)
watch(search, () => debouncedSearch())
watch(activeTenantId, (newId) => { if (isBuildingOwner.value && newId) fetchStaff(1) })
watch(roleFilter, () => fetchStaff(1))

function openCreateDialog() {
  isEditing.value = false
  editingUser.value = null
  formName.value = ''
  formEmail.value = ''
  formPhone.value = ''
  formPassword.value = ''
  formRole.value = ''
  formShiftId.value = null
  formIsActive.value = true
  formErrors.value = {}
  showDialog.value = true
}

function openEditDialog(user: IUser) {
  isEditing.value = true
  editingUser.value = user
  formName.value = user.name
  formEmail.value = user.email
  formPhone.value = user.phone ?? ''
  formPassword.value = ''
  formRole.value = user.roles?.[0] ?? ''
  formShiftId.value = user.shift_id ?? null
  formIsActive.value = user.is_active
  formErrors.value = {}
  showDialog.value = true
}

async function saveStaff() {
  formErrors.value = {}

  if (!await validateBuilding()) return
  if (!formName.value) { formErrors.value.name = 'Name is required'; return }
  if (!formEmail.value) { formErrors.value.email = 'Email is required'; return }
  if (!formRole.value) { formErrors.value.role = 'Role is required'; return }
  if (!formShiftId.value) { formErrors.value.shift_id = 'Shift is required'; return }
  if (!isEditing.value && !formPassword.value) { formErrors.value.password = 'Password is required'; return }
  if (formPassword.value && formPassword.value.length < 8) { formErrors.value.password = 'Password must be at least 8 characters'; return }

  isSaving.value = true
  try {
    if (isEditing.value && editingUser.value) {
      const payload: Parameters<typeof tenantService.updateStaff>[1] = {
        name: formName.value,
        email: formEmail.value,
        phone: formPhone.value || undefined,
        role: formRole.value,
        shift_id: formShiftId.value,
        is_active: formIsActive.value,
      }
      if (formPassword.value) payload.password = formPassword.value
      await tenantService.updateStaff(editingUser.value.id, payload)
      toast.add({ severity: 'success', summary: 'Success', detail: 'Staff updated', life: 3000 })
    } else {
      await tenantService.createStaff({
        name: formName.value,
        email: formEmail.value,
        phone: formPhone.value || undefined,
        password: formPassword.value,
        role: formRole.value,
        shift_id: formShiftId.value!,
      })
      toast.add({ severity: 'success', summary: 'Success', detail: 'Staff member created', life: 3000 })
    }
    showDialog.value = false
    await fetchStaff(isEditing.value ? currentPage.value : 1)
  } catch (error) {
    if (isAxiosError(error) && error.response?.status === 422) {
      const errs = error.response.data.errors ?? {}
      for (const key in errs) {
        formErrors.value[key] = errs[key][0]
      }
    } else {
      toast.add({ severity: 'error', summary: 'Error', detail: 'Failed to save', life: 5000 })
    }
  } finally {
    isSaving.value = false
  }
}

function openDeleteDialog(user: IUser) {
  pendingDeleteUser.value = user
  showDeleteDialog.value = true
}

async function confirmDeleteStaff() {
  if (!pendingDeleteUser.value) return
  try {
    await tenantService.deleteStaff(pendingDeleteUser.value.id)
    toast.add({ severity: 'success', summary: 'Deleted', detail: 'Staff member deleted', life: 3000 })
    await fetchStaff(currentPage.value)
  } catch (error) {
    if (isAxiosError(error) && error.response?.data?.message) {
      toast.add({ severity: 'error', summary: 'Error', detail: error.response.data.message, life: 5000 })
    }
  } finally {
    pendingDeleteUser.value = null
  }
}

function getRoleBadge(role: string): string {
  const map: Record<string, string> = {
    tenant_admin: 'bg-purple-100 text-purple-700',
    supervisor: 'bg-blue-100 text-blue-700',
    valet_staff: 'bg-green-100 text-green-700',
    cashier: 'bg-amber-100 text-amber-700',
    viewer: 'bg-gray-100 text-gray-700',
  }
  return map[role] ?? 'bg-indigo-100 text-indigo-700'
}

function getShiftBadge(shiftName: string): string {
  const map: Record<string, string> = {
    Morning: 'bg-yellow-100 text-yellow-700',
    Afternoon: 'bg-orange-100 text-orange-700',
    Night: 'bg-indigo-100 text-indigo-700',
  }
  return map[shiftName] ?? 'bg-gray-100 text-gray-700'
}

onMounted(() => {
  fetchStaff()
  fetchRoles()
  fetchShifts()
})
</script>

<template>
  <div>
    <BuildingSelector mode="filter" />

    <div class="flex items-center justify-between mb-6">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Staff Management</h1>
        <p class="text-sm text-gray-500 mt-1">Manage your team members and their roles</p>
      </div>
      <button v-if="can('users.create')" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium flex items-center gap-2" @click="openCreateDialog">
        <i class="pi pi-plus" /> Add Staff
      </button>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6 flex gap-4 flex-wrap">
      <div class="flex-1 min-w-[200px]">
        <input v-model="search" type="text" placeholder="Search by name or email..." class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none" />
      </div>
      <select v-model="roleFilter" class="border border-gray-300 rounded-lg px-4 py-2 text-sm">
        <option value="">All Roles</option>
        <option v-for="r in roles" :key="r.id" :value="r.name">{{ r.name.replace(/_/g, ' ') }}</option>
      </select>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
      <div v-if="isLoading" class="p-12 text-center"><i class="pi pi-spinner pi-spin text-3xl text-gray-400" /></div>
      <div v-else-if="staff.length === 0" class="p-12 text-center">
        <i class="pi pi-users text-5xl text-gray-300 mb-4" />
        <h3 class="text-lg font-semibold text-gray-700">No staff members yet</h3>
        <p class="text-sm text-gray-500 mt-2">Add your first team member to get started</p>
      </div>
      <AppTable v-else min-width="min-w-[520px]">
        <thead class="bg-gray-50 border-b border-gray-100">
          <tr>
            <th class="text-left text-xs font-medium text-gray-500 uppercase px-6 py-3">Name</th>
            <th class="text-left text-xs font-medium text-gray-500 uppercase px-6 py-3">Role</th>
            <th class="hidden sm:table-cell text-left text-xs font-medium text-gray-500 uppercase px-6 py-3">Shift</th>
            <th class="hidden md:table-cell text-left text-xs font-medium text-gray-500 uppercase px-6 py-3">Status</th>
            <th class="hidden lg:table-cell text-left text-xs font-medium text-gray-500 uppercase px-6 py-3">Last Login</th>
            <th class="text-right text-xs font-medium text-gray-500 uppercase px-6 py-3">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <tr v-for="user in staff" :key="user.id" class="hover:bg-gray-50">
            <td class="px-6 py-4">
              <p class="font-medium text-gray-900">{{ user.name }}</p>
              <p class="text-sm text-gray-500">{{ user.email }}</p>
            </td>
            <td class="px-6 py-4">
              <span v-for="role in (user.roles ?? [])" :key="role"
                class="inline-flex px-2 py-0.5 rounded text-xs font-medium" :class="getRoleBadge(role)">
                {{ role.replace(/_/g, ' ') }}
              </span>
            </td>
            <td class="hidden sm:table-cell px-6 py-4">
              <span v-if="user.shift" class="inline-flex px-2 py-0.5 rounded text-xs font-medium" :class="getShiftBadge(user.shift.name)">
                {{ user.shift.name }}
              </span>
              <span v-else class="text-sm text-gray-400">—</span>
            </td>
            <td class="hidden md:table-cell px-6 py-4">
              <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium"
                :class="user.is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'">
                {{ user.is_active ? 'Active' : 'Inactive' }}
              </span>
            </td>
            <td class="hidden lg:table-cell px-6 py-4 text-sm text-gray-500">
              {{ user.last_login_at ? new Date(user.last_login_at).toLocaleString() : 'Never' }}
            </td>
            <td class="px-6 py-4 text-right">
              <button v-if="can('users.update')" class="text-blue-600 hover:text-blue-700 text-sm mr-2" @click="openEditDialog(user)"><i class="pi pi-pencil" /></button>
              <button v-if="can('users.delete')" class="text-red-600 hover:text-red-700 text-sm" @click="openDeleteDialog(user)"><i class="pi pi-trash" /></button>
            </td>
          </tr>
        </tbody>
      </AppTable>

      <div v-if="meta && meta.last_page > 1" class="flex items-center justify-between px-6 py-4 border-t border-gray-100">
        <p class="text-sm text-gray-500">Showing {{ staff.length }} of {{ meta.total }}</p>
        <div class="flex gap-2">
          <button class="px-3 py-1.5 text-sm border rounded-lg disabled:opacity-50" :disabled="currentPage === 1" @click="fetchStaff(currentPage - 1)">Previous</button>
          <button class="px-3 py-1.5 text-sm border rounded-lg disabled:opacity-50" :disabled="currentPage === meta.last_page" @click="fetchStaff(currentPage + 1)">Next</button>
        </div>
      </div>
    </div>

    <!-- Create/Edit Dialog -->
    <div v-if="showDialog" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
      <div class="bg-white rounded-2xl w-full max-w-lg mx-4 max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b border-gray-100 flex items-center justify-between">
          <h2 class="text-lg font-semibold">{{ isEditing ? 'Edit Staff Member' : 'Add Staff Member' }}</h2>
          <button class="text-gray-400 hover:text-gray-600" @click="showDialog = false"><i class="pi pi-times" /></button>
        </div>
        <div class="p-6 space-y-4">
          <!-- Building selector for building owners -->
          <BuildingSelector />

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Name <span class="text-red-500">*</span></label>
            <input v-model="formName" type="text" class="w-full border rounded-lg px-4 py-2 text-sm" :class="formErrors.name ? 'border-red-500' : 'border-gray-300'" />
            <p v-if="formErrors.name" class="mt-1 text-sm text-red-500">{{ formErrors.name }}</p>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Email <span class="text-red-500">*</span></label>
            <input v-model="formEmail" type="email" class="w-full border rounded-lg px-4 py-2 text-sm" :class="formErrors.email ? 'border-red-500' : 'border-gray-300'" />
            <p v-if="formErrors.email" class="mt-1 text-sm text-red-500">{{ formErrors.email }}</p>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
            <input v-model="formPhone" type="text" class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              Password <span v-if="!isEditing" class="text-red-500">*</span>
              <span v-if="isEditing" class="text-gray-400 font-normal text-xs ml-1">(leave blank to keep current)</span>
            </label>
            <input v-model="formPassword" type="password" class="w-full border rounded-lg px-4 py-2 text-sm" :class="formErrors.password ? 'border-red-500' : 'border-gray-300'" />
            <p v-if="formErrors.password" class="mt-1 text-sm text-red-500">{{ formErrors.password }}</p>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Role <span class="text-red-500">*</span></label>
            <select v-model="formRole" class="w-full border rounded-lg px-4 py-2 text-sm" :class="formErrors.role ? 'border-red-500' : 'border-gray-300'">
              <option value="" disabled>Select role</option>
              <option v-for="r in roles" :key="r.id" :value="r.name">{{ r.name.replace(/_/g, ' ') }}</option>
            </select>
            <p v-if="formErrors.role" class="mt-1 text-sm text-red-500">{{ formErrors.role }}</p>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Shift <span class="text-red-500">*</span></label>
            <select v-model="formShiftId" class="w-full border rounded-lg px-4 py-2 text-sm" :class="formErrors.shift_id ? 'border-red-500' : 'border-gray-300'">
              <option :value="null" disabled>Select shift</option>
              <option v-for="s in shifts" :key="s.id" :value="s.id">
                {{ s.name }} ({{ s.start_time }} – {{ s.end_time }})
              </option>
            </select>
            <p v-if="formErrors.shift_id" class="mt-1 text-sm text-red-500">{{ formErrors.shift_id }}</p>
          </div>
          <div v-if="isEditing" class="flex items-center gap-3">
            <input id="staff_active" v-model="formIsActive" type="checkbox" class="w-4 h-4 rounded border-gray-300" />
            <label for="staff_active" class="text-sm font-medium text-gray-700">Active</label>
          </div>
        </div>
        <div class="p-6 border-t border-gray-100 flex justify-end gap-3">
          <button class="px-4 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50" @click="showDialog = false">Cancel</button>
          <button :disabled="isSaving" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 flex items-center gap-2" @click="saveStaff">
            <i v-if="isSaving" class="pi pi-spinner pi-spin" />
            {{ isEditing ? 'Update' : 'Create' }}
          </button>
        </div>
      </div>
    </div>

    <AppConfirmDialog
      v-model="showDeleteDialog"
      title="Delete Staff Member"
      :message="pendingDeleteUser ? `Delete &quot;${pendingDeleteUser.name}&quot;? This cannot be undone.` : ''"
      confirm-label="Delete"
      :danger="true"
      @confirm="confirmDeleteStaff"
    />
  </div>
</template>
