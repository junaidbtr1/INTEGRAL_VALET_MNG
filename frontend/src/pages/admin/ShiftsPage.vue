<script setup lang="ts">
import { ref, onMounted, watch } from 'vue'
import AppConfirmDialog from '@/components/ui/AppConfirmDialog.vue'
import BuildingSelector from '@/components/ui/BuildingSelector.vue'
import { tenantService } from '@/services/tenantService'
import type { IShift } from '@/services/tenantService'
import { useToast } from 'primevue/usetoast'
import { isAxiosError } from 'axios'
import { useBuildingGuard } from '@/composables/useBuildingGuard'
import { usePermission } from '@/composables/usePermission'

const { validateBuilding, isBuildingOwner, activeTenantId } = useBuildingGuard()
const { can } = usePermission()

const toast = useToast()
const shifts = ref<IShift[]>([])
const isLoading = ref(true)

// Dialog
const showDialog = ref(false)
const isEditing = ref(false)
const editingShift = ref<IShift | null>(null)
const showDeleteDialog = ref(false)
const pendingDelete = ref<IShift | null>(null)
const isSaving = ref(false)

// Form
const formName = ref('')
const formStartTime = ref('')
const formEndTime = ref('')
const formIsActive = ref(true)
const formErrors = ref<Record<string, string>>({})

async function fetchShifts() {
  isLoading.value = true
  try {
    const response = await tenantService.getShifts()
    shifts.value = response.data
  } finally {
    isLoading.value = false
  }
}

function openCreateDialog() {
  isEditing.value = false
  editingShift.value = null
  formName.value = ''
  formStartTime.value = ''
  formEndTime.value = ''
  formIsActive.value = true
  formErrors.value = {}
  showDialog.value = true
}

function openEditDialog(shift: IShift) {
  isEditing.value = true
  editingShift.value = shift
  formName.value = shift.name
  formStartTime.value = shift.start_time
  formEndTime.value = shift.end_time
  formIsActive.value = shift.is_active
  formErrors.value = {}
  showDialog.value = true
}

async function saveShift() {
  formErrors.value = {}

  if (!await validateBuilding()) return
  if (!formName.value) { formErrors.value.name = 'Shift name is required'; return }
  if (!formStartTime.value) { formErrors.value.start_time = 'Start time is required'; return }
  if (!formEndTime.value) { formErrors.value.end_time = 'End time is required'; return }

  isSaving.value = true
  try {
    if (isEditing.value && editingShift.value) {
      await tenantService.updateShift(editingShift.value.id, {
        name: formName.value,
        start_time: formStartTime.value,
        end_time: formEndTime.value,
        is_active: formIsActive.value,
      })
      toast.add({ severity: 'success', summary: 'Success', detail: 'Shift updated', life: 3000 })
    } else {
      await tenantService.createShift({
        name: formName.value,
        start_time: formStartTime.value,
        end_time: formEndTime.value,
        is_active: formIsActive.value,
      })
      toast.add({ severity: 'success', summary: 'Success', detail: 'Shift created', life: 3000 })
    }
    showDialog.value = false
    await fetchShifts()
  } catch (error) {
    if (isAxiosError(error) && error.response?.status === 422) {
      const errs = error.response.data.errors ?? {}
      for (const key in errs) {
        formErrors.value[key] = errs[key][0]
      }
    } else {
      toast.add({ severity: 'error', summary: 'Error', detail: 'Failed to save shift', life: 5000 })
    }
  } finally {
    isSaving.value = false
  }
}

function openDeleteDialog(shift: IShift) {
  pendingDelete.value = shift
  showDeleteDialog.value = true
}

async function confirmDeleteShift() {
  if (!pendingDelete.value) return
  try {
    await tenantService.deleteShift(pendingDelete.value.id)
    toast.add({ severity: 'success', summary: 'Deleted', detail: 'Shift deleted', life: 3000 })
    await fetchShifts()
  } catch (error) {
    if (isAxiosError(error) && error.response?.data?.message) {
      toast.add({ severity: 'error', summary: 'Error', detail: error.response.data.message, life: 5000 })
    }
  } finally {
    pendingDelete.value = null
  }
}

function getShiftColor(name: string): { bg: string; dot: string } {
  const map: Record<string, { bg: string; dot: string }> = {
    Morning: { bg: 'bg-yellow-50 border-yellow-200', dot: 'bg-yellow-400' },
    Afternoon: { bg: 'bg-orange-50 border-orange-200', dot: 'bg-orange-400' },
    Night: { bg: 'bg-indigo-50 border-indigo-200', dot: 'bg-indigo-400' },
  }
  return map[name] ?? { bg: 'bg-gray-50 border-gray-200', dot: 'bg-gray-400' }
}

watch(activeTenantId, (newId) => { if (isBuildingOwner.value && newId) fetchShifts() })
onMounted(() => fetchShifts())
</script>

<template>
  <div>
    <BuildingSelector mode="filter" />

    <div class="flex items-center justify-between mb-6">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Shifts</h1>
        <p class="text-sm text-gray-500 mt-1">Manage work shifts for your team</p>
      </div>
      <button v-if="can('settings.update')" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium flex items-center gap-2" @click="openCreateDialog">
        <i class="pi pi-plus" /> Add Shift
      </button>
    </div>

    <!-- Loading -->
    <div v-if="isLoading" class="flex justify-center py-16">
      <i class="pi pi-spinner pi-spin text-3xl text-gray-400" />
    </div>

    <!-- Empty -->
    <div v-else-if="shifts.length === 0" class="bg-white rounded-xl shadow-sm border border-gray-100 p-16 text-center">
      <i class="pi pi-clock text-5xl text-gray-300 mb-4" />
      <h3 class="text-lg font-semibold text-gray-700">No shifts yet</h3>
      <p class="text-sm text-gray-500 mt-2">Create shifts to assign to your staff members</p>
    </div>

    <!-- Shifts Grid -->
    <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
      <div
        v-for="shift in shifts"
        :key="shift.id"
        class="bg-white rounded-xl shadow-sm border p-5 flex flex-col gap-4"
        :class="getShiftColor(shift.name).bg"
      >
        <div class="flex items-start justify-between">
          <div class="flex items-center gap-3">
            <div class="w-3 h-3 rounded-full" :class="getShiftColor(shift.name).dot" />
            <div>
              <h3 class="font-semibold text-gray-900">{{ shift.name }}</h3>
              <p class="text-sm text-gray-500 mt-0.5">{{ shift.start_time }} – {{ shift.end_time }}</p>
            </div>
          </div>
          <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium"
            :class="shift.is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'">
            {{ shift.is_active ? 'Active' : 'Inactive' }}
          </span>
        </div>

        <div class="flex items-center justify-between pt-2 border-t border-gray-100">
          <span class="text-sm text-gray-500">
            <i class="pi pi-users text-xs mr-1" />
            {{ shift.staff_count }} staff assigned
          </span>
          <div class="flex gap-2">
            <button v-if="can('settings.update')" class="text-blue-600 hover:text-blue-700 text-sm p-1" @click="openEditDialog(shift)">
              <i class="pi pi-pencil" />
            </button>
            <button v-if="can('settings.update')" class="text-red-600 hover:text-red-700 text-sm p-1" @click="openDeleteDialog(shift)">
              <i class="pi pi-trash" />
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Create/Edit Dialog -->
    <div v-if="showDialog" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
      <div class="bg-white rounded-2xl w-full max-w-md mx-4">
        <div class="p-6 border-b border-gray-100 flex items-center justify-between">
          <h2 class="text-lg font-semibold">{{ isEditing ? 'Edit Shift' : 'Add Shift' }}</h2>
          <button class="text-gray-400 hover:text-gray-600" @click="showDialog = false">
            <i class="pi pi-times" />
          </button>
        </div>
        <div class="p-6 space-y-4">
          <!-- Building selector for building owners -->
          <BuildingSelector />

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Name <span class="text-red-500">*</span></label>
            <input v-model="formName" type="text" placeholder="e.g. Morning" class="w-full border rounded-lg px-4 py-2 text-sm" :class="formErrors.name ? 'border-red-500' : 'border-gray-300'" />
            <p v-if="formErrors.name" class="mt-1 text-sm text-red-500">{{ formErrors.name }}</p>
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Start Time <span class="text-red-500">*</span></label>
              <input v-model="formStartTime" type="time" class="w-full border rounded-lg px-4 py-2 text-sm" :class="formErrors.start_time ? 'border-red-500' : 'border-gray-300'" />
              <p v-if="formErrors.start_time" class="mt-1 text-sm text-red-500">{{ formErrors.start_time }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">End Time <span class="text-red-500">*</span></label>
              <input v-model="formEndTime" type="time" class="w-full border rounded-lg px-4 py-2 text-sm" :class="formErrors.end_time ? 'border-red-500' : 'border-gray-300'" />
              <p v-if="formErrors.end_time" class="mt-1 text-sm text-red-500">{{ formErrors.end_time }}</p>
            </div>
          </div>
          <div class="flex items-center gap-3">
            <input id="shift_active" v-model="formIsActive" type="checkbox" class="w-4 h-4 rounded border-gray-300" />
            <label for="shift_active" class="text-sm font-medium text-gray-700">Active</label>
          </div>
        </div>
        <div class="p-6 border-t border-gray-100 flex justify-end gap-3">
          <button class="px-4 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50" @click="showDialog = false">Cancel</button>
          <button :disabled="isSaving" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 flex items-center gap-2" @click="saveShift">
            <i v-if="isSaving" class="pi pi-spinner pi-spin" />
            {{ isEditing ? 'Update' : 'Create' }}
          </button>
        </div>
      </div>
    </div>

    <AppConfirmDialog
      v-model="showDeleteDialog"
      title="Delete Shift"
      :message="pendingDelete ? `Delete &quot;${pendingDelete.name}&quot; shift? This cannot be undone.` : ''"
      confirm-label="Delete"
      :danger="true"
      @confirm="confirmDeleteShift"
    />
  </div>
</template>
