<script setup lang="ts">
import { ref, onMounted, watch } from 'vue'
import AppTable from '@/components/ui/AppTable.vue'
import AppConfirmDialog from '@/components/ui/AppConfirmDialog.vue'
import BuildingSelector from '@/components/ui/BuildingSelector.vue'
import { tenantService } from '@/services/tenantService'
import type { ICoupon } from '@/services/tenantService'
import { useToast } from 'primevue/usetoast'
import { isAxiosError } from 'axios'
import { useBuildingGuard } from '@/composables/useBuildingGuard'
import { usePermission } from '@/composables/usePermission'

const { validateBuilding, isBuildingOwner, activeTenantId } = useBuildingGuard()
const { can } = usePermission()

const toast = useToast()
const coupons = ref<ICoupon[]>([])
const isLoading = ref(true)
const showDialog = ref(false)
const showDeleteDialog = ref(false)
const pendingDelete = ref<ICoupon | null>(null)
const isEditing = ref(false)
const editingId = ref<number | null>(null)
const isSaving = ref(false)

const form = ref({ code: '', type: 'percentage', value: 0, min_amount: 0, max_discount: 0, usage_limit: 0, per_user_limit: 1, valid_from: '', valid_until: '', description: '', is_active: true })
const formErrors = ref<Record<string, string>>({})

const couponTypes = [
  { value: 'percentage', label: 'Percentage Discount' },
  { value: 'fixed_amount', label: 'Fixed Amount Off' },
  { value: 'free_hours', label: 'Free Hours' },
  { value: 'full_waiver', label: 'Full Waiver (100% free)' },
]

async function fetchCoupons() {
  isLoading.value = true
  try { const response = await tenantService.getCoupons(); coupons.value = response.data }
  finally { isLoading.value = false }
}

function openCreate() {
  isEditing.value = false; editingId.value = null; formErrors.value = {}
  form.value = { code: '', type: 'percentage', value: 0, min_amount: 0, max_discount: 0, usage_limit: 0, per_user_limit: 1, valid_from: '', valid_until: '', description: '', is_active: true }
  showDialog.value = true
}

function openEdit(c: ICoupon) {
  isEditing.value = true; editingId.value = c.id; formErrors.value = {}
  form.value = { code: c.code, type: c.type, value: c.value, min_amount: c.min_amount, max_discount: c.max_discount ?? 0, usage_limit: c.usage_limit ?? 0, per_user_limit: c.per_user_limit, valid_from: c.valid_from?.split('T')[0] ?? '', valid_until: c.valid_until?.split('T')[0] ?? '', description: c.description ?? '', is_active: c.is_active }
  showDialog.value = true
}

async function saveCoupon() {
  formErrors.value = {}
  if (!await validateBuilding()) return
  if (!form.value.code) { formErrors.value.code = 'Code is required'; return }
  if (!form.value.value) { formErrors.value.value = 'Value is required'; return }
  isSaving.value = true
  try {
    const data: Record<string, unknown> = { ...form.value }
    if (!data.valid_from) delete data.valid_from
    if (!data.valid_until) delete data.valid_until
    if (!data.usage_limit) delete data.usage_limit
    if (!data.max_discount) delete data.max_discount
    if (isEditing.value && editingId.value) {
      delete data.code
      await tenantService.updateCoupon(editingId.value, data)
      toast.add({ severity: 'success', summary: 'Updated', detail: 'Coupon updated', life: 3000 })
    } else {
      await tenantService.createCoupon(data)
      toast.add({ severity: 'success', summary: 'Created', detail: 'Coupon created', life: 3000 })
    }
    showDialog.value = false
    await fetchCoupons()
  } catch (error) {
    if (isAxiosError(error) && error.response?.status === 422) {
      const errs = error.response.data.errors ?? {}
      for (const key in errs) formErrors.value[key] = errs[key][0]
    } else if (isAxiosError(error)) toast.add({ severity: 'error', summary: 'Error', detail: error.response?.data?.message ?? 'Failed', life: 5000 })
  } finally { isSaving.value = false }
}

function openDeleteDialog(c: ICoupon) {
  pendingDelete.value = c
  showDeleteDialog.value = true
}

async function confirmDelete() {
  if (!pendingDelete.value) return
  try { await tenantService.deleteCoupon(pendingDelete.value.id); toast.add({ severity: 'success', summary: 'Deleted', life: 3000 }); await fetchCoupons() }
  catch { toast.add({ severity: 'error', summary: 'Error', detail: 'Failed to delete', life: 5000 }) }
  finally { pendingDelete.value = null }
}

function formatValue(c: ICoupon): string {
  if (c.type === 'percentage') return `${c.value / 100}%`
  if (c.type === 'fixed_amount') return `$${(c.value / 100).toFixed(2)}`
  if (c.type === 'free_hours') return `${c.value} hrs free`
  return 'Full waiver'
}

watch(activeTenantId, (newId) => { if (isBuildingOwner.value && newId) fetchCoupons() })
onMounted(fetchCoupons)
</script>

<template>
  <div>
    <BuildingSelector mode="filter" />

    <div class="flex items-center justify-between mb-6">
      <div><h1 class="text-2xl font-bold text-slate-900">Coupons</h1><p class="text-sm text-slate-500 mt-1">Manage discount coupons</p></div>
      <button v-if="can('coupons.create')" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2.5 rounded-lg text-sm font-semibold flex items-center gap-2 shadow-sm transition-all" @click="openCreate"><i class="pi pi-plus text-xs" />New Coupon</button>
    </div>

    <div class="bg-white rounded-xl border border-slate-200">
      <div v-if="isLoading" class="p-16 text-center"><i class="pi pi-spinner pi-spin text-3xl text-slate-300" /></div>
      <div v-else-if="coupons.length === 0" class="p-16 text-center"><i class="pi pi-tag text-5xl text-slate-200 mb-4" /><h3 class="text-lg font-semibold text-slate-600">No coupons yet</h3><p class="text-sm text-slate-400 mt-1">Create your first discount coupon</p></div>
      <template v-else>
        <AppTable min-width="min-w-[560px]">
          <thead><tr class="border-b border-slate-100">
            <th class="text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider px-6 py-3">Code</th>
            <th class="hidden sm:table-cell text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider px-6 py-3">Type</th>
            <th class="text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider px-6 py-3">Value</th>
            <th class="hidden md:table-cell text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider px-6 py-3">Usage</th>
            <th class="hidden lg:table-cell text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider px-6 py-3">Valid</th>
            <th class="text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider px-6 py-3">Status</th>
            <th class="text-right text-[11px] font-semibold text-slate-400 uppercase tracking-wider px-6 py-3">Actions</th>
          </tr></thead>
          <tbody class="divide-y divide-slate-100">
            <tr v-for="c in coupons" :key="c.id" class="hover:bg-slate-50/50 transition-colors">
              <td class="px-6 py-4 text-sm font-bold text-slate-900 font-mono">{{ c.code }}</td>
              <td class="hidden sm:table-cell px-6 py-4 text-sm text-slate-600 capitalize">{{ c.type.replace('_', ' ') }}</td>
              <td class="px-6 py-4 text-sm font-semibold text-indigo-600">{{ formatValue(c) }}</td>
              <td class="hidden md:table-cell px-6 py-4 text-sm text-slate-600">{{ c.times_used }}{{ c.usage_limit ? ` / ${c.usage_limit}` : '' }}</td>
              <td class="hidden lg:table-cell px-6 py-4 text-xs text-slate-500">{{ c.valid_from ? new Date(c.valid_from).toLocaleDateString() : 'Always' }} — {{ c.valid_until ? new Date(c.valid_until).toLocaleDateString() : 'Always' }}</td>
              <td class="px-6 py-4"><span class="inline-flex px-2.5 py-1 rounded-md text-[11px] font-semibold" :class="c.is_active ? 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/20' : 'bg-slate-100 text-slate-500'">{{ c.is_active ? 'Active' : 'Inactive' }}</span></td>
              <td class="px-6 py-4 text-right">
                <button v-if="can('coupons.update')" class="p-1.5 rounded-md text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 transition-colors" @click="openEdit(c)"><i class="pi pi-pencil text-sm" /></button>
                <button v-if="can('coupons.delete')" class="p-1.5 rounded-md text-slate-400 hover:text-red-600 hover:bg-red-50 transition-colors" @click="openDeleteDialog(c)"><i class="pi pi-trash text-sm" /></button>
              </td>
            </tr>
          </tbody>
        </AppTable>
      </template>
    </div>

    <AppConfirmDialog
      v-model="showDeleteDialog"
      title="Delete Coupon"
      :message="pendingDelete ? `Delete coupon &quot;${pendingDelete.code}&quot;? This action cannot be undone.` : ''"
      confirm-label="Delete"
      :danger="true"
      @confirm="confirmDelete"
    />

    <!-- Dialog -->
    <div v-if="showDialog" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center z-50">
      <div class="bg-white rounded-2xl w-full max-w-lg mx-4 shadow-2xl max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b border-slate-100 flex items-center justify-between sticky top-0 bg-white z-10"><h2 class="text-lg font-semibold text-slate-900">{{ isEditing ? 'Edit Coupon' : 'New Coupon' }}</h2><button class="text-slate-400 hover:text-slate-600" @click="showDialog = false"><i class="pi pi-times text-sm" /></button></div>
        <div class="p-6 space-y-4">
          <!-- Building selector for building owners -->
          <BuildingSelector />

          <div v-if="!isEditing"><label class="block text-sm font-medium text-slate-700 mb-1.5">Code <span class="text-red-500">*</span></label><input v-model="form.code" type="text" placeholder="e.g. 20OFF" class="w-full h-10 border rounded-lg px-4 text-sm uppercase outline-none transition-all" :class="formErrors.code ? 'border-red-400 bg-red-50/50' : 'border-slate-300 focus:border-indigo-500'" /><p v-if="formErrors.code" class="mt-1 text-[13px] text-red-500">{{ formErrors.code }}</p><p class="mt-1 text-xs text-slate-400">Uppercase letters and numbers only</p></div>
          <div v-else class="bg-slate-50 rounded-lg px-4 py-2 text-sm font-mono font-bold text-slate-700">{{ form.code }}</div>
          <div><label class="block text-sm font-medium text-slate-700 mb-1.5">Type</label><select v-model="form.type" class="w-full h-10 border border-slate-300 rounded-lg px-4 text-sm outline-none focus:border-indigo-500 transition-all"><option v-for="t in couponTypes" :key="t.value" :value="t.value">{{ t.label }}</option></select></div>
          <div><label class="block text-sm font-medium text-slate-700 mb-1.5">Value <span class="text-red-500">*</span> <span class="text-xs text-slate-400 font-normal">{{ form.type === 'percentage' ? '(e.g. 2000 = 20%)' : '(in paisa)' }}</span></label><input v-model.number="form.value" type="number" class="w-full h-10 border rounded-lg px-4 text-sm outline-none transition-all" :class="formErrors.value ? 'border-red-400 bg-red-50/50' : 'border-slate-300 focus:border-indigo-500'" /><p v-if="formErrors.value" class="mt-1 text-[13px] text-red-500">{{ formErrors.value }}</p></div>
          <div class="grid grid-cols-2 gap-4">
            <div><label class="block text-sm font-medium text-slate-700 mb-1.5">Min Amount</label><input v-model.number="form.min_amount" type="number" class="w-full h-10 border border-slate-300 rounded-lg px-4 text-sm outline-none focus:border-indigo-500 transition-all" /></div>
            <div><label class="block text-sm font-medium text-slate-700 mb-1.5">Max Discount</label><input v-model.number="form.max_discount" type="number" class="w-full h-10 border border-slate-300 rounded-lg px-4 text-sm outline-none focus:border-indigo-500 transition-all" /></div>
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div><label class="block text-sm font-medium text-slate-700 mb-1.5">Usage Limit</label><input v-model.number="form.usage_limit" type="number" class="w-full h-10 border border-slate-300 rounded-lg px-4 text-sm outline-none focus:border-indigo-500 transition-all" placeholder="0 = unlimited" /></div>
            <div><label class="block text-sm font-medium text-slate-700 mb-1.5">Per User Limit</label><input v-model.number="form.per_user_limit" type="number" class="w-full h-10 border border-slate-300 rounded-lg px-4 text-sm outline-none focus:border-indigo-500 transition-all" /></div>
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div><label class="block text-sm font-medium text-slate-700 mb-1.5">Valid From</label><input v-model="form.valid_from" type="date" class="w-full h-10 border border-slate-300 rounded-lg px-4 text-sm outline-none focus:border-indigo-500 transition-all" /></div>
            <div><label class="block text-sm font-medium text-slate-700 mb-1.5">Valid Until</label><input v-model="form.valid_until" type="date" class="w-full h-10 border border-slate-300 rounded-lg px-4 text-sm outline-none focus:border-indigo-500 transition-all" /></div>
          </div>
          <div><label class="block text-sm font-medium text-slate-700 mb-1.5">Description</label><input v-model="form.description" type="text" class="w-full h-10 border border-slate-300 rounded-lg px-4 text-sm outline-none focus:border-indigo-500 transition-all" /></div>
          <label v-if="isEditing" class="flex items-center gap-2 cursor-pointer"><input v-model="form.is_active" type="checkbox" class="w-4 h-4 rounded border-slate-300 text-indigo-600" /><span class="text-sm font-medium text-slate-700">Active</span></label>
        </div>
        <div class="p-6 border-t border-slate-100 flex gap-3 sticky bottom-0 bg-white">
          <button class="flex-1 px-4 py-2.5 text-sm font-medium border border-slate-300 rounded-lg hover:bg-slate-50 transition-colors" @click="showDialog = false">Cancel</button>
          <button :disabled="isSaving" class="flex-1 px-4 py-2.5 text-sm font-semibold bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 disabled:opacity-50 flex items-center justify-center gap-2 transition-all" @click="saveCoupon"><i v-if="isSaving" class="pi pi-spinner pi-spin text-xs" />{{ isEditing ? 'Update' : 'Create' }}</button>
        </div>
      </div>
    </div>
  </div>
</template>
