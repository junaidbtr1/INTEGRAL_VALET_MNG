<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { adminService } from '@/services/adminService'
import { useToast } from 'primevue/usetoast'
import { isAxiosError } from 'axios'
import type { IPlan } from '@/types'

const toast = useToast()
const plans = ref<IPlan[]>([])
const isLoading = ref(true)

// ─── Dialog state ───
const showDialog = ref(false)
const isEditing = ref(false)
const editingPlan = ref<IPlan | null>(null)
const isSaving = ref(false)
const showDeleteId = ref<number | null>(null)
const isDeleting = ref(false)
const formErrors = ref<Record<string, string>>({})

// ─── Form fields ───
const fName = ref('')
const fDescription = ref('')
const fPriceMonthly = ref<number | string>('')   // display dollars
const fPriceYearly = ref<number | string>('')     // display dollars
const fMaxSlots = ref<number | string>(50)
const fMaxStaff = ref<number | string>(5)
const fMaxTickets = ref<number | string>(200)
const fUnlimitedSlots = ref(false)
const fUnlimitedStaff = ref(false)
const fUnlimitedTickets = ref(false)
const fIsActive = ref(true)
const fFeatures = ref<Record<string, boolean>>({
  sms_notifications: false,
  custom_branding: false,
  advanced_reports: false,
  api_access: false,
  multi_floor: false,
  valet_tracking: false,
  priority_support: false,
})

const featureLabels: Record<string, string> = {
  sms_notifications: 'SMS Notifications',
  custom_branding: 'Custom Branding',
  advanced_reports: 'Advanced Reports',
  api_access: 'API Access',
  multi_floor: 'Multi-Floor Parking',
  valet_tracking: 'Valet GPS Tracking',
  priority_support: 'Priority Support',
}

async function fetchPlans() {
  isLoading.value = true
  try {
    const response = await adminService.getPlans()
    plans.value = response.data
  } finally {
    isLoading.value = false
  }
}

function resetForm() {
  fName.value = ''
  fDescription.value = ''
  fPriceMonthly.value = ''
  fPriceYearly.value = ''
  fMaxSlots.value = 50
  fMaxStaff.value = 5
  fMaxTickets.value = 200
  fUnlimitedSlots.value = false
  fUnlimitedStaff.value = false
  fUnlimitedTickets.value = false
  fIsActive.value = true
  fFeatures.value = {
    sms_notifications: false,
    custom_branding: false,
    advanced_reports: false,
    api_access: false,
    multi_floor: false,
    valet_tracking: false,
    priority_support: false,
  }
  formErrors.value = {}
}

function openCreate() {
  isEditing.value = false
  editingPlan.value = null
  resetForm()
  showDialog.value = true
}

function openEdit(plan: IPlan) {
  isEditing.value = true
  editingPlan.value = plan
  formErrors.value = {}
  fName.value = plan.name
  fDescription.value = plan.description ?? ''
  fPriceMonthly.value = plan.price_monthly / 100
  fPriceYearly.value = plan.price_yearly / 100
  fUnlimitedSlots.value = plan.max_slots >= 99999
  fUnlimitedStaff.value = plan.max_staff_users >= 99999
  fUnlimitedTickets.value = plan.max_tickets_per_day >= 99999
  fMaxSlots.value = fUnlimitedSlots.value ? 500 : plan.max_slots
  fMaxStaff.value = fUnlimitedStaff.value ? 25 : plan.max_staff_users
  fMaxTickets.value = fUnlimitedTickets.value ? 1000 : plan.max_tickets_per_day
  fIsActive.value = plan.is_active
  fFeatures.value = {
    sms_notifications: plan.features?.sms_notifications ?? false,
    custom_branding: plan.features?.custom_branding ?? false,
    advanced_reports: plan.features?.advanced_reports ?? false,
    api_access: plan.features?.api_access ?? false,
    multi_floor: plan.features?.multi_floor ?? false,
    valet_tracking: plan.features?.valet_tracking ?? false,
    priority_support: plan.features?.priority_support ?? false,
  }
  showDialog.value = true
}

function validate(): boolean {
  formErrors.value = {}
  if (!fName.value.trim()) { formErrors.value.name = 'Plan name is required'; return false }
  if (!fPriceMonthly.value && fPriceMonthly.value !== 0) { formErrors.value.price_monthly = 'Monthly price is required'; return false }
  if (!fPriceYearly.value && fPriceYearly.value !== 0) { formErrors.value.price_yearly = 'Yearly price is required'; return false }
  if (!fUnlimitedSlots.value && (!fMaxSlots.value || Number(fMaxSlots.value) < 1)) { formErrors.value.max_slots = 'Must be at least 1'; return false }
  if (!fUnlimitedStaff.value && (!fMaxStaff.value || Number(fMaxStaff.value) < 1)) { formErrors.value.max_staff = 'Must be at least 1'; return false }
  if (!fUnlimitedTickets.value && (!fMaxTickets.value || Number(fMaxTickets.value) < 1)) { formErrors.value.max_tickets = 'Must be at least 1'; return false }
  return true
}

async function savePlan() {
  if (!validate()) return
  isSaving.value = true
  try {
    const payload = {
      name: fName.value.trim(),
      description: fDescription.value.trim() || null,
      price_monthly: Math.round(Number(fPriceMonthly.value) * 100),
      price_yearly: Math.round(Number(fPriceYearly.value) * 100),
      currency: 'USD',
      max_slots: fUnlimitedSlots.value ? 99999 : Number(fMaxSlots.value),
      max_staff_users: fUnlimitedStaff.value ? 99999 : Number(fMaxStaff.value),
      max_tickets_per_day: fUnlimitedTickets.value ? 99999 : Number(fMaxTickets.value),
      features: { ...fFeatures.value },
      is_active: fIsActive.value,
    }

    if (isEditing.value && editingPlan.value) {
      await adminService.updatePlan(editingPlan.value.id, payload)
      toast.add({ severity: 'success', summary: 'Updated', detail: 'Plan updated successfully', life: 3000 })
    } else {
      await adminService.createPlan(payload)
      toast.add({ severity: 'success', summary: 'Created', detail: 'Plan created successfully', life: 3000 })
    }
    showDialog.value = false
    await fetchPlans()
  } catch (error) {
    if (isAxiosError(error) && error.response?.status === 422) {
      const errs = error.response.data.errors ?? {}
      for (const key in errs) formErrors.value[key] = errs[key][0]
    } else {
      toast.add({ severity: 'error', summary: 'Error', detail: 'Failed to save plan', life: 5000 })
    }
  } finally {
    isSaving.value = false
  }
}

async function deletePlan(id: number) {
  isDeleting.value = true
  try {
    await adminService.deletePlan(id)
    toast.add({ severity: 'success', summary: 'Deleted', detail: 'Plan deleted', life: 3000 })
    showDeleteId.value = null
    await fetchPlans()
  } catch (error) {
    if (isAxiosError(error) && error.response?.data?.message) {
      toast.add({ severity: 'error', summary: 'Error', detail: error.response.data.message, life: 5000 })
    }
  } finally {
    isDeleting.value = false
  }
}

function formatUSD(cents: number): string {
  return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD', minimumFractionDigits: 0 }).format(cents / 100)
}

function limitLabel(val: number): string {
  return val >= 99999 ? 'Unlimited' : val.toLocaleString()
}

onMounted(fetchPlans)
</script>

<template>
  <div>
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Plans</h1>
        <p class="text-sm text-gray-500 mt-1">Manage subscription plans and their limits</p>
      </div>
      <button
        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium flex items-center gap-2"
        @click="openCreate"
      >
        <i class="pi pi-plus" /> New Plan
      </button>
    </div>

    <!-- Loading skeletons -->
    <div v-if="isLoading" class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <div v-for="i in 3" :key="i" class="bg-white rounded-xl p-6 shadow-sm border border-gray-100 animate-pulse">
        <div class="h-5 bg-gray-200 rounded w-24 mb-3" />
        <div class="h-4 bg-gray-200 rounded w-40 mb-4" />
        <div class="h-9 bg-gray-200 rounded w-28 mb-1" />
        <div class="h-3 bg-gray-200 rounded w-24 mb-5" />
        <div class="space-y-2">
          <div v-for="j in 5" :key="j" class="h-4 bg-gray-200 rounded" />
        </div>
      </div>
    </div>

    <!-- Plan cards -->
    <div v-else class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <div
        v-for="plan in plans"
        :key="plan.id"
        class="bg-white rounded-xl shadow-sm border overflow-hidden flex flex-col"
        :class="plan.slug === 'professional' ? 'border-blue-400 ring-2 ring-blue-100' : 'border-gray-200'"
      >
        <!-- Card header -->
        <div class="px-6 pt-5 pb-4 border-b border-gray-100">
          <div class="flex items-start justify-between gap-2 mb-3">
            <div>
              <div v-if="plan.slug === 'professional'" class="inline-flex items-center gap-1 text-[11px] font-semibold text-blue-600 bg-blue-50 px-2 py-0.5 rounded-full mb-1.5">
                <i class="pi pi-star-fill text-[10px]" /> Most Popular
              </div>
              <h3 class="text-lg font-bold text-gray-900">{{ plan.name }}</h3>
              <p class="text-xs text-gray-400 mt-0.5">{{ plan.description }}</p>
            </div>
            <span
              class="shrink-0 text-[11px] font-medium px-2 py-0.5 rounded-full"
              :class="plan.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'"
            >
              {{ plan.is_active ? 'Active' : 'Inactive' }}
            </span>
          </div>

          <!-- Pricing -->
          <div>
            <div class="flex items-baseline gap-1">
              <span class="text-3xl font-bold text-gray-900">{{ formatUSD(plan.price_monthly) }}</span>
              <span class="text-sm text-gray-400">/mo</span>
            </div>
            <p class="text-xs text-gray-400 mt-0.5">{{ formatUSD(plan.price_yearly) }}/year <span class="text-green-600 font-medium">(save {{ Math.round((1 - plan.price_yearly / (plan.price_monthly * 12)) * 100) }}%)</span></p>
          </div>
        </div>

        <!-- Limits -->
        <div class="px-6 py-4 border-b border-gray-100 space-y-2">
          <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider mb-2">Limits</p>
          <div class="flex items-center justify-between text-sm">
            <span class="text-gray-500 flex items-center gap-2"><i class="pi pi-th-large text-gray-300 text-xs" /> Parking Slots</span>
            <span class="font-semibold text-gray-800">{{ limitLabel(plan.max_slots) }}</span>
          </div>
          <div class="flex items-center justify-between text-sm">
            <span class="text-gray-500 flex items-center gap-2"><i class="pi pi-users text-gray-300 text-xs" /> Staff Users</span>
            <span class="font-semibold text-gray-800">{{ limitLabel(plan.max_staff_users) }}</span>
          </div>
          <div class="flex items-center justify-between text-sm">
            <span class="text-gray-500 flex items-center gap-2"><i class="pi pi-ticket text-gray-300 text-xs" /> Tickets / Day</span>
            <span class="font-semibold text-gray-800">{{ limitLabel(plan.max_tickets_per_day) }}</span>
          </div>
        </div>

        <!-- Features -->
        <div class="px-6 py-4 flex-1">
          <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider mb-2">Features</p>
          <ul class="space-y-1.5">
            <li
              v-for="(enabled, feature) in plan.features ?? {}"
              :key="String(feature)"
              class="flex items-center gap-2 text-sm"
            >
              <i :class="enabled ? 'pi pi-check-circle text-green-500' : 'pi pi-times-circle text-gray-200'" class="text-sm shrink-0" />
              <span :class="enabled ? 'text-gray-700' : 'text-gray-300'">
                {{ featureLabels[String(feature)] ?? String(feature).replace(/_/g, ' ') }}
              </span>
            </li>
          </ul>
        </div>

        <!-- Footer -->
        <div class="px-6 pb-5 pt-3 border-t border-gray-100 flex items-center justify-between">
          <p class="text-xs text-gray-400">{{ plan.tenants_count ?? 0 }} tenant{{ plan.tenants_count !== 1 ? 's' : '' }}</p>
          <div class="flex gap-2">
            <button
              class="px-3 py-1.5 text-xs font-medium border border-gray-300 rounded-lg hover:bg-gray-50 text-gray-600 transition-colors"
              @click="openEdit(plan)"
            >
              <i class="pi pi-pencil mr-1" /> Edit
            </button>
            <button
              class="px-3 py-1.5 text-xs font-medium border border-red-200 rounded-lg hover:bg-red-50 text-red-500 transition-colors"
              :disabled="(plan.tenants_count ?? 0) > 0"
              :title="(plan.tenants_count ?? 0) > 0 ? 'Cannot delete — has active tenants' : ''"
              :class="(plan.tenants_count ?? 0) > 0 ? 'opacity-40 cursor-not-allowed' : ''"
              @click="(plan.tenants_count ?? 0) === 0 && (showDeleteId = plan.id)"
            >
              <i class="pi pi-trash" />
            </button>
          </div>
        </div>
      </div>

      <!-- Empty state -->
      <div v-if="plans.length === 0" class="col-span-3 py-20 text-center">
        <i class="pi pi-box text-5xl text-gray-200 mb-4" />
        <p class="text-gray-400">No plans yet. Create your first plan.</p>
      </div>
    </div>

    <!-- ═══ Create / Edit Dialog ═══ -->
    <div v-if="showDialog" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-2xl w-full max-w-xl shadow-2xl max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b border-gray-100 flex items-center justify-between sticky top-0 bg-white z-10">
          <h2 class="text-lg font-semibold text-gray-900">{{ isEditing ? 'Edit Plan' : 'Create Plan' }}</h2>
          <button class="text-gray-400 hover:text-gray-600" @click="showDialog = false">
            <i class="pi pi-times" />
          </button>
        </div>

        <div class="p-6 space-y-5">
          <!-- Name + Active -->
          <div class="flex gap-4">
            <div class="flex-1">
              <label class="block text-sm font-medium text-gray-700 mb-1.5">Plan Name <span class="text-red-500">*</span></label>
              <input
                v-model="fName"
                type="text"
                placeholder="e.g. Starter"
                class="w-full border rounded-lg px-4 py-2 text-sm outline-none focus:border-blue-500"
                :class="formErrors.name ? 'border-red-400' : 'border-gray-300'"
              />
              <p v-if="formErrors.name" class="mt-1 text-xs text-red-500">{{ formErrors.name }}</p>
            </div>
            <div class="flex items-end pb-2 gap-2">
              <input id="plan_active" v-model="fIsActive" type="checkbox" class="w-4 h-4 rounded border-gray-300" />
              <label for="plan_active" class="text-sm font-medium text-gray-700 whitespace-nowrap">Active</label>
            </div>
          </div>

          <!-- Description -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Description</label>
            <input v-model="fDescription" type="text" placeholder="Short description..." class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm outline-none focus:border-blue-500" />
          </div>

          <!-- Pricing -->
          <div>
            <p class="text-sm font-semibold text-gray-700 mb-3">Pricing (USD)</p>
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-medium text-gray-500 mb-1.5">Monthly Price <span class="text-red-500">*</span></label>
                <div class="relative">
                  <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">$</span>
                  <input
                    v-model="fPriceMonthly"
                    type="number"
                    min="0"
                    step="0.01"
                    placeholder="49.99"
                    class="w-full border rounded-lg pl-7 pr-4 py-2 text-sm outline-none focus:border-blue-500"
                    :class="formErrors.price_monthly ? 'border-red-400' : 'border-gray-300'"
                  />
                </div>
                <p v-if="formErrors.price_monthly" class="mt-1 text-xs text-red-500">{{ formErrors.price_monthly }}</p>
              </div>
              <div>
                <label class="block text-xs font-medium text-gray-500 mb-1.5">Yearly Price <span class="text-red-500">*</span></label>
                <div class="relative">
                  <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">$</span>
                  <input
                    v-model="fPriceYearly"
                    type="number"
                    min="0"
                    step="0.01"
                    placeholder="499.00"
                    class="w-full border rounded-lg pl-7 pr-4 py-2 text-sm outline-none focus:border-blue-500"
                    :class="formErrors.price_yearly ? 'border-red-400' : 'border-gray-300'"
                  />
                </div>
                <p v-if="formErrors.price_yearly" class="mt-1 text-xs text-red-500">{{ formErrors.price_yearly }}</p>
              </div>
            </div>
          </div>

          <!-- Limits -->
          <div>
            <p class="text-sm font-semibold text-gray-700 mb-3">Plan Limits</p>
            <div class="space-y-3">
              <!-- Parking Slots -->
              <div class="flex items-center gap-3">
                <div class="flex-1">
                  <label class="block text-xs font-medium text-gray-500 mb-1.5">Parking Slots</label>
                  <input
                    v-model.number="fMaxSlots"
                    type="number"
                    min="1"
                    :disabled="fUnlimitedSlots"
                    class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm outline-none focus:border-blue-500 disabled:bg-gray-50 disabled:text-gray-400"
                    :class="formErrors.max_slots ? 'border-red-400' : ''"
                  />
                  <p v-if="formErrors.max_slots" class="mt-1 text-xs text-red-500">{{ formErrors.max_slots }}</p>
                </div>
                <div class="flex items-center gap-1.5 pt-5">
                  <input :id="`unlimited-slots`" v-model="fUnlimitedSlots" type="checkbox" class="w-4 h-4 rounded border-gray-300" />
                  <label :for="`unlimited-slots`" class="text-xs text-gray-500 whitespace-nowrap">Unlimited</label>
                </div>
              </div>

              <!-- Staff Users -->
              <div class="flex items-center gap-3">
                <div class="flex-1">
                  <label class="block text-xs font-medium text-gray-500 mb-1.5">Staff Users</label>
                  <input
                    v-model.number="fMaxStaff"
                    type="number"
                    min="1"
                    :disabled="fUnlimitedStaff"
                    class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm outline-none focus:border-blue-500 disabled:bg-gray-50 disabled:text-gray-400"
                    :class="formErrors.max_staff ? 'border-red-400' : ''"
                  />
                  <p v-if="formErrors.max_staff" class="mt-1 text-xs text-red-500">{{ formErrors.max_staff }}</p>
                </div>
                <div class="flex items-center gap-1.5 pt-5">
                  <input :id="`unlimited-staff`" v-model="fUnlimitedStaff" type="checkbox" class="w-4 h-4 rounded border-gray-300" />
                  <label :for="`unlimited-staff`" class="text-xs text-gray-500 whitespace-nowrap">Unlimited</label>
                </div>
              </div>

              <!-- Tickets / Day -->
              <div class="flex items-center gap-3">
                <div class="flex-1">
                  <label class="block text-xs font-medium text-gray-500 mb-1.5">Tickets per Day</label>
                  <input
                    v-model.number="fMaxTickets"
                    type="number"
                    min="1"
                    :disabled="fUnlimitedTickets"
                    class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm outline-none focus:border-blue-500 disabled:bg-gray-50 disabled:text-gray-400"
                    :class="formErrors.max_tickets ? 'border-red-400' : ''"
                  />
                  <p v-if="formErrors.max_tickets" class="mt-1 text-xs text-red-500">{{ formErrors.max_tickets }}</p>
                </div>
                <div class="flex items-center gap-1.5 pt-5">
                  <input :id="`unlimited-tickets`" v-model="fUnlimitedTickets" type="checkbox" class="w-4 h-4 rounded border-gray-300" />
                  <label :for="`unlimited-tickets`" class="text-xs text-gray-500 whitespace-nowrap">Unlimited</label>
                </div>
              </div>
            </div>
          </div>

          <!-- Features -->
          <div>
            <p class="text-sm font-semibold text-gray-700 mb-3">Features</p>
            <div class="grid grid-cols-2 gap-2">
              <label
                v-for="(label, key) in featureLabels"
                :key="key"
                class="flex items-center gap-2.5 p-2.5 rounded-lg border cursor-pointer transition-colors select-none"
                :class="fFeatures[key] ? 'border-blue-200 bg-blue-50' : 'border-gray-200 hover:bg-gray-50'"
              >
                <input v-model="fFeatures[key]" type="checkbox" class="w-4 h-4 rounded border-gray-300 text-blue-600" />
                <span class="text-sm" :class="fFeatures[key] ? 'text-blue-700 font-medium' : 'text-gray-600'">{{ label }}</span>
              </label>
            </div>
          </div>
        </div>

        <div class="p-6 border-t border-gray-100 flex justify-end gap-3 sticky bottom-0 bg-white">
          <button class="px-4 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50" @click="showDialog = false">Cancel</button>
          <button
            :disabled="isSaving"
            class="px-4 py-2 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 flex items-center gap-2"
            @click="savePlan"
          >
            <i v-if="isSaving" class="pi pi-spinner pi-spin" />
            {{ isEditing ? 'Save Changes' : 'Create Plan' }}
          </button>
        </div>
      </div>
    </div>

    <!-- ═══ Delete Confirmation ═══ -->
    <div v-if="showDeleteId !== null" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
      <div class="bg-white rounded-2xl w-full max-w-sm mx-4 shadow-2xl p-6">
        <div class="flex items-start gap-4 mb-5">
          <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center shrink-0">
            <i class="pi pi-trash text-red-600" />
          </div>
          <div>
            <h3 class="text-base font-semibold text-gray-900">Delete Plan</h3>
            <p class="text-sm text-gray-500 mt-1">This action cannot be undone. Are you sure?</p>
          </div>
        </div>
        <div class="flex justify-end gap-3">
          <button class="px-4 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50" @click="showDeleteId = null">Cancel</button>
          <button
            :disabled="isDeleting"
            class="px-4 py-2 text-sm bg-red-600 text-white rounded-lg hover:bg-red-700 disabled:opacity-50 flex items-center gap-2"
            @click="deletePlan(showDeleteId!)"
          >
            <i v-if="isDeleting" class="pi pi-spinner pi-spin" />
            Delete
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
