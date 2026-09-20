<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted, nextTick, watch } from 'vue'
import printJS from 'print-js'
import QRCode from 'qrcode'
import { tenantService } from '@/services/tenantService'
import type { ITicket, IParkingSlot, IPricing } from '@/services/tenantService'
import { useToast } from 'primevue/usetoast'
import { isAxiosError } from 'axios'
import { formatCurrency, formatDate } from '@/utils/formatters'
import BuildingSelector from '@/components/ui/BuildingSelector.vue'
import { useBuildingGuard } from '@/composables/useBuildingGuard'
import { usePermission } from '@/composables/usePermission'

const toast = useToast()
const { validateBuilding, isBuildingOwner, activeTenantId } = useBuildingGuard()
const { can, canAny } = usePermission()

// ─── Tab ───
const activeTab = ref<'active' | 'history'>('active')

// ─── Stats ───
const stats = ref({ activeCars: 0, availableSlots: 0, revenueToday: 0 })

// ─── Active tickets ───
const activeTickets = ref<ITicket[]>([])
const activeLoading = ref(true)

// ─── History ───
const historyTickets = ref<ITicket[]>([])
const historyLoading = ref(true)
const historyMeta = ref<{ current_page: number; last_page: number; total: number } | null>(null)
const historyPage = ref(1)

// ─── Live duration timer ───
let durationTimer: ReturnType<typeof setInterval> | null = null
const now = ref(Date.now())

function startTimer() {
  durationTimer = setInterval(() => { now.value = Date.now() }, 30000)
}

function stopTimer() {
  if (durationTimer) clearInterval(durationTimer)
}

function liveDuration(entryAt: string): string {
  const ms = now.value - new Date(entryAt).getTime()
  if (ms < 0) return '0m'
  const totalMinutes = Math.floor(ms / 60000)
  const h = Math.floor(totalMinutes / 60)
  const m = totalMinutes % 60
  return h > 0 ? `${h}h ${m}m` : `${m}m`
}

// ─── Fetch ───
async function fetchActiveTickets() {
  activeLoading.value = true
  try {
    const res = await tenantService.getTickets({ status: 'active', per_page: 100 })
    activeTickets.value = res.data
    stats.value.activeCars = res.meta?.total ?? res.data.length
  } finally {
    activeLoading.value = false
  }
}

async function fetchHistoryTickets(page = 1) {
  historyLoading.value = true
  try {
    const res = await tenantService.getTickets({ status: 'closed', page, per_page: 15 })
    historyTickets.value = res.data
    historyMeta.value = res.meta as typeof historyMeta.value
    historyPage.value = page
  } finally {
    historyLoading.value = false
  }
}

async function fetchSlotStats() {
  try {
    const res = await tenantService.getSlotSummary()
    stats.value.availableSlots = res.data.available
  } catch {
    // non-critical
  }
}

async function fetchRevenueStats() {
  try {
    const res = await tenantService.getDashboard()
    stats.value.revenueToday = res.data.stats.revenue_today
  } catch {
    // non-critical
  }
}

// ─── Check In dialog ───
const showCheckIn = ref(false)
const availableSlots = ref<IParkingSlot[]>([])
const checkInLoading = ref(false)
const checkInErrors = ref<Record<string, string>>({})

const checkInForm = ref({
  vehicle_plate: '',
  vehicle_type: '',
  vehicle_color: '',
  vehicle_make: '',
  owner_name: '',
  owner_phone: '',
  parking_slot_id: null as number | null,
})

const vehicleTypes = [
  { value: 'motorcycle', label: 'Motorcycle' },
  { value: 'car', label: 'Car' },
  { value: 'suv', label: 'SUV' },
  { value: 'van', label: 'Van' },
  { value: 'truck', label: 'Truck' },
]

async function openCheckIn() {
  checkInForm.value = { vehicle_plate: '', vehicle_type: '', vehicle_color: '', vehicle_make: '', owner_name: '', owner_phone: '', parking_slot_id: null }
  checkInErrors.value = {}
  showCheckIn.value = true
  try {
    const res = await tenantService.getSlots({ status: 'available' })
    availableSlots.value = res.data
  } catch {
    availableSlots.value = []
  }
}

// Reload all data when building owner switches buildings
watch(activeTenantId, (newId) => {
  if (!isBuildingOwner.value || !newId) return
  Promise.all([fetchActiveTickets(), fetchSlotStats(), fetchRevenueStats()])
  fetchHistoryTickets(1)
})

// Reload available slots when building owner switches buildings while dialog is open
watch(
  () => activeTenantId.value,
  async (newId) => {
    if (!showCheckIn.value || !newId) return
    checkInForm.value.parking_slot_id = null
    try {
      const res = await tenantService.getSlots({ status: 'available' })
      availableSlots.value = res.data
    } catch {
      availableSlots.value = []
    }
  },
)

async function submitCheckIn() {
  checkInErrors.value = {}
  if (!await validateBuilding()) return
  if (!checkInForm.value.vehicle_plate) { checkInErrors.value.vehicle_plate = 'Plate number is required'; return }
  if (!checkInForm.value.vehicle_type) { checkInErrors.value.vehicle_type = 'Vehicle type is required'; return }

  checkInLoading.value = true
  try {
    const res = await tenantService.createTicket({
      vehicle_plate: checkInForm.value.vehicle_plate.toUpperCase(),
      vehicle_type: checkInForm.value.vehicle_type,
      vehicle_color: checkInForm.value.vehicle_color || undefined,
      vehicle_make: checkInForm.value.vehicle_make || undefined,
      parking_slot_id: checkInForm.value.parking_slot_id ?? undefined,
      notes: checkInForm.value.owner_name ? `Owner: ${checkInForm.value.owner_name}${checkInForm.value.owner_phone ? ` | ${checkInForm.value.owner_phone}` : ''}` : undefined,
    })
    const ticket = res.data
    showCheckIn.value = false
    await Promise.all([fetchActiveTickets(), fetchSlotStats()])
    await generateQr(ticket)
  } catch (error) {
    if (isAxiosError(error) && error.response?.status === 422) {
      const errs = error.response.data.errors ?? {}
      for (const key in errs) {
        checkInErrors.value[key] = Array.isArray(errs[key]) ? errs[key][0] : errs[key]
      }
    } else if (isAxiosError(error) && error.response?.data?.message) {
      toast.add({ severity: 'error', summary: 'Error', detail: error.response.data.message, life: 5000 })
    }
  } finally {
    checkInLoading.value = false
  }
}

// ─── QR Ticket modal ───
const showQrTicket = ref(false)
const qrTicket = ref<ITicket | null>(null)
const qrDataUrl = ref('')

async function generateQr(ticket: ITicket) {
  qrTicket.value = ticket
  showQrTicket.value = true
  await nextTick()
  try {
    qrDataUrl.value = await QRCode.toDataURL(ticket.ticket_number, {
      width: 200,
      margin: 2,
      color: { dark: '#1e293b', light: '#ffffff' },
    })
  } catch {
    qrDataUrl.value = ''
  }
}

function printTicket() {
  printJS({
    printable: 'qr-ticket-print',
    type: 'html',
    targetStyles: ['*'],
    style: `
      @page { size: 80mm auto; margin: 2mm; }
      * { box-sizing: border-box; }
      body { font-family: monospace; text-align: center; width: 72mm; margin: 0 auto; background: #fff; color: #000; padding: 4px 0; }
      p { margin: 2px 0; font-size: 11px; }
      img { display: block; margin: 6px auto; max-width: 110px; }
      .text-xs { font-size: 10px; } .text-2xl { font-size: 16px; } .font-bold { font-weight: bold; }
      .font-mono { font-family: monospace; } .tracking-widest { letter-spacing: 2px; }
      .flex { display: flex; } .justify-between { justify-content: space-between; }
      .px-2 { padding: 0 4px; } .my-4 { margin: 6px 0; } .mb-4 { margin-bottom: 6px; }
      .mb-1 { margin-bottom: 2px; } .space-y-1\\.5 > * + * { margin-top: 3px; }
      .text-slate-400 { color: #666; } .text-slate-700 { color: #333; }
      .text-slate-800 { color: #111; } .text-slate-900 { color: #000; }
      .uppercase { text-transform: uppercase; } .capitalize { text-transform: capitalize; }
      .font-semibold { font-weight: 600; } .font-medium { font-weight: 500; }
      .inline-block { display: inline-block; }
      .p-3 { padding: 4px; } .border { border: 1px solid #ccc; } .rounded-xl { border-radius: 4px; }
      .bg-white { background: #fff; } .flex.justify-center { justify-content: center; }
      .w-40 { width: 110px; } .h-40 { height: 110px; }
      .border-dashed { border-style: dashed; } .border-t { border-top: 1px dashed #999; }
      .border-slate-200 { border-color: #ccc; }
    `,
  })
}

// ─── Check Out dialog ───
const showCheckOut = ref(false)
const checkOutTicket = ref<ITicket | null>(null)
const checkOutPricing = ref<IPricing | null>(null)
const checkOutPricingLoading = ref(false)
const checkOutMethod = ref('cash')
const checkOutCoupon = ref('')
const checkOutLoading = ref(false)
const checkOutError = ref('')

const paymentMethods = [
  { value: 'cash', label: 'Cash', icon: 'pi pi-money-bill' },
  { value: 'card', label: 'Card', icon: 'pi pi-credit-card' },
  { value: 'digital', label: 'Digital', icon: 'pi pi-mobile' },
]

async function openCheckOut(ticket: ITicket) {
  checkOutTicket.value = ticket
  checkOutMethod.value = 'cash'
  checkOutCoupon.value = ''
  checkOutError.value = ''
  checkOutPricing.value = null
  showCheckOut.value = true
  await loadPrice()
}

async function loadPrice() {
  if (!checkOutTicket.value) return
  checkOutPricingLoading.value = true
  try {
    const res = await tenantService.calculatePrice(checkOutTicket.value.id)
    checkOutPricing.value = res.data
  } finally {
    checkOutPricingLoading.value = false
  }
}

async function confirmCheckOut() {
  if (!checkOutTicket.value || !checkOutPricing.value) return
  checkOutError.value = ''
  checkOutLoading.value = true
  try {
    await tenantService.processPayment({
      ticket_id: checkOutTicket.value.id,
      payment_method: checkOutMethod.value,
      coupon_code: checkOutCoupon.value || undefined,
    })
    await tenantService.updateTicketStatus(checkOutTicket.value.id, { status: 'closed' })
    toast.add({ severity: 'success', summary: 'Checked Out', detail: 'Payment collected, vehicle released', life: 3000 })
    showCheckOut.value = false
    await Promise.all([fetchActiveTickets(), fetchSlotStats(), fetchRevenueStats()])
  } catch (error) {
    if (isAxiosError(error) && error.response?.data?.message) {
      checkOutError.value = error.response.data.message
    } else {
      checkOutError.value = 'Checkout failed. Please try again.'
    }
  } finally {
    checkOutLoading.value = false
  }
}

// ─── Helpers ───
function formatDuration(minutes: number | null): string {
  if (!minutes) return '-'
  const h = Math.floor(minutes / 60), m = minutes % 60
  return h > 0 ? `${h}h ${m}m` : `${m}m`
}

const canCreate = computed(() => can('tickets.create'))
const canClose  = computed(() => canAny(['tickets.close', 'payments.create']))

onMounted(async () => {
  startTimer()
  await Promise.all([fetchActiveTickets(), fetchSlotStats(), fetchRevenueStats()])
  await fetchHistoryTickets()
})

onUnmounted(stopTimer)
</script>

<template>
  <div>
    <!-- Building filter for building owners -->
    <BuildingSelector mode="filter" />

    <!-- Header -->
    <div class="flex items-center justify-between mb-6 gap-4">
      <div>
        <h1 class="text-xl sm:text-2xl font-bold text-slate-900">Parking Operations</h1>
        <p class="text-sm text-slate-500 mt-1 hidden sm:block">Check in vehicles and manage active parking</p>
      </div>
      <button
        v-if="canCreate"
        class="shrink-0 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white px-4 py-2.5 rounded-lg text-sm font-semibold flex items-center gap-2 shadow-sm transition-all"
        @click="openCheckIn"
      >
        <i class="pi pi-plus text-xs" />
        <span class="hidden sm:inline">Check In</span>
        <span class="sm:hidden">Check In</span>
      </button>
    </div>

    <!-- Stats bar -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-6">
      <div class="bg-white rounded-xl border border-slate-200 p-4 sm:p-5 flex sm:block items-center gap-4">
        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide sm:mb-1 shrink-0">Active Cars</p>
        <p class="text-2xl sm:text-3xl font-bold text-slate-900">{{ stats.activeCars }}</p>
        <p class="text-xs text-slate-400 hidden sm:block mt-1">Currently parked</p>
      </div>
      <div class="bg-white rounded-xl border border-slate-200 p-4 sm:p-5 flex sm:block items-center gap-4">
        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide sm:mb-1 shrink-0">Available Slots</p>
        <p class="text-2xl sm:text-3xl font-bold text-emerald-600">{{ stats.availableSlots }}</p>
        <p class="text-xs text-slate-400 hidden sm:block mt-1">Open right now</p>
      </div>
      <div class="bg-white rounded-xl border border-slate-200 p-4 sm:p-5 flex sm:block items-center gap-4">
        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide sm:mb-1 shrink-0">Revenue Today</p>
        <p class="text-2xl sm:text-3xl font-bold text-indigo-600">{{ formatCurrency(stats.revenueToday) }}</p>
        <p class="text-xs text-slate-400 hidden sm:block mt-1">Collected so far</p>
      </div>
    </div>

    <!-- Tabs -->
    <div class="flex border-b border-slate-200 mb-0">
      <button
        class="px-5 py-3 text-sm font-medium border-b-2 -mb-px transition-colors"
        :class="activeTab === 'active' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-slate-500 hover:text-slate-700'"
        @click="activeTab = 'active'"
      >
        Active Parking
        <span v-if="activeTickets.length > 0" class="ml-2 bg-indigo-100 text-indigo-700 text-[11px] font-bold px-1.5 py-0.5 rounded-full">{{ activeTickets.length }}</span>
      </button>
      <button
        class="px-5 py-3 text-sm font-medium border-b-2 -mb-px transition-colors"
        :class="activeTab === 'history' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-slate-500 hover:text-slate-700'"
        @click="activeTab = 'history'; fetchHistoryTickets(1)"
      >
        History
      </button>
    </div>

    <!-- Active Parking Tab -->
    <div v-if="activeTab === 'active'" class="bg-white rounded-b-xl rounded-tr-xl border border-t-0 border-slate-200 overflow-hidden">
      <div v-if="activeLoading" class="p-16 text-center">
        <i class="pi pi-spinner pi-spin text-3xl text-slate-300" />
      </div>
      <div v-else-if="activeTickets.length === 0" class="p-16 text-center">
        <i class="pi pi-car text-5xl text-slate-200 mb-4" />
        <h3 class="text-lg font-semibold text-slate-600">No vehicles parked</h3>
        <p class="text-sm text-slate-400 mt-1">Check in the first vehicle to get started</p>
        <button v-if="canCreate" class="mt-4 bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium" @click="openCheckIn">
          Check In Vehicle
        </button>
      </div>
      <div v-else class="overflow-x-auto">
        <table class="w-full min-w-[600px]">
          <thead>
            <tr class="border-b border-slate-100 bg-slate-50/50">
              <th class="text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider px-4 sm:px-6 py-3">Plate</th>
              <th class="text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider px-4 sm:px-6 py-3 hidden sm:table-cell">Ticket</th>
              <th class="text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider px-4 sm:px-6 py-3 hidden md:table-cell">Type</th>
              <th class="text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider px-4 sm:px-6 py-3 hidden lg:table-cell">Make</th>
              <th class="text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider px-4 sm:px-6 py-3">Slot</th>
              <th class="text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider px-4 sm:px-6 py-3 hidden lg:table-cell">Check-in</th>
              <th class="text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider px-4 sm:px-6 py-3 hidden xl:table-cell">Check In By</th>
              <th class="text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider px-4 sm:px-6 py-3">Duration</th>
              <th class="text-right text-[11px] font-semibold text-slate-400 uppercase tracking-wider px-4 sm:px-6 py-3">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr v-for="ticket in activeTickets" :key="ticket.id" class="hover:bg-slate-50/50 transition-colors">
              <td class="px-4 sm:px-6 py-3 sm:py-4">
                <p class="text-sm font-bold text-slate-800">{{ ticket.vehicle_plate }}</p>
                <p v-if="ticket.vehicle_color" class="text-xs text-slate-400">{{ ticket.vehicle_color }}</p>
              </td>
              <td class="px-4 sm:px-6 py-3 sm:py-4 text-sm font-mono font-semibold text-slate-700 hidden sm:table-cell">{{ ticket.ticket_number }}</td>
              <td class="px-4 sm:px-6 py-3 sm:py-4 text-sm text-slate-600 capitalize hidden md:table-cell">{{ ticket.vehicle_type }}</td>
              <td class="px-4 sm:px-6 py-3 sm:py-4 text-sm text-slate-500 hidden lg:table-cell">{{ ticket.vehicle_make ?? '—' }}</td>
              <td class="px-4 sm:px-6 py-3 sm:py-4 text-sm font-medium text-slate-700">
                {{ ticket.parking_slot ? `${ticket.parking_slot.floor}-${ticket.parking_slot.slot_number}` : '—' }}
              </td>
              <td class="px-4 sm:px-6 py-3 sm:py-4 text-sm text-slate-500 hidden lg:table-cell">{{ formatDate(ticket.entry_at) }}</td>
              <td class="px-4 sm:px-6 py-3 sm:py-4 text-sm text-slate-500 hidden xl:table-cell">{{ ticket.creator?.name ?? '—' }}</td>
              <td class="px-4 sm:px-6 py-3 sm:py-4">
                <span class="inline-flex items-center gap-1 text-xs sm:text-sm font-semibold text-amber-700 bg-amber-50 px-2 sm:px-2.5 py-1 rounded-md ring-1 ring-amber-200 whitespace-nowrap">
                  <i class="pi pi-clock text-[10px]" />
                  {{ liveDuration(ticket.entry_at) }}
                </span>
              </td>
              <td class="px-4 sm:px-6 py-3 sm:py-4 text-right">
                <div class="flex items-center justify-end gap-1.5">
                  <button
                    class="inline-flex items-center gap-1 px-2 sm:px-3 py-1.5 border border-slate-300 text-slate-600 hover:bg-slate-50 text-xs font-medium rounded-lg transition-colors"
                    title="Reprint QR Ticket"
                    @click="generateQr(ticket)"
                  >
                    <i class="pi pi-qrcode text-[11px]" />
                    <span class="hidden sm:inline">QR</span>
                  </button>
                  <button
                    v-if="canClose"
                    class="inline-flex items-center gap-1 px-2 sm:px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-lg transition-colors shadow-sm whitespace-nowrap"
                    @click="openCheckOut(ticket)"
                  >
                    <i class="pi pi-sign-out text-[11px]" />
                    <span class="hidden sm:inline">Check Out</span>
                    <span class="sm:hidden">Out</span>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- History Tab -->
    <div v-if="activeTab === 'history'" class="bg-white rounded-b-xl rounded-tr-xl border border-t-0 border-slate-200 overflow-hidden">
      <div v-if="historyLoading" class="p-16 text-center">
        <i class="pi pi-spinner pi-spin text-3xl text-slate-300" />
      </div>
      <div v-else-if="historyTickets.length === 0" class="p-16 text-center">
        <i class="pi pi-history text-5xl text-slate-200 mb-4" />
        <h3 class="text-lg font-semibold text-slate-600">No history yet</h3>
      </div>
      <template v-else>
        <div class="overflow-x-auto">
          <table class="w-full min-w-[640px]">
            <thead>
              <tr class="border-b border-slate-100 bg-slate-50/50">
                <th class="text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider px-4 sm:px-6 py-3">Plate</th>
                <th class="text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider px-4 sm:px-6 py-3 hidden sm:table-cell">Ticket</th>
                <th class="text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider px-4 sm:px-6 py-3 hidden md:table-cell">Slot</th>
                <th class="text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider px-4 sm:px-6 py-3">Check-in</th>
                <th class="text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider px-4 sm:px-6 py-3 hidden lg:table-cell">Check-out</th>
                <th class="text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider px-4 sm:px-6 py-3">Duration</th>
                <th class="text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider px-4 sm:px-6 py-3">Amount</th>
                <th class="text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider px-4 sm:px-6 py-3 hidden xl:table-cell">Check In By</th>
                <th class="text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider px-4 sm:px-6 py-3 hidden xl:table-cell">Check Out By</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <tr v-for="ticket in historyTickets" :key="ticket.id" class="hover:bg-slate-50/50 transition-colors">
                <td class="px-4 sm:px-6 py-3 sm:py-4">
                  <p class="text-sm font-medium text-slate-800">{{ ticket.vehicle_plate }}</p>
                  <p class="text-xs text-slate-400 capitalize">{{ ticket.vehicle_type }}</p>
                </td>
                <td class="px-4 sm:px-6 py-3 sm:py-4 text-sm font-mono font-semibold text-slate-600 hidden sm:table-cell">{{ ticket.ticket_number }}</td>
                <td class="px-4 sm:px-6 py-3 sm:py-4 text-sm text-slate-500 hidden md:table-cell">
                  {{ ticket.parking_slot ? `${ticket.parking_slot.floor}-${ticket.parking_slot.slot_number}` : '—' }}
                </td>
                <td class="px-4 sm:px-6 py-3 sm:py-4 text-sm text-slate-500">{{ formatDate(ticket.entry_at) }}</td>
                <td class="px-4 sm:px-6 py-3 sm:py-4 text-sm text-slate-500 hidden lg:table-cell">{{ ticket.exit_at ? formatDate(ticket.exit_at) : '—' }}</td>
                <td class="px-4 sm:px-6 py-3 sm:py-4 text-sm text-slate-500">{{ formatDuration(ticket.duration_minutes) }}</td>
                <td class="px-4 sm:px-6 py-3 sm:py-4 text-sm font-semibold text-slate-800">
                  {{ ticket.total_amount > 0 ? formatCurrency(ticket.total_amount) : '—' }}
                </td>
                <td class="px-4 sm:px-6 py-3 sm:py-4 text-sm text-slate-400 hidden xl:table-cell">{{ ticket.creator?.name ?? '—' }}</td>
                <td class="px-4 sm:px-6 py-3 sm:py-4 text-sm text-slate-400 hidden xl:table-cell">{{ ticket.closer?.name ?? '—' }}</td>
              </tr>
            </tbody>
          </table>
        </div>
        <div v-if="historyMeta && historyMeta.last_page > 1" class="flex items-center justify-between px-4 sm:px-6 py-4 border-t border-slate-100 flex-wrap gap-3">
          <p class="text-sm text-slate-400">Page {{ historyPage }} of {{ historyMeta.last_page }} ({{ historyMeta.total }} tickets)</p>
          <div class="flex gap-2">
            <button class="px-3 py-1.5 text-sm border border-slate-300 rounded-lg hover:bg-slate-50 disabled:opacity-40 transition-colors" :disabled="historyPage <= 1" @click="fetchHistoryTickets(historyPage - 1)">Previous</button>
            <button class="px-3 py-1.5 text-sm border border-slate-300 rounded-lg hover:bg-slate-50 disabled:opacity-40 transition-colors" :disabled="historyPage >= historyMeta.last_page" @click="fetchHistoryTickets(historyPage + 1)">Next</button>
          </div>
        </div>
      </template>
    </div>

    <!-- ═══ QR Ticket Modal ═══ -->
    <div v-if="showQrTicket && qrTicket" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center z-50">
      <div class="bg-white rounded-2xl w-full max-w-sm mx-4 shadow-2xl">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
          <h2 class="text-base font-semibold text-slate-900">Parking Ticket Ready</h2>
          <button class="text-slate-400 hover:text-slate-600 p-1 rounded-md hover:bg-slate-100 transition-colors" @click="showQrTicket = false">
            <i class="pi pi-times text-sm" />
          </button>
        </div>

        <!-- Printable ticket content -->
        <div id="qr-ticket-print" class="p-6 text-center">
          <p class="text-xs font-bold uppercase tracking-widest mb-1">*** PARKING TICKET ***</p>
          <p class="text-2xl font-bold font-mono text-slate-900 tracking-widest">{{ qrTicket.ticket_number }}</p>

          <div class="border-t border-dashed border-slate-200 my-4" />

          <!-- QR Code -->
          <div class="flex justify-center mb-4">
            <div v-if="qrDataUrl">
              <img :src="qrDataUrl" alt="QR Code" class="w-40 h-40" />
            </div>
          </div>

          <!-- Ticket details -->
          <div class="space-y-1.5 text-sm">
            <div class="flex justify-between px-2">
              <span class="text-slate-400">Plate</span>
              <span class="font-bold font-mono text-slate-800">{{ qrTicket.vehicle_plate }}</span>
            </div>
            <div class="flex justify-between px-2">
              <span class="text-slate-400">Type</span>
              <span class="font-medium text-slate-700 capitalize">{{ qrTicket.vehicle_type }}</span>
            </div>
            <div v-if="qrTicket.vehicle_color" class="flex justify-between px-2">
              <span class="text-slate-400">Color</span>
              <span class="font-medium text-slate-700">{{ qrTicket.vehicle_color }}</span>
            </div>
            <div v-if="qrTicket.vehicle_make" class="flex justify-between px-2">
              <span class="text-slate-400">Make</span>
              <span class="font-medium text-slate-700">{{ qrTicket.vehicle_make }}</span>
            </div>
            <div class="flex justify-between px-2">
              <span class="text-slate-400">Slot</span>
              <span class="font-medium text-slate-700">
                {{ qrTicket.parking_slot ? `${qrTicket.parking_slot.floor}-${qrTicket.parking_slot.slot_number}` : '—' }}
              </span>
            </div>
            <div class="flex justify-between px-2">
              <span class="text-slate-400">Entry</span>
              <span class="font-medium text-slate-700">{{ formatDate(qrTicket.entry_at) }}</span>
            </div>
          </div>

          <div class="border-t border-dashed border-slate-200 my-4" />
          <p class="text-xs text-slate-400">Keep ticket. Present at exit.</p>
        </div>

        <!-- Actions -->
        <div class="p-5 border-t border-slate-100 flex gap-3">
          <button
            class="flex-1 px-4 py-2.5 text-sm font-medium border border-slate-300 rounded-lg hover:bg-slate-50 transition-colors"
            @click="showQrTicket = false"
          >
            Close
          </button>
          <button
            class="flex-1 px-4 py-2.5 text-sm font-semibold bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 flex items-center justify-center gap-2 shadow-sm transition-all"
            @click="printTicket"
          >
            <i class="pi pi-print text-xs" />
            Print Ticket
          </button>
        </div>
      </div>
    </div>

    <!-- ═══ Check In Dialog ═══ -->
    <div v-if="showCheckIn" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center z-50">
      <div class="bg-white rounded-2xl w-full max-w-lg mx-4 shadow-2xl">
        <div class="p-6 border-b border-slate-100 flex items-center justify-between">
          <div>
            <h2 class="text-lg font-semibold text-slate-900">Vehicle Check In</h2>
            <p class="text-xs text-slate-400 mt-0.5">Enter vehicle details to start parking session</p>
          </div>
          <button class="text-slate-400 hover:text-slate-600 p-1 rounded-md hover:bg-slate-100 transition-colors" @click="showCheckIn = false">
            <i class="pi pi-times text-sm" />
          </button>
        </div>

        <div class="p-6 space-y-4">
          <!-- Building selector for building owners -->
          <BuildingSelector />

          <!-- Plate -->
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5">Plate Number <span class="text-red-500">*</span></label>
            <input
              v-model="checkInForm.vehicle_plate"
              type="text"
              placeholder="ABC-1234"
              class="w-full h-10 border rounded-lg px-4 text-sm font-mono uppercase outline-none transition-all"
              :class="checkInErrors.vehicle_plate ? 'border-red-400 bg-red-50/50 focus:border-red-400' : 'border-slate-300 hover:border-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/10'"
            />
            <p v-if="checkInErrors.vehicle_plate" class="mt-1.5 text-[13px] text-red-500 flex items-center gap-1"><i class="pi pi-exclamation-circle text-[11px]" />{{ checkInErrors.vehicle_plate }}</p>
          </div>

          <!-- Type + Color -->
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1.5">Vehicle Type <span class="text-red-500">*</span></label>
              <select
                v-model="checkInForm.vehicle_type"
                class="w-full h-10 border rounded-lg px-4 text-sm outline-none transition-all"
                :class="checkInErrors.vehicle_type ? 'border-red-400 bg-red-50/50' : 'border-slate-300 hover:border-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/10'"
              >
                <option value="" disabled>Select type</option>
                <option v-for="vt in vehicleTypes" :key="vt.value" :value="vt.value">{{ vt.label }}</option>
              </select>
              <p v-if="checkInErrors.vehicle_type" class="mt-1.5 text-[13px] text-red-500 flex items-center gap-1"><i class="pi pi-exclamation-circle text-[11px]" />{{ checkInErrors.vehicle_type }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1.5">Color <span class="text-slate-400 font-normal text-xs">(optional)</span></label>
              <input v-model="checkInForm.vehicle_color" type="text" placeholder="White, Red..." class="w-full h-10 border border-slate-300 rounded-lg px-4 text-sm outline-none hover:border-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/10 transition-all" />
            </div>
          </div>

          <!-- Make -->
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5">Make of Car <span class="text-slate-400 font-normal text-xs">(optional)</span></label>
            <input v-model="checkInForm.vehicle_make" type="text" placeholder="Toyota, Honda, BMW..." class="w-full h-10 border border-slate-300 rounded-lg px-4 text-sm outline-none hover:border-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/10 transition-all" />
          </div>

          <!-- Owner name + phone -->
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1.5">Owner Name <span class="text-slate-400 font-normal text-xs">(optional)</span></label>
              <input v-model="checkInForm.owner_name" type="text" placeholder="John Doe" class="w-full h-10 border border-slate-300 rounded-lg px-4 text-sm outline-none hover:border-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/10 transition-all" />
            </div>
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1.5">Phone <span class="text-slate-400 font-normal text-xs">(optional)</span></label>
              <input v-model="checkInForm.owner_phone" type="tel" placeholder="+1 234 567 8900" class="w-full h-10 border border-slate-300 rounded-lg px-4 text-sm outline-none hover:border-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/10 transition-all" />
            </div>
          </div>

          <!-- Slot -->
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5">Parking Slot <span class="text-slate-400 font-normal text-xs">(auto-assign if empty)</span></label>
            <select v-model="checkInForm.parking_slot_id" class="w-full h-10 border border-slate-300 rounded-lg px-4 text-sm outline-none hover:border-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/10 transition-all">
              <option :value="null">Auto-assign best available</option>
              <option v-for="slot in availableSlots" :key="slot.id" :value="slot.id">
                {{ slot.floor }}-{{ slot.slot_number }} ({{ slot.zone }})
              </option>
            </select>
            <p v-if="availableSlots.length === 0" class="mt-1 text-xs text-amber-600 flex items-center gap-1">
              <i class="pi pi-exclamation-triangle text-[11px]" />
              No available slots found
            </p>
          </div>
        </div>

        <div class="p-6 border-t border-slate-100 flex gap-3">
          <button class="flex-1 px-4 py-2.5 text-sm font-medium border border-slate-300 rounded-lg hover:bg-slate-50 transition-colors" @click="showCheckIn = false">Cancel</button>
          <button
            :disabled="checkInLoading"
            class="flex-1 px-4 py-2.5 text-sm font-semibold bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 disabled:opacity-50 flex items-center justify-center gap-2 shadow-sm transition-all"
            @click="submitCheckIn"
          >
            <i v-if="checkInLoading" class="pi pi-spinner pi-spin text-xs" />
            <i v-else class="pi pi-car text-xs" />
            Check In
          </button>
        </div>
      </div>
    </div>

    <!-- ═══ Check Out Dialog ═══ -->
    <div v-if="showCheckOut && checkOutTicket" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center z-50">
      <div class="bg-white rounded-2xl w-full max-w-md mx-4 shadow-2xl">
        <div class="p-6 border-b border-slate-100 flex items-center justify-between">
          <div>
            <h2 class="text-lg font-semibold text-slate-900">Check Out</h2>
            <p class="text-xs text-slate-400 mt-0.5">{{ checkOutTicket.vehicle_plate }} &middot; {{ checkOutTicket.ticket_number }}</p>
          </div>
          <button class="text-slate-400 hover:text-slate-600 p-1 rounded-md hover:bg-slate-100 transition-colors" @click="showCheckOut = false">
            <i class="pi pi-times text-sm" />
          </button>
        </div>

        <div class="p-6 space-y-4">
          <!-- Duration + amount -->
          <div class="bg-slate-50 rounded-xl p-4">
            <div class="flex justify-between items-start mb-3">
              <div>
                <p class="text-xs text-slate-400 font-medium">Plate</p>
                <p class="text-base font-bold text-slate-900 font-mono">{{ checkOutTicket.vehicle_plate }}</p>
              </div>
              <div class="text-right">
                <p class="text-xs text-slate-400 font-medium">Slot</p>
                <p class="text-base font-semibold text-slate-700">{{ checkOutTicket.parking_slot ? `${checkOutTicket.parking_slot.floor}-${checkOutTicket.parking_slot.slot_number}` : '—' }}</p>
              </div>
            </div>
            <div class="flex justify-between items-start">
              <div>
                <p class="text-xs text-slate-400 font-medium">Check-in</p>
                <p class="text-sm font-medium text-slate-700">{{ formatDate(checkOutTicket.entry_at) }}</p>
              </div>
              <div class="text-right">
                <p class="text-xs text-slate-400 font-medium">Duration</p>
                <p class="text-sm font-bold text-amber-700">{{ liveDuration(checkOutTicket.entry_at) }}</p>
              </div>
            </div>
          </div>

          <!-- Price breakdown -->
          <div v-if="checkOutPricingLoading" class="bg-white border border-slate-200 rounded-lg p-4 text-center">
            <i class="pi pi-spinner pi-spin text-slate-400" />
            <p class="text-xs text-slate-400 mt-1">Calculating price...</p>
          </div>
          <div v-else-if="checkOutPricing" class="border border-slate-200 rounded-lg p-4">
            <h3 class="text-sm font-semibold text-slate-700 mb-3">Price Breakdown</h3>
            <div class="space-y-2 text-sm">
              <div class="flex justify-between text-slate-600">
                <span>Duration</span>
                <span>{{ formatDuration(checkOutPricing.duration_minutes) }}</span>
              </div>
              <div class="flex justify-between text-slate-600">
                <span>Base Amount</span>
                <span>{{ formatCurrency(checkOutPricing.base_amount) }}</span>
              </div>
              <div v-if="checkOutPricing.tax_amount > 0" class="flex justify-between text-slate-500">
                <span>Tax</span>
                <span>{{ formatCurrency(checkOutPricing.tax_amount) }}</span>
              </div>
              <div v-if="checkOutPricing.surcharge_amount > 0" class="flex justify-between text-amber-600">
                <span>Surcharge</span>
                <span>{{ formatCurrency(checkOutPricing.surcharge_amount) }}</span>
              </div>
              <div v-if="checkOutPricing.discount_amount > 0" class="flex justify-between text-emerald-600">
                <span>Discount</span>
                <span>-{{ formatCurrency(checkOutPricing.discount_amount) }}</span>
              </div>
              <hr class="border-slate-200" />
              <div class="flex justify-between text-base">
                <span class="font-bold text-slate-800">Total</span>
                <span class="font-bold text-indigo-700 text-lg">{{ formatCurrency(checkOutPricing.total_amount) }}</span>
              </div>
            </div>
          </div>

          <!-- Coupon -->
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5">Coupon Code <span class="text-slate-400 font-normal text-xs">(optional)</span></label>
            <input
              v-model="checkOutCoupon"
              type="text"
              placeholder="Enter coupon code"
              class="w-full h-10 border border-slate-300 rounded-lg px-4 text-sm uppercase outline-none hover:border-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/10 transition-all"
            />
          </div>

          <!-- Payment method -->
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-2">Payment Method</label>
            <div class="grid grid-cols-3 gap-2">
              <button
                v-for="method in paymentMethods"
                :key="method.value"
                type="button"
                class="p-3 border rounded-lg text-center text-sm font-medium transition-all"
                :class="checkOutMethod === method.value
                  ? 'border-indigo-500 bg-indigo-50 text-indigo-700 ring-1 ring-indigo-500'
                  : 'border-slate-200 text-slate-600 hover:border-slate-300 hover:bg-slate-50'"
                @click="checkOutMethod = method.value"
              >
                <i :class="method.icon" class="text-xl block mb-1" />
                {{ method.label }}
              </button>
            </div>
          </div>

          <!-- Error -->
          <p v-if="checkOutError" class="text-sm text-red-500 flex items-center gap-1.5">
            <i class="pi pi-exclamation-circle" />
            {{ checkOutError }}
          </p>
        </div>

        <div class="p-6 border-t border-slate-100 flex gap-3">
          <button class="flex-1 px-4 py-2.5 text-sm font-medium border border-slate-300 rounded-lg hover:bg-slate-50 transition-colors" @click="showCheckOut = false">Cancel</button>
          <button
            :disabled="checkOutLoading || checkOutPricingLoading || !checkOutPricing"
            class="flex-1 px-4 py-2.5 text-sm font-semibold bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 disabled:opacity-50 flex items-center justify-center gap-2 shadow-sm transition-all"
            @click="confirmCheckOut"
          >
            <i v-if="checkOutLoading" class="pi pi-spinner pi-spin text-xs" />
            <i v-else class="pi pi-check text-xs" />
            Confirm &amp; Collect
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
