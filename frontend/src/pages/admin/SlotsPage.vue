<script setup lang="ts">
import { ref, onMounted, watch, computed } from 'vue'
import { tenantService } from '@/services/tenantService'
import type { IParkingSlot, ISlotSummary } from '@/services/tenantService'
import { useToast } from 'primevue/usetoast'
import { isAxiosError } from 'axios'
import BuildingSelector from '@/components/ui/BuildingSelector.vue'
import { useBuildingGuard } from '@/composables/useBuildingGuard'
import { usePermission } from '@/composables/usePermission'

const toast = useToast()
const { validateBuilding, isBuildingOwner, activeTenantId } = useBuildingGuard()
const { can } = usePermission()

const slots = ref<IParkingSlot[]>([])
const summary = ref<ISlotSummary | null>(null)
const isLoading = ref(true)
const floorFilter = ref('')
const statusFilter = ref('')
const showBulkDialog = ref(false)
const bulkFloor = ref('G')
const bulkZone = ref('A')
const bulkPrefix = ref('A')
const bulkStart = ref(1)
const bulkEnd = ref(10)
const bulkType = ref('standard')
const bulkLoading = ref(false)

const floors = computed(() => Array.from(new Set(slots.value.map(s => s.floor))).sort())

const filteredSlots = computed(() => {
  let result = slots.value
  if (floorFilter.value) result = result.filter(s => s.floor === floorFilter.value)
  if (statusFilter.value) result = result.filter(s => s.status === statusFilter.value)
  return result
})

async function fetchData() {
  isLoading.value = true
  try {
    const [slotsRes, summaryRes] = await Promise.all([tenantService.getSlots(), tenantService.getSlotSummary()])
    slots.value = slotsRes.data
    summary.value = summaryRes.data
  } finally {
    isLoading.value = false
  }
}

async function bulkCreate() {
  if (!await validateBuilding()) return
  bulkLoading.value = true
  try {
    const response = await tenantService.bulkCreateSlots({ floor: bulkFloor.value, zone: bulkZone.value, prefix: bulkPrefix.value, start: bulkStart.value, end: bulkEnd.value, slot_type: bulkType.value })
    toast.add({ severity: 'success', summary: 'Created', detail: `${response.data.created} slots created`, life: 3000 })
    showBulkDialog.value = false
    await fetchData()
  } catch (error) {
    if (isAxiosError(error) && error.response?.data?.message) toast.add({ severity: 'error', summary: 'Error', detail: error.response.data.message, life: 5000 })
  } finally {
    bulkLoading.value = false
  }
}

async function toggleStatus(slot: IParkingSlot) {
  if (slot.status === 'occupied') { toast.add({ severity: 'warn', summary: 'Occupied', detail: 'Close the ticket first', life: 3000 }); return }
  const newStatus = slot.status === 'available' ? 'maintenance' : 'available'
  try {
    await tenantService.updateSlotStatus(slot.id, newStatus)
    await fetchData()
  } catch (error) {
    if (isAxiosError(error) && error.response?.data?.message) toast.add({ severity: 'error', summary: 'Error', detail: error.response.data.message, life: 5000 })
  }
}

function getSlotBg(status: string): string {
  return { available: 'border-emerald-200 bg-emerald-50 hover:bg-emerald-100', occupied: 'border-red-200 bg-red-50', reserved: 'border-amber-200 bg-amber-50', maintenance: 'border-slate-300 bg-slate-100', out_of_service: 'border-slate-300 bg-slate-200 opacity-50' }[status] ?? 'border-slate-200'
}

function getDotColor(status: string): string {
  return { available: 'bg-emerald-500', occupied: 'bg-red-500', reserved: 'bg-amber-500', maintenance: 'bg-slate-400' }[status] ?? 'bg-slate-300'
}

watch(activeTenantId, (newId) => { if (isBuildingOwner.value && newId) fetchData() })
onMounted(fetchData)
</script>

<template>
  <div>
    <BuildingSelector mode="filter" />

    <div class="flex items-center justify-between mb-6">
      <div>
        <h1 class="text-2xl font-bold text-slate-900">Parking Slots</h1>
        <p class="text-sm text-slate-500 mt-1">Manage and monitor parking capacity</p>
      </div>
      <button v-if="can('slots.create')" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2.5 rounded-lg text-sm font-semibold flex items-center gap-2 shadow-sm transition-all" @click="showBulkDialog = true">
        <i class="pi pi-plus text-xs" /> Add Slots
      </button>
    </div>

    <!-- Summary -->
    <div v-if="summary" class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
      <div class="bg-white rounded-xl border border-slate-200 p-4"><p class="text-xs text-slate-400 font-medium">Total</p><p class="text-2xl font-bold text-slate-900 mt-1">{{ summary.total }}</p></div>
      <div class="bg-white rounded-xl border border-slate-200 p-4"><div class="flex items-center gap-2"><div class="w-2 h-2 rounded-full bg-emerald-500" /><p class="text-xs text-slate-400 font-medium">Available</p></div><p class="text-2xl font-bold text-emerald-600 mt-1">{{ summary.available }}</p></div>
      <div class="bg-white rounded-xl border border-slate-200 p-4"><div class="flex items-center gap-2"><div class="w-2 h-2 rounded-full bg-red-500" /><p class="text-xs text-slate-400 font-medium">Occupied</p></div><p class="text-2xl font-bold text-red-600 mt-1">{{ summary.occupied }}</p></div>
      <div class="bg-white rounded-xl border border-slate-200 p-4"><div class="flex items-center gap-2"><div class="w-2 h-2 rounded-full bg-amber-500" /><p class="text-xs text-slate-400 font-medium">Reserved</p></div><p class="text-2xl font-bold text-amber-600 mt-1">{{ summary.reserved }}</p></div>
      <div class="bg-white rounded-xl border border-slate-200 p-4"><p class="text-xs text-slate-400 font-medium">Occupancy</p><p class="text-2xl font-bold text-slate-900 mt-1">{{ summary.occupancy_percent }}%</p><div class="mt-2 h-1.5 bg-slate-100 rounded-full overflow-hidden"><div class="h-full rounded-full transition-all" :class="summary.occupancy_percent > 90 ? 'bg-red-500' : summary.occupancy_percent > 70 ? 'bg-amber-500' : 'bg-emerald-500'" :style="{ width: summary.occupancy_percent + '%' }" /></div></div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl border border-slate-200 p-4 mb-6 flex gap-4">
      <select v-model="floorFilter" class="h-10 border border-slate-300 rounded-lg px-4 text-sm outline-none focus:border-indigo-500 transition-all"><option value="">All Floors</option><option v-for="f in floors" :key="f" :value="f">Floor {{ f }}</option></select>
      <select v-model="statusFilter" class="h-10 border border-slate-300 rounded-lg px-4 text-sm outline-none focus:border-indigo-500 transition-all"><option value="">All Statuses</option><option value="available">Available</option><option value="occupied">Occupied</option><option value="maintenance">Maintenance</option></select>
    </div>

    <!-- Slot Grid -->
    <div class="bg-white rounded-xl border border-slate-200 p-6">
      <div v-if="isLoading" class="p-16 text-center"><i class="pi pi-spinner pi-spin text-3xl text-slate-300" /></div>
      <div v-else-if="filteredSlots.length === 0" class="p-16 text-center"><i class="pi pi-th-large text-5xl text-slate-200 mb-4" /><h3 class="text-lg font-semibold text-slate-600">No slots found</h3></div>
      <div v-else class="grid grid-cols-4 sm:grid-cols-6 md:grid-cols-8 lg:grid-cols-10 xl:grid-cols-12 gap-2">
        <button v-for="slot in filteredSlots" :key="slot.id" class="relative p-3 rounded-lg border text-center transition-all hover:shadow-md" :class="getSlotBg(slot.status)" :title="`${slot.slot_number} — ${slot.status}`" @click="toggleStatus(slot)">
          <div class="absolute top-1 right-1 w-2 h-2 rounded-full" :class="getDotColor(slot.status)" />
          <p class="text-xs font-bold text-slate-700">{{ slot.slot_number }}</p>
          <p class="text-[9px] text-slate-400 mt-0.5">{{ slot.floor }}</p>
        </button>
      </div>
      <div class="flex items-center gap-6 mt-6 pt-4 border-t border-slate-100">
        <div class="flex items-center gap-1.5"><div class="w-3 h-3 rounded-full bg-emerald-500" /><span class="text-xs text-slate-500">Available</span></div>
        <div class="flex items-center gap-1.5"><div class="w-3 h-3 rounded-full bg-red-500" /><span class="text-xs text-slate-500">Occupied</span></div>
        <div class="flex items-center gap-1.5"><div class="w-3 h-3 rounded-full bg-amber-500" /><span class="text-xs text-slate-500">Reserved</span></div>
        <div class="flex items-center gap-1.5"><div class="w-3 h-3 rounded-full bg-slate-400" /><span class="text-xs text-slate-500">Maintenance</span></div>
      </div>
    </div>

    <!-- Bulk Dialog -->
    <div v-if="showBulkDialog" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center z-50">
      <div class="bg-white rounded-2xl w-full max-w-md mx-4 shadow-2xl">
        <div class="p-6 border-b border-slate-100 flex items-center justify-between"><h2 class="text-lg font-semibold text-slate-900">Add Parking Slots</h2><button class="text-slate-400 hover:text-slate-600" @click="showBulkDialog = false"><i class="pi pi-times text-sm" /></button></div>
        <div class="p-6 space-y-4">
          <!-- Building selector for building owners -->
          <BuildingSelector />

          <div class="grid grid-cols-2 gap-4">
            <div><label class="block text-sm font-medium text-slate-700 mb-1.5">Floor</label><input v-model="bulkFloor" type="text" class="w-full h-10 border border-slate-300 rounded-lg px-4 text-sm outline-none focus:border-indigo-500 transition-all" /></div>
            <div><label class="block text-sm font-medium text-slate-700 mb-1.5">Zone</label><input v-model="bulkZone" type="text" class="w-full h-10 border border-slate-300 rounded-lg px-4 text-sm outline-none focus:border-indigo-500 transition-all" /></div>
          </div>
          <div><label class="block text-sm font-medium text-slate-700 mb-1.5">Prefix</label><input v-model="bulkPrefix" type="text" class="w-full h-10 border border-slate-300 rounded-lg px-4 text-sm outline-none focus:border-indigo-500 transition-all" /></div>
          <div class="grid grid-cols-2 gap-4">
            <div><label class="block text-sm font-medium text-slate-700 mb-1.5">From</label><input v-model.number="bulkStart" type="number" min="1" class="w-full h-10 border border-slate-300 rounded-lg px-4 text-sm outline-none focus:border-indigo-500 transition-all" /></div>
            <div><label class="block text-sm font-medium text-slate-700 mb-1.5">To</label><input v-model.number="bulkEnd" type="number" min="1" class="w-full h-10 border border-slate-300 rounded-lg px-4 text-sm outline-none focus:border-indigo-500 transition-all" /></div>
          </div>
          <div><label class="block text-sm font-medium text-slate-700 mb-1.5">Type</label><select v-model="bulkType" class="w-full h-10 border border-slate-300 rounded-lg px-4 text-sm outline-none focus:border-indigo-500 transition-all"><option value="standard">Standard</option><option value="compact">Compact</option><option value="large">Large</option><option value="vip">VIP</option><option value="disabled">Disabled</option></select></div>
          <p class="text-xs text-slate-400">Creates {{ bulkPrefix }}-{{ String(bulkStart).padStart(3, '0') }} to {{ bulkPrefix }}-{{ String(bulkEnd).padStart(3, '0') }}</p>
        </div>
        <div class="p-6 border-t border-slate-100 flex gap-3">
          <button class="flex-1 px-4 py-2.5 text-sm font-medium border border-slate-300 rounded-lg hover:bg-slate-50 transition-colors" @click="showBulkDialog = false">Cancel</button>
          <button :disabled="bulkLoading" class="flex-1 px-4 py-2.5 text-sm font-semibold bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 disabled:opacity-50 flex items-center justify-center gap-2 transition-all" @click="bulkCreate"><i v-if="bulkLoading" class="pi pi-spinner pi-spin text-xs" />Create {{ bulkEnd - bulkStart + 1 }} Slots</button>
        </div>
      </div>
    </div>
  </div>
</template>
