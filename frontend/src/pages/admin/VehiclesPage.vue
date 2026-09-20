<script setup lang="ts">
import { ref, onMounted, watch } from 'vue'
import { debounce } from 'lodash-es'
import AppTable from '@/components/ui/AppTable.vue'
import AppConfirmDialog from '@/components/ui/AppConfirmDialog.vue'
import BuildingSelector from '@/components/ui/BuildingSelector.vue'
import { tenantService } from '@/services/tenantService'
import type { IVehicle } from '@/services/tenantService'
import { useToast } from 'primevue/usetoast'
import { isAxiosError } from 'axios'
import { useBuildingGuard } from '@/composables/useBuildingGuard'
import { usePermission } from '@/composables/usePermission'

const { validateBuilding, isBuildingOwner, activeTenantId } = useBuildingGuard()
const { can } = usePermission()

const toast = useToast()
const vehicles = ref<IVehicle[]>([])
const isLoading = ref(true)
const search = ref('')
const meta = ref<{ current_page: number; last_page: number; total: number } | null>(null)
const currentPage = ref(1)
const showDialog = ref(false)
const showDeleteDialog = ref(false)
const pendingDelete = ref<IVehicle | null>(null)
const isEditing = ref(false)
const editingId = ref<number | null>(null)
const isSaving = ref(false)
const form = ref({ plate_number: '', vehicle_type: 'car', color: '', make: '', model: '', owner_name: '', owner_phone: '', is_vip: false, is_blacklisted: false, blacklist_reason: '', notes: '' })
const formErrors = ref<Record<string, string>>({})

async function fetchVehicles(page = 1) {
  isLoading.value = true
  try {
    const params: Record<string, unknown> = { page, per_page: 15 }
    if (search.value) params.search = search.value
    const response = await tenantService.getVehicles(params)
    vehicles.value = response.data
    meta.value = response.meta as typeof meta.value
    currentPage.value = page
  } finally { isLoading.value = false }
}

const debouncedSearch = debounce(() => fetchVehicles(1), 300)
watch(search, () => debouncedSearch())
watch(activeTenantId, (newId) => { if (isBuildingOwner.value && newId) fetchVehicles(1) })

function openCreate() {
  isEditing.value = false; editingId.value = null; formErrors.value = {}
  form.value = { plate_number: '', vehicle_type: 'car', color: '', make: '', model: '', owner_name: '', owner_phone: '', is_vip: false, is_blacklisted: false, blacklist_reason: '', notes: '' }
  showDialog.value = true
}

function openEdit(v: IVehicle) {
  isEditing.value = true; editingId.value = v.id; formErrors.value = {}
  form.value = { plate_number: v.plate_number, vehicle_type: v.vehicle_type, color: v.color ?? '', make: v.make ?? '', model: v.model ?? '', owner_name: v.owner_name ?? '', owner_phone: v.owner_phone ?? '', is_vip: v.is_vip, is_blacklisted: v.is_blacklisted, blacklist_reason: v.blacklist_reason ?? '', notes: '' }
  showDialog.value = true
}

async function saveVehicle() {
  formErrors.value = {}
  if (!await validateBuilding()) return
  if (!form.value.plate_number) { formErrors.value.plate_number = 'Plate number is required'; return }
  isSaving.value = true
  try {
    if (isEditing.value && editingId.value) {
      await tenantService.updateVehicle(editingId.value, form.value)
      toast.add({ severity: 'success', summary: 'Updated', detail: 'Vehicle updated', life: 3000 })
    } else {
      await tenantService.createVehicle(form.value)
      toast.add({ severity: 'success', summary: 'Created', detail: 'Vehicle registered', life: 3000 })
    }
    showDialog.value = false
    await fetchVehicles(isEditing.value ? currentPage.value : 1)
  } catch (error) {
    if (isAxiosError(error) && error.response?.status === 422) {
      const errs = error.response.data.errors ?? {}
      for (const key in errs) formErrors.value[key] = errs[key][0]
    } else if (isAxiosError(error)) toast.add({ severity: 'error', summary: 'Error', detail: error.response?.data?.message ?? 'Failed', life: 5000 })
  } finally { isSaving.value = false }
}

function openDeleteDialog(v: IVehicle) {
  pendingDelete.value = v
  showDeleteDialog.value = true
}

async function confirmDeleteVehicle() {
  if (!pendingDelete.value) return
  try { await tenantService.deleteVehicle(pendingDelete.value.id); toast.add({ severity: 'success', summary: 'Deleted', life: 3000 }); await fetchVehicles(currentPage.value) }
  catch { toast.add({ severity: 'error', summary: 'Error', detail: 'Failed to delete', life: 5000 }) }
  finally { pendingDelete.value = null }
}

onMounted(() => fetchVehicles())
</script>

<template>
  <div>
    <BuildingSelector mode="filter" />

    <div class="flex items-center justify-between mb-6">
      <div><h1 class="text-2xl font-bold text-slate-900">Vehicles</h1><p class="text-sm text-slate-500 mt-1">Registered vehicles and visit history</p></div>
      <button v-if="can('vehicles.create')" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2.5 rounded-lg text-sm font-semibold flex items-center gap-2 shadow-sm transition-all" @click="openCreate"><i class="pi pi-plus text-xs" />Register Vehicle</button>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 p-4 mb-6">
      <input v-model="search" type="text" placeholder="Search by plate, owner name, or phone..." class="w-full h-10 border border-slate-300 rounded-lg px-4 text-sm outline-none hover:border-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/10 transition-all" />
    </div>

    <div class="bg-white rounded-xl border border-slate-200">
      <div v-if="isLoading" class="p-16 text-center"><i class="pi pi-spinner pi-spin text-3xl text-slate-300" /></div>
      <div v-else-if="vehicles.length === 0" class="p-16 text-center"><i class="pi pi-car text-5xl text-slate-200 mb-4" /><h3 class="text-lg font-semibold text-slate-600">No vehicles found</h3></div>
      <template v-else>
        <AppTable min-width="min-w-[520px]">
          <thead><tr class="border-b border-slate-100">
            <th class="text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider px-6 py-3">Plate</th>
            <th class="hidden sm:table-cell text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider px-6 py-3">Type</th>
            <th class="text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider px-6 py-3">Owner</th>
            <th class="hidden md:table-cell text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider px-6 py-3">Visits</th>
            <th class="hidden lg:table-cell text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider px-6 py-3">Flags</th>
            <th class="text-right text-[11px] font-semibold text-slate-400 uppercase tracking-wider px-6 py-3">Actions</th>
          </tr></thead>
          <tbody class="divide-y divide-slate-100">
            <tr v-for="v in vehicles" :key="v.id" class="hover:bg-slate-50/50 transition-colors">
              <td class="px-6 py-4"><p class="text-sm font-semibold text-slate-900 font-mono">{{ v.plate_number }}</p><p v-if="v.color" class="text-xs text-slate-400 mt-0.5">{{ v.color }}</p></td>
              <td class="hidden sm:table-cell px-6 py-4 text-sm text-slate-600 capitalize">{{ v.vehicle_type }}</td>
              <td class="px-6 py-4"><p class="text-sm text-slate-700">{{ v.owner_name || '-' }}</p><p v-if="v.owner_phone" class="text-xs text-slate-400">{{ v.owner_phone }}</p></td>
              <td class="hidden md:table-cell px-6 py-4 text-sm font-medium text-slate-700">{{ v.visit_count }}</td>
              <td class="hidden lg:table-cell px-6 py-4">
                <div class="flex gap-1">
                  <span v-if="v.is_vip" class="inline-flex px-2 py-0.5 rounded text-[10px] font-semibold bg-indigo-50 text-indigo-700 ring-1 ring-indigo-600/20">VIP</span>
                  <span v-if="v.is_blacklisted" class="inline-flex px-2 py-0.5 rounded text-[10px] font-semibold bg-red-50 text-red-700 ring-1 ring-red-600/20">Blacklisted</span>
                </div>
              </td>
              <td class="px-6 py-4 text-right">
                <button v-if="can('vehicles.update')" class="p-1.5 rounded-md text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 transition-colors" @click="openEdit(v)"><i class="pi pi-pencil text-sm" /></button>
                <button v-if="can('vehicles.delete')" class="p-1.5 rounded-md text-slate-400 hover:text-red-600 hover:bg-red-50 transition-colors" @click="openDeleteDialog(v)"><i class="pi pi-trash text-sm" /></button>
              </td>
            </tr>
          </tbody>
        </AppTable>
        <div v-if="meta && meta.last_page > 1" class="flex items-center justify-between px-6 py-4 border-t border-slate-100">
          <p class="text-sm text-slate-400">Page {{ currentPage }} of {{ meta.last_page }}</p>
          <div class="flex gap-2"><button class="px-3 py-1.5 text-sm border border-slate-300 rounded-lg hover:bg-slate-50 disabled:opacity-40" :disabled="currentPage <= 1" @click="fetchVehicles(currentPage - 1)">Previous</button><button class="px-3 py-1.5 text-sm border border-slate-300 rounded-lg hover:bg-slate-50 disabled:opacity-40" :disabled="currentPage >= meta.last_page" @click="fetchVehicles(currentPage + 1)">Next</button></div>
        </div>
      </template>
    </div>

    <!-- Dialog -->
    <div v-if="showDialog" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center z-50">
      <div class="bg-white rounded-2xl w-full max-w-lg mx-4 shadow-2xl max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b border-slate-100 flex items-center justify-between sticky top-0 bg-white z-10"><h2 class="text-lg font-semibold text-slate-900">{{ isEditing ? 'Edit Vehicle' : 'Register Vehicle' }}</h2><button class="text-slate-400 hover:text-slate-600" @click="showDialog = false"><i class="pi pi-times text-sm" /></button></div>
        <div class="p-6 space-y-4">
          <!-- Building selector for building owners -->
          <BuildingSelector />

          <div><label class="block text-sm font-medium text-slate-700 mb-1.5">Plate Number <span class="text-red-500">*</span></label><input v-model="form.plate_number" type="text" :disabled="isEditing" class="w-full h-10 border rounded-lg px-4 text-sm uppercase outline-none transition-all" :class="[formErrors.plate_number ? 'border-red-400 bg-red-50/50' : 'border-slate-300 focus:border-indigo-500', isEditing ? 'bg-slate-50 text-slate-500' : '']" /><p v-if="formErrors.plate_number" class="mt-1 text-[13px] text-red-500">{{ formErrors.plate_number }}</p></div>
          <div><label class="block text-sm font-medium text-slate-700 mb-1.5">Type</label><select v-model="form.vehicle_type" class="w-full h-10 border border-slate-300 rounded-lg px-4 text-sm outline-none focus:border-indigo-500 transition-all"><option value="motorcycle">Motorcycle</option><option value="car">Car</option><option value="suv">SUV</option><option value="van">Van</option><option value="truck">Truck</option><option value="bus">Bus</option></select></div>
          <div class="grid grid-cols-2 gap-4">
            <div><label class="block text-sm font-medium text-slate-700 mb-1.5">Color</label><input v-model="form.color" type="text" class="w-full h-10 border border-slate-300 rounded-lg px-4 text-sm outline-none focus:border-indigo-500 transition-all" /></div>
            <div><label class="block text-sm font-medium text-slate-700 mb-1.5">Make</label><input v-model="form.make" type="text" placeholder="Toyota" class="w-full h-10 border border-slate-300 rounded-lg px-4 text-sm outline-none focus:border-indigo-500 transition-all" /></div>
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div><label class="block text-sm font-medium text-slate-700 mb-1.5">Owner Name</label><input v-model="form.owner_name" type="text" class="w-full h-10 border border-slate-300 rounded-lg px-4 text-sm outline-none focus:border-indigo-500 transition-all" /></div>
            <div><label class="block text-sm font-medium text-slate-700 mb-1.5">Owner Phone</label><input v-model="form.owner_phone" type="text" class="w-full h-10 border border-slate-300 rounded-lg px-4 text-sm outline-none focus:border-indigo-500 transition-all" /></div>
          </div>
          <div class="flex items-center gap-6">
            <label class="flex items-center gap-2 cursor-pointer"><input v-model="form.is_vip" type="checkbox" class="w-4 h-4 rounded border-slate-300 text-indigo-600" /><span class="text-sm font-medium text-slate-700">VIP</span></label>
            <label class="flex items-center gap-2 cursor-pointer"><input v-model="form.is_blacklisted" type="checkbox" class="w-4 h-4 rounded border-slate-300 text-red-600" /><span class="text-sm font-medium text-slate-700">Blacklisted</span></label>
          </div>
          <div v-if="form.is_blacklisted"><label class="block text-sm font-medium text-slate-700 mb-1.5">Blacklist Reason</label><input v-model="form.blacklist_reason" type="text" class="w-full h-10 border border-slate-300 rounded-lg px-4 text-sm outline-none focus:border-indigo-500 transition-all" /></div>
        </div>
        <div class="p-6 border-t border-slate-100 flex gap-3 sticky bottom-0 bg-white">
          <button class="flex-1 px-4 py-2.5 text-sm font-medium border border-slate-300 rounded-lg hover:bg-slate-50 transition-colors" @click="showDialog = false">Cancel</button>
          <button :disabled="isSaving" class="flex-1 px-4 py-2.5 text-sm font-semibold bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 disabled:opacity-50 flex items-center justify-center gap-2 transition-all" @click="saveVehicle"><i v-if="isSaving" class="pi pi-spinner pi-spin text-xs" />{{ isEditing ? 'Update' : 'Register' }}</button>
        </div>
      </div>
    </div>

    <AppConfirmDialog
      v-model="showDeleteDialog"
      title="Delete Vehicle"
      :message="pendingDelete ? `Delete vehicle &quot;${pendingDelete.plate_number}&quot;? This action cannot be undone.` : ''"
      confirm-label="Delete"
      :danger="true"
      @confirm="confirmDeleteVehicle"
    />
  </div>
</template>
