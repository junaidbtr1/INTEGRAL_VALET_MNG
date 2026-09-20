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
import type { IUser } from '@/types'

const toast = useToast()
const users = ref<IUser[]>([])
const isLoading = ref(true)
const search = ref('')
const meta = ref<{ current_page: number; last_page: number; total: number } | null>(null)
const currentPage = ref(1)
const showCreateDialog = ref(false)
const showEditDialog = ref(false)
const editingUser = ref<IUser | null>(null)
const showDeleteDialog = ref(false)
const pendingDelete = ref<IUser | null>(null)

// Create form — building owners only (no tenant_id, no role)
const createSchema = yup.object({
  name: yup.string().required('Name is required').max(255),
  email: yup.string().required('Email is required').email('Invalid email'),
  phone: yup.string().nullable(),
  password: yup.string().required('Password is required').min(8, 'Password must be at least 8 characters'),
})

const { handleSubmit: handleCreate, setErrors: setCreateErrors, isSubmitting: isCreating, resetForm: resetCreateForm } = useForm({
  validationSchema: createSchema,
  initialValues: { name: '', email: '', phone: '', password: '' },
})
const { value: cName, errorMessage: cNameError } = useField('name')
const { value: cEmail, errorMessage: cEmailError } = useField('email')
const { value: cPhone } = useField('phone')
const { value: cPassword, errorMessage: cPasswordError } = useField('password')

// Edit form
const editSchema = yup.object({
  name: yup.string().required('Name is required').max(255),
  email: yup.string().required('Email is required').email('Invalid email'),
  phone: yup.string().nullable(),
  password: yup.string().nullable().min(8, 'Password must be at least 8 characters'),
  is_active: yup.boolean(),
})

const { handleSubmit: handleEdit, setErrors: setEditErrors, isSubmitting: isUpdating, resetForm: resetEditForm, setValues: setEditValues } = useForm({
  validationSchema: editSchema,
})
const { value: eName, errorMessage: eNameError } = useField('name')
const { value: eEmail, errorMessage: eEmailError } = useField('email')
const { value: ePhone } = useField('phone')
const { value: ePassword, errorMessage: ePasswordError } = useField('password')
const { value: eIsActive } = useField('is_active')

async function fetchUsers(page = 1) {
  isLoading.value = true
  try {
    const params: Record<string, unknown> = { page, per_page: 15, is_building_owner: true }
    if (search.value) params.search = search.value
    const response = await adminService.getUsers(params)
    users.value = response.data
    meta.value = response.meta as typeof meta.value
    currentPage.value = page
  } finally {
    isLoading.value = false
  }
}

const debouncedSearch = debounce(() => fetchUsers(1), 300)
watch(search, () => debouncedSearch())

const onCreateSubmit = handleCreate(async (values) => {
  try {
    await adminService.createUser({
      ...values,
      is_building_owner: true,
    } as Parameters<typeof adminService.createUser>[0])
    toast.add({ severity: 'success', summary: 'Success', detail: 'Building owner created successfully', life: 3000 })
    showCreateDialog.value = false
    resetCreateForm()
    await fetchUsers(1)
  } catch (error) {
    if (isAxiosError(error) && error.response?.status === 422) {
      setCreateErrors(error.response.data.errors ?? {})
    } else {
      toast.add({ severity: 'error', summary: 'Error', detail: 'Failed to create building owner', life: 5000 })
    }
  }
})

function openEditDialog(user: IUser) {
  editingUser.value = user
  setEditValues({
    name: user.name,
    email: user.email,
    phone: user.phone ?? '',
    password: '',
    is_active: user.is_active,
  })
  showEditDialog.value = true
}

const onEditSubmit = handleEdit(async (values) => {
  if (!editingUser.value) return
  try {
    const payload: Record<string, unknown> = { ...values }
    if (!payload.password) delete payload.password
    await adminService.updateUser(editingUser.value.id, payload as Parameters<typeof adminService.updateUser>[1])
    toast.add({ severity: 'success', summary: 'Success', detail: 'User updated successfully', life: 3000 })
    showEditDialog.value = false
    editingUser.value = null
    resetEditForm()
    await fetchUsers(currentPage.value)
  } catch (error) {
    if (isAxiosError(error) && error.response?.status === 422) {
      setEditErrors(error.response.data.errors ?? {})
    } else {
      toast.add({ severity: 'error', summary: 'Error', detail: 'Failed to update user', life: 5000 })
    }
  }
})

function openDeleteDialog(user: IUser) {
  pendingDelete.value = user
  showDeleteDialog.value = true
}

async function confirmDeleteUser() {
  if (!pendingDelete.value) return
  try {
    await adminService.deleteUser(pendingDelete.value.id)
    toast.add({ severity: 'success', summary: 'Deleted', detail: 'User deleted', life: 3000 })
    await fetchUsers(currentPage.value)
  } catch {
    toast.add({ severity: 'error', summary: 'Error', detail: 'Failed to delete user', life: 5000 })
  } finally {
    pendingDelete.value = null
  }
}

onMounted(() => {
  fetchUsers()
})
</script>

<template>
  <div>
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Building Owners</h1>
        <p class="text-sm text-gray-500 mt-1">Users who own and manage one or more buildings</p>
      </div>
      <button
        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium flex items-center gap-2"
        @click="showCreateDialog = true"
      >
        <i class="pi pi-plus" />
        Add Building Owner
      </button>
    </div>

    <!-- Search -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6">
      <input
        v-model="search"
        type="text"
        placeholder="Search by name or email..."
        class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
      />
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
      <div v-if="isLoading" class="p-12 text-center">
        <i class="pi pi-spinner pi-spin text-3xl text-gray-400" />
      </div>
      <div v-else-if="users.length === 0" class="p-12 text-center">
        <i class="pi pi-building text-4xl text-gray-300 mb-3" />
        <p class="text-gray-500">No building owners found</p>
      </div>
      <AppTable v-else min-width="min-w-[480px]">
        <thead class="bg-gray-50 border-b border-gray-100">
          <tr>
            <th class="text-left text-xs font-medium text-gray-500 uppercase px-6 py-3">Owner</th>
            <th class="hidden md:table-cell text-left text-xs font-medium text-gray-500 uppercase px-6 py-3">Buildings</th>
            <th class="text-left text-xs font-medium text-gray-500 uppercase px-6 py-3">Status</th>
            <th class="hidden lg:table-cell text-left text-xs font-medium text-gray-500 uppercase px-6 py-3">Last Login</th>
            <th class="text-right text-xs font-medium text-gray-500 uppercase px-6 py-3">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <tr v-for="user in users" :key="user.id" class="hover:bg-gray-50">
            <td class="px-6 py-4">
              <div>
                <p class="font-medium text-gray-900 flex items-center gap-2">
                  {{ user.name }}
                  <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium bg-indigo-100 text-indigo-700">
                    Building Owner
                  </span>
                </p>
                <p class="text-sm text-gray-500">{{ user.email }}</p>
              </div>
            </td>
            <td class="hidden md:table-cell px-6 py-4 text-sm text-gray-600">
              {{ user.owned_buildings?.length ?? 0 }} building(s)
            </td>
            <td class="px-6 py-4">
              <span
                class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium"
                :class="user.is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'"
              >
                {{ user.is_active ? 'Active' : 'Inactive' }}
              </span>
            </td>
            <td class="hidden lg:table-cell px-6 py-4 text-sm text-gray-500">
              {{ user.last_login_at ? new Date(user.last_login_at).toLocaleString() : 'Never' }}
            </td>
            <td class="px-6 py-4 text-right">
              <div class="flex items-center justify-end gap-2">
                <button class="text-blue-600 hover:text-blue-700 text-sm" @click="openEditDialog(user)">
                  <i class="pi pi-pencil" />
                </button>
                <button class="text-red-600 hover:text-red-700 text-sm" @click="openDeleteDialog(user)">
                  <i class="pi pi-trash" />
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </AppTable>

      <!-- Pagination -->
      <div v-if="meta && meta.last_page > 1" class="flex items-center justify-between px-6 py-4 border-t border-gray-100">
        <p class="text-sm text-gray-500">Showing {{ users.length }} of {{ meta.total }}</p>
        <div class="flex gap-2">
          <button class="px-3 py-1.5 text-sm border rounded-lg disabled:opacity-50" :disabled="currentPage === 1" @click="fetchUsers(currentPage - 1)">Previous</button>
          <button class="px-3 py-1.5 text-sm border rounded-lg disabled:opacity-50" :disabled="currentPage === meta.last_page" @click="fetchUsers(currentPage + 1)">Next</button>
        </div>
      </div>
    </div>

    <!-- Create Dialog -->
    <div v-if="showCreateDialog" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
      <div class="bg-white rounded-2xl w-full max-w-lg mx-4 max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b border-gray-100 flex items-center justify-between">
          <h2 class="text-lg font-semibold">Add Building Owner</h2>
          <button class="text-gray-400 hover:text-gray-600" @click="showCreateDialog = false">
            <i class="pi pi-times" />
          </button>
        </div>
        <form class="p-6 space-y-4" @submit.prevent="onCreateSubmit" novalidate>
          <div class="bg-indigo-50 border border-indigo-100 rounded-lg px-4 py-3 text-sm text-indigo-700">
            <i class="pi pi-info-circle mr-2" />
            Building owners can be assigned to one or more buildings by the super admin. They don't belong to any specific tenant.
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Name <span class="text-red-500">*</span></label>
            <input v-model="cName" type="text" class="w-full border rounded-lg px-4 py-2 text-sm" :class="cNameError ? 'border-red-500' : 'border-gray-300'" />
            <p v-if="cNameError" class="mt-1 text-sm text-red-500">{{ cNameError }}</p>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Email <span class="text-red-500">*</span></label>
            <input v-model="cEmail" type="email" class="w-full border rounded-lg px-4 py-2 text-sm" :class="cEmailError ? 'border-red-500' : 'border-gray-300'" />
            <p v-if="cEmailError" class="mt-1 text-sm text-red-500">{{ cEmailError }}</p>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
            <input v-model="cPhone" type="text" class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Password <span class="text-red-500">*</span></label>
            <input v-model="cPassword" type="password" class="w-full border rounded-lg px-4 py-2 text-sm" :class="cPasswordError ? 'border-red-500' : 'border-gray-300'" />
            <p v-if="cPasswordError" class="mt-1 text-sm text-red-500">{{ cPasswordError }}</p>
          </div>
          <div class="flex justify-end gap-3 pt-4">
            <button type="button" class="px-4 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50" @click="showCreateDialog = false">Cancel</button>
            <button type="submit" :disabled="isCreating" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 flex items-center gap-2">
              <i v-if="isCreating" class="pi pi-spinner pi-spin" />
              Create Building Owner
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Edit Dialog -->
    <div v-if="showEditDialog && editingUser" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
      <div class="bg-white rounded-2xl w-full max-w-lg mx-4 max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b border-gray-100 flex items-center justify-between">
          <h2 class="text-lg font-semibold">Edit — {{ editingUser.name }}</h2>
          <button class="text-gray-400 hover:text-gray-600" @click="showEditDialog = false; editingUser = null">
            <i class="pi pi-times" />
          </button>
        </div>
        <form class="p-6 space-y-4" @submit.prevent="onEditSubmit" novalidate>
          <div class="bg-gray-50 rounded-lg px-4 py-2 text-sm text-gray-600">
            Buildings owned: <strong>{{ editingUser.owned_buildings?.length ?? 0 }}</strong>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Name <span class="text-red-500">*</span></label>
            <input v-model="eName" type="text" class="w-full border rounded-lg px-4 py-2 text-sm" :class="eNameError ? 'border-red-500' : 'border-gray-300'" />
            <p v-if="eNameError" class="mt-1 text-sm text-red-500">{{ eNameError }}</p>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Email <span class="text-red-500">*</span></label>
            <input v-model="eEmail" type="email" class="w-full border rounded-lg px-4 py-2 text-sm" :class="eEmailError ? 'border-red-500' : 'border-gray-300'" />
            <p v-if="eEmailError" class="mt-1 text-sm text-red-500">{{ eEmailError }}</p>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
            <input v-model="ePhone" type="text" class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Password <span class="text-gray-400">(leave blank to keep current)</span></label>
            <input v-model="ePassword" type="password" class="w-full border rounded-lg px-4 py-2 text-sm" :class="ePasswordError ? 'border-red-500' : 'border-gray-300'" placeholder="Enter new password..." />
            <p v-if="ePasswordError" class="mt-1 text-sm text-red-500">{{ ePasswordError }}</p>
          </div>
          <div class="flex items-center gap-3">
            <input id="edit_is_active" v-model="eIsActive" type="checkbox" class="w-4 h-4 rounded border-gray-300" />
            <label for="edit_is_active" class="text-sm font-medium text-gray-700">Active</label>
          </div>
          <div class="flex justify-end gap-3 pt-4">
            <button type="button" class="px-4 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50" @click="showEditDialog = false; editingUser = null">Cancel</button>
            <button type="submit" :disabled="isUpdating" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 flex items-center gap-2">
              <i v-if="isUpdating" class="pi pi-spinner pi-spin" />
              Update
            </button>
          </div>
        </form>
      </div>
    </div>

    <AppConfirmDialog
      v-model="showDeleteDialog"
      title="Delete Building Owner"
      :message="pendingDelete ? `Delete &quot;${pendingDelete.name}&quot;? This cannot be undone.` : ''"
      confirm-label="Delete"
      :danger="true"
      @confirm="confirmDeleteUser"
    />
  </div>
</template>
