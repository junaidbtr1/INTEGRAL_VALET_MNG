<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useAuthStore } from '@/stores/authStore'
import { tenantService } from '@/services/tenantService'
import { useToast } from 'primevue/usetoast'
import { isAxiosError } from 'axios'
import BuildingSelector from '@/components/ui/BuildingSelector.vue'
import { useBuildingGuard } from '@/composables/useBuildingGuard'

const authStore = useAuthStore()
const toast = useToast()
const { validateBuilding } = useBuildingGuard()

type TabKey = 'general' | 'pricing'
const activeTab = ref<TabKey>('general')

const TABS = [
  { key: 'general', label: 'General', icon: 'pi pi-cog' },
  { key: 'pricing', label: 'Pricing', icon: 'pi pi-wallet' },
] as const

// ─── Fresh settings from API ───
const isLoading = ref(true)
const rawSettings = ref<Record<string, Record<string, unknown>>>({})

async function loadSettings() {
  isLoading.value = true
  try {
    const res = await tenantService.getSettings()
    rawSettings.value = (res.data ?? {}) as Record<string, Record<string, unknown>>
    initForms()
  } catch {
    rawSettings.value = (authStore.user?.tenant?.settings ?? {}) as Record<string, Record<string, unknown>>
    initForms()
  } finally {
    isLoading.value = false
  }
}

onMounted(() => loadSettings())

const parking = computed(() => rawSettings.value.parking ?? {})
const ticketConf = computed(() => rawSettings.value.ticket ?? {})
const opHours = computed(() => {
  const h = parking.value.operating_hours as Record<string, string> | undefined
  return { start: h?.start ?? '06:00', end: h?.end ?? '23:00' }
})

const tenant = computed(() => authStore.user?.tenant)

// ─── Pricing form ───
interface ITier {
  up_to_hours: number | null
  rate_per_hour: number
}

const pricingForm = ref({
  pricing_mode: 'flat' as 'flat' | 'tiered',
  rate_per_hour: 5,
  grace_period_minutes: 15,
  tax_rate: 0,
  op_start: '06:00',
  op_end: '23:00',
  overstay_after_hours: 24,
  overstay_surcharge: 0,
  lost_ticket_penalty: 0,
  auto_close_hours: 24,
})
const pricingTiers = ref<ITier[]>([
  { up_to_hours: null, rate_per_hour: 5 },
])
const pricingSaving = ref(false)

function addTier() {
  const lastIdx = pricingTiers.value.length - 1
  const last = pricingTiers.value[lastIdx]
  pricingTiers.value.splice(lastIdx, 0, { up_to_hours: null, rate_per_hour: last.rate_per_hour })
  pricingTiers.value[lastIdx].up_to_hours = (lastIdx > 0
    ? (pricingTiers.value[lastIdx - 1].up_to_hours ?? 0) + 1
    : 1)
}

function removeTier(index: number) {
  if (pricingTiers.value.length <= 1) return
  pricingTiers.value.splice(index, 1)
  pricingTiers.value[pricingTiers.value.length - 1].up_to_hours = null
}

function initForms() {
  const mode = (parking.value.pricing_mode as string) === 'tiered' ? 'tiered' : 'flat'
  const rawTiers = parking.value.pricing_tiers as ITier[] | undefined

  pricingForm.value = {
    pricing_mode: mode,
    rate_per_hour: Math.round((Number(parking.value.default_rate_per_hour) || 500) / 100),
    grace_period_minutes: parking.value.grace_period_minutes !== undefined
      ? Number(parking.value.grace_period_minutes)
      : 15,
    tax_rate: Number(parking.value.tax_rate) || 0,
    op_start: opHours.value.start,
    op_end: opHours.value.end,
    overstay_after_hours: Number(parking.value.overstay_after_hours) || 24,
    overstay_surcharge: Math.round((Number(parking.value.overstay_surcharge) || 0) / 100),
    lost_ticket_penalty: Math.round((Number(parking.value.lost_ticket_penalty) || 0) / 100),
    auto_close_hours: Number(ticketConf.value.auto_close_after_hours) || 24,
  }

  if (rawTiers && rawTiers.length > 0) {
    pricingTiers.value = rawTiers.map(t => ({
      up_to_hours: t.up_to_hours !== null && t.up_to_hours !== undefined ? Number(t.up_to_hours) : null,
      rate_per_hour: Math.round(Number(t.rate_per_hour) / 100),
    }))
  } else {
    pricingTiers.value = [{ up_to_hours: null, rate_per_hour: pricingForm.value.rate_per_hour }]
  }
}

function previewAmount(hours: number): number {
  if (pricingForm.value.pricing_mode === 'tiered' && pricingTiers.value.length > 0) {
    const sorted = [...pricingTiers.value].sort((a, b) => {
      const aL = a.up_to_hours ?? Infinity
      const bL = b.up_to_hours ?? Infinity
      return aL - bL
    })
    for (const tier of sorted) {
      if (tier.up_to_hours === null || hours <= tier.up_to_hours) {
        return hours * tier.rate_per_hour
      }
    }
    return hours * (sorted[sorted.length - 1]?.rate_per_hour ?? 0)
  }
  return hours * pricingForm.value.rate_per_hour
}

async function savePricing() {
  if (!await validateBuilding()) return
  pricingSaving.value = true
  try {
    await tenantService.updateSettings({
      parking: {
        pricing_mode: pricingForm.value.pricing_mode,
        pricing_tiers: pricingForm.value.pricing_mode === 'tiered'
          ? pricingTiers.value.map(t => ({
              up_to_hours: t.up_to_hours,
              rate_per_hour: Math.round(t.rate_per_hour * 100),
            }))
          : [],
        default_rate_per_hour: Math.round(pricingForm.value.rate_per_hour * 100),
        grace_period_minutes: pricingForm.value.grace_period_minutes,
        tax_rate: pricingForm.value.tax_rate,
        operating_hours: {
          start: pricingForm.value.op_start,
          end: pricingForm.value.op_end,
        },
        overstay_after_hours: pricingForm.value.overstay_after_hours,
        overstay_surcharge: Math.round(pricingForm.value.overstay_surcharge * 100),
        lost_ticket_penalty: Math.round(pricingForm.value.lost_ticket_penalty * 100),
      },
      ticket: {
        auto_close_after_hours: pricingForm.value.auto_close_hours,
      },
    })
    await authStore.fetchMe()
    await loadSettings()
    toast.add({ severity: 'success', summary: 'Saved', detail: 'Pricing settings updated', life: 3000 })
  } catch (error) {
    if (isAxiosError(error) && error.response?.data?.message) {
      toast.add({ severity: 'error', summary: 'Error', detail: error.response.data.message, life: 5000 })
    }
  } finally {
    pricingSaving.value = false
  }
}
</script>

<template>
  <div>
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-slate-900">Settings</h1>
      <p class="text-sm text-slate-500 mt-1">Configure your parking system</p>
    </div>

    <!-- Building selector for building owners -->
    <div v-if="authStore.isBuildingOwner" class="mb-4">
      <BuildingSelector />
    </div>

    <div class="bg-white rounded-xl border border-slate-200">
      <!-- Tabs -->
      <div class="flex border-b border-slate-200">
        <button
          v-for="tab in TABS"
          :key="tab.key"
          class="flex items-center gap-2 px-5 py-3.5 text-sm font-medium border-b-2 -mb-px transition-colors"
          :class="activeTab === tab.key
            ? 'border-indigo-600 text-indigo-600'
            : 'border-transparent text-slate-500 hover:text-slate-700'"
          @click="activeTab = tab.key as TabKey"
        >
          <i :class="tab.icon" class="text-xs" />
          {{ tab.label }}
        </button>
      </div>

      <!-- Loading skeleton -->
      <div v-if="isLoading" class="p-6 space-y-3 max-w-lg">
        <div v-for="i in 4" :key="i" class="h-10 bg-slate-100 rounded-lg animate-pulse" />
      </div>

      <template v-else>
        <!-- ── General ── -->
        <div v-if="activeTab === 'general'" class="p-6 space-y-4 max-w-lg">
          <div>
            <label class="block text-sm font-medium text-slate-600 mb-1">Name</label>
            <input :value="tenant?.name ?? '—'" disabled class="w-full h-10 border border-slate-200 rounded-lg px-4 text-sm bg-slate-50 text-slate-500" />
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-600 mb-1">Email</label>
            <input :value="tenant?.email ?? '—'" disabled class="w-full h-10 border border-slate-200 rounded-lg px-4 text-sm bg-slate-50 text-slate-500" />
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-600 mb-1">Phone</label>
            <input :value="tenant?.phone ?? 'Not set'" disabled class="w-full h-10 border border-slate-200 rounded-lg px-4 text-sm bg-slate-50 text-slate-500" />
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-600 mb-1">Status</label>
            <span
              class="inline-flex px-3 py-1 rounded-md text-sm font-semibold capitalize"
              :class="tenant?.status === 'active' ? 'bg-emerald-50 text-emerald-700' : 'bg-blue-50 text-blue-700'"
            >
              {{ tenant?.status }}
            </span>
          </div>
          <p class="text-xs text-slate-400 pt-1">Contact support to update organization details.</p>
        </div>

        <!-- ── Pricing ── -->
        <div v-if="activeTab === 'pricing'" class="p-6 max-w-lg">
          <p class="text-sm text-slate-500 mb-5">Changes take effect on the next parking session.</p>

          <div class="space-y-5">
            <!-- Pricing mode toggle -->
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1.5">Pricing Mode</label>
              <div class="flex gap-2">
                <button
                  type="button"
                  class="px-4 py-2 text-sm font-medium rounded-lg border transition-all"
                  :class="pricingForm.pricing_mode === 'flat'
                    ? 'bg-indigo-600 text-white border-indigo-600'
                    : 'bg-white text-slate-600 border-slate-300 hover:border-slate-400'"
                  @click="pricingForm.pricing_mode = 'flat'"
                >Single Rate</button>
                <button
                  type="button"
                  class="px-4 py-2 text-sm font-medium rounded-lg border transition-all"
                  :class="pricingForm.pricing_mode === 'tiered'
                    ? 'bg-indigo-600 text-white border-indigo-600'
                    : 'bg-white text-slate-600 border-slate-300 hover:border-slate-400'"
                  @click="pricingForm.pricing_mode = 'tiered'"
                >Tiered Rate</button>
              </div>
              <p class="text-xs text-slate-400 mt-1">
                <span v-if="pricingForm.pricing_mode === 'flat'">One fixed rate applied to all hours</span>
                <span v-else>Rate escalates based on total duration — higher tier applies to all hours</span>
              </p>
            </div>

            <!-- Flat rate -->
            <div v-if="pricingForm.pricing_mode === 'flat'">
              <label class="block text-sm font-medium text-slate-700 mb-1.5">Hourly Rate</label>
              <div class="flex items-center gap-2">
                <span class="text-slate-500 text-sm font-medium">$</span>
                <input
                  v-model.number="pricingForm.rate_per_hour"
                  type="number" min="0" step="0.01" placeholder="5.00"
                  class="flex-1 h-10 border border-slate-300 rounded-lg px-4 text-sm outline-none hover:border-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/10 transition-all"
                />
                <span class="text-slate-400 text-sm">/ hr</span>
              </div>
              <p class="text-xs text-slate-400 mt-1">Rate charged per hour of parking</p>
            </div>

            <!-- Tiered rate editor -->
            <div v-else>
              <label class="block text-sm font-medium text-slate-700 mb-2">Pricing Tiers</label>
              <div class="space-y-2">
                <div
                  v-for="(tier, idx) in pricingTiers"
                  :key="idx"
                  class="flex items-center gap-2"
                >
                  <span class="text-xs text-slate-400 w-16 shrink-0 text-right">
                    {{ idx === 0 ? 'Up to' : 'Up to' }}
                  </span>
                  <input
                    v-if="tier.up_to_hours !== null"
                    v-model.number="tier.up_to_hours"
                    type="number" min="1" placeholder="hrs"
                    class="w-20 h-9 border border-slate-300 rounded-lg px-3 text-sm outline-none hover:border-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/10 transition-all"
                  />
                  <span v-else class="w-20 h-9 flex items-center px-3 text-sm text-slate-400 border border-dashed border-slate-300 rounded-lg">
                    above
                  </span>
                  <span class="text-xs text-slate-400">hrs →</span>
                  <span class="text-slate-500 text-sm font-medium">$</span>
                  <input
                    v-model.number="tier.rate_per_hour"
                    type="number" min="0" step="0.01" placeholder="0.00"
                    class="w-24 h-9 border border-slate-300 rounded-lg px-3 text-sm outline-none hover:border-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/10 transition-all"
                  />
                  <span class="text-xs text-slate-400">/ hr</span>
                  <button
                    v-if="pricingTiers.length > 1"
                    type="button"
                    class="ml-auto p-1.5 text-slate-400 hover:text-red-500 hover:bg-red-50 rounded-md transition-colors"
                    title="Remove tier"
                    @click="removeTier(idx)"
                  >
                    <i class="pi pi-times text-xs" />
                  </button>
                </div>
              </div>
              <button
                type="button"
                class="mt-2 flex items-center gap-1.5 text-xs text-indigo-600 hover:text-indigo-700 font-medium"
                @click="addTier"
              >
                <i class="pi pi-plus text-[10px]" />
                Add tier
              </button>
              <p class="text-xs text-slate-400 mt-1.5">The last row (above) is the fallback for any duration beyond the last threshold</p>
            </div>

            <!-- Grace + Tax -->
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Grace Period (min)</label>
                <input
                  v-model.number="pricingForm.grace_period_minutes"
                  type="number" min="0" max="60"
                  class="w-full h-10 border border-slate-300 rounded-lg px-4 text-sm outline-none hover:border-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/10 transition-all"
                />
                <p class="text-xs text-slate-400 mt-1">Free minutes before charging</p>
              </div>
              <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Tax Rate (%)</label>
                <input
                  v-model.number="pricingForm.tax_rate"
                  type="number" min="0" max="100" step="0.1"
                  class="w-full h-10 border border-slate-300 rounded-lg px-4 text-sm outline-none hover:border-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/10 transition-all"
                />
                <p class="text-xs text-slate-400 mt-1">Applied on top of base rate</p>
              </div>
            </div>

            <!-- Operating hours -->
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1.5">Operating Hours</label>
              <div class="grid grid-cols-2 gap-4">
                <div class="flex items-center gap-2">
                  <span class="text-xs text-slate-400 w-10 shrink-0">Open</span>
                  <input
                    v-model="pricingForm.op_start" type="time"
                    class="flex-1 h-10 border border-slate-300 rounded-lg px-3 text-sm outline-none hover:border-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/10 transition-all"
                  />
                </div>
                <div class="flex items-center gap-2">
                  <span class="text-xs text-slate-400 w-10 shrink-0">Close</span>
                  <input
                    v-model="pricingForm.op_end" type="time"
                    class="flex-1 h-10 border border-slate-300 rounded-lg px-3 text-sm outline-none hover:border-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/10 transition-all"
                  />
                </div>
              </div>
            </div>

            <!-- Max duration -->
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1.5">Max Parking Duration (hours)</label>
              <input
                v-model.number="pricingForm.overstay_after_hours"
                type="number" min="1" max="168"
                class="w-full h-10 border border-slate-300 rounded-lg px-4 text-sm outline-none hover:border-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/10 transition-all"
              />
              <p class="text-xs text-slate-400 mt-1">After this, the overstay surcharge is added</p>
            </div>

            <!-- Overstay + Lost ticket -->
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Overstay Surcharge</label>
                <div class="flex items-center gap-2">
                  <span class="text-slate-500 text-sm font-medium">$</span>
                  <input
                    v-model.number="pricingForm.overstay_surcharge"
                    type="number" min="0" step="0.01"
                    class="flex-1 h-10 border border-slate-300 rounded-lg px-4 text-sm outline-none hover:border-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/10 transition-all"
                  />
                </div>
                <p class="text-xs text-slate-400 mt-1">Flat fee when max duration exceeded</p>
              </div>
              <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Lost Ticket Penalty</label>
                <div class="flex items-center gap-2">
                  <span class="text-slate-500 text-sm font-medium">$</span>
                  <input
                    v-model.number="pricingForm.lost_ticket_penalty"
                    type="number" min="0" step="0.01"
                    class="flex-1 h-10 border border-slate-300 rounded-lg px-4 text-sm outline-none hover:border-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/10 transition-all"
                  />
                </div>
                <p class="text-xs text-slate-400 mt-1">Flat fee for a lost/missing ticket</p>
              </div>
            </div>

            <!-- Auto-close -->
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1.5">Auto-close Tickets After (hours)</label>
              <input
                v-model.number="pricingForm.auto_close_hours"
                type="number" min="1" max="168"
                class="w-full h-10 border border-slate-300 rounded-lg px-4 text-sm outline-none hover:border-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/10 transition-all"
              />
              <p class="text-xs text-slate-400 mt-1">Active tickets are auto-closed after this many hours</p>
            </div>

            <!-- Preview -->
            <div class="bg-slate-50 rounded-xl p-4 border border-slate-200 text-sm space-y-1.5">
              <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Preview</p>
              <div class="flex justify-between text-slate-700">
                <span>1 hour</span>
                <span class="font-semibold">${{ previewAmount(1).toFixed(2) }}</span>
              </div>
              <div class="flex justify-between text-slate-700">
                <span>3 hours</span>
                <span class="font-semibold">${{ previewAmount(3).toFixed(2) }}</span>
              </div>
              <div class="flex justify-between text-slate-700">
                <span>6 hours</span>
                <span class="font-semibold">${{ previewAmount(6).toFixed(2) }}</span>
              </div>
              <div v-if="pricingForm.tax_rate > 0" class="flex justify-between text-slate-500 text-xs">
                <span>+ {{ pricingForm.tax_rate }}% tax on 3h</span>
                <span>${{ (previewAmount(3) * pricingForm.tax_rate / 100).toFixed(2) }}</span>
              </div>
              <div v-if="pricingForm.grace_period_minutes > 0" class="text-xs text-emerald-600">
                First {{ pricingForm.grace_period_minutes }} min free
              </div>
              <div v-if="pricingForm.overstay_surcharge > 0" class="text-xs text-amber-600">
                + ${{ pricingForm.overstay_surcharge.toFixed(2) }} overstay fee after {{ pricingForm.overstay_after_hours }}h
              </div>
              <div v-if="pricingForm.lost_ticket_penalty > 0" class="text-xs text-red-500">
                Lost ticket: ${{ pricingForm.lost_ticket_penalty.toFixed(2) }} penalty
              </div>
            </div>

            <!-- Save -->
            <div class="pt-1">
              <button
                :disabled="pricingSaving"
                class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg disabled:opacity-50 flex items-center gap-2 shadow-sm transition-all"
                @click="savePricing"
              >
                <i v-if="pricingSaving" class="pi pi-spinner pi-spin text-xs" />
                <i v-else class="pi pi-check text-xs" />
                Save Pricing
              </button>
            </div>
          </div>
        </div>
      </template>
    </div>
  </div>
</template>
