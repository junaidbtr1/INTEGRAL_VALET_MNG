<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { tenantService } from '@/services/tenantService'
import type { IReportData } from '@/services/tenantService'
import { formatCurrency, formatDuration } from '@/utils/formatters'

// ─── Date range ───
type Preset = 'today' | 'week' | 'month' | 'last_month' | 'custom'
const preset = ref<Preset>('month')
const customFrom = ref('')
const customTo = ref('')

function toLocalDate(d: Date): string {
  return d.toISOString().slice(0, 10)
}

const dateRange = computed(() => {
  const now = new Date()
  if (preset.value === 'today') {
    const d = toLocalDate(now)
    return { from: d, to: d }
  }
  if (preset.value === 'week') {
    const mon = new Date(now)
    mon.setDate(now.getDate() - now.getDay() + 1)
    return { from: toLocalDate(mon), to: toLocalDate(now) }
  }
  if (preset.value === 'month') {
    return {
      from: `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}-01`,
      to: toLocalDate(now),
    }
  }
  if (preset.value === 'last_month') {
    const first = new Date(now.getFullYear(), now.getMonth() - 1, 1)
    const last  = new Date(now.getFullYear(), now.getMonth(), 0)
    return { from: toLocalDate(first), to: toLocalDate(last) }
  }
  return { from: customFrom.value, to: customTo.value }
})

// ─── Data ───
const report = ref<IReportData | null>(null)
const isLoading = ref(false)
const error = ref('')
const isDownloading = ref(false)

async function downloadCsv() {
  const { from, to } = dateRange.value
  if (!from || !to) return
  isDownloading.value = true
  try {
    const blob = await tenantService.downloadReport({ from, to }) as unknown as Blob
    const url = URL.createObjectURL(blob)
    const a = document.createElement('a')
    a.href = url
    a.download = `report_${from}_to_${to}.csv`
    a.click()
    URL.revokeObjectURL(url)
  } finally {
    isDownloading.value = false
  }
}

async function load() {
  const { from, to } = dateRange.value
  if (!from || !to) return
  isLoading.value = true
  error.value = ''
  try {
    const res = await tenantService.getReports({ from, to })
    report.value = res.data
  } catch {
    error.value = 'Failed to load report data.'
  } finally {
    isLoading.value = false
  }
}

function applyPreset(p: Preset) {
  preset.value = p
  if (p !== 'custom') load()
}

function applyCustom() {
  if (customFrom.value && customTo.value) load()
}

onMounted(() => load())

// ─── Chart helpers ───
const maxDayRevenue = computed(() =>
  Math.max(1, ...((report.value?.revenue_by_day ?? []).map(d => d.revenue)))
)

const maxHourCount = computed(() =>
  Math.max(1, ...((report.value?.by_hour ?? []).map(h => h.count)))
)

const totalMethodCount = computed(() =>
  (report.value?.by_payment_method ?? []).reduce((s, m) => s + m.count, 0) || 1
)

const totalVehicleCount = computed(() =>
  (report.value?.by_vehicle_type ?? []).reduce((s, v) => s + v.count, 0) || 1
)

const methodColors: Record<string, string> = {
  cash: 'bg-emerald-500',
  card: 'bg-blue-500',
  digital: 'bg-violet-500',
  coupon: 'bg-amber-500',
}

const vehicleColors: Record<string, string> = {
  motorcycle: 'bg-rose-500',
  car: 'bg-indigo-500',
  suv: 'bg-sky-500',
  van: 'bg-amber-500',
  truck: 'bg-orange-500',
  bus: 'bg-teal-500',
}

const vehicleIcons: Record<string, string> = {
  motorcycle: 'pi-angle-double-right',
  car: 'pi-car',
  suv: 'pi-car',
  van: 'pi-car',
  truck: 'pi-server',
  bus: 'pi-server',
}

function hourLabel(h: number): string {
  if (h === 0) return '12a'
  if (h < 12) return `${h}a`
  if (h === 12) return '12p'
  return `${h - 12}p`
}

const presetLabels: Record<Preset, string> = {
  today: 'Today',
  week: 'This Week',
  month: 'This Month',
  last_month: 'Last Month',
  custom: 'Custom',
}

// Show only every 3rd hour label to avoid crowding
function showHourLabel(h: number): boolean {
  return h % 3 === 0
}
</script>

<template>
  <div>
    <!-- Header -->
    <div class="flex items-start justify-between mb-6 flex-wrap gap-4">
      <div>
        <h1 class="text-2xl font-bold text-slate-900">Reports</h1>
        <p class="text-sm text-slate-500 mt-1">Analytics for {{ presetLabels[preset] }}</p>
      </div>

      <!-- Actions -->
      <div class="flex flex-wrap items-center gap-2">
        <button
          v-if="report"
          class="h-9 px-4 flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg transition-all disabled:opacity-60"
          :disabled="isDownloading"
          @click="downloadCsv"
        >
          <i class="pi pi-download text-xs" />
          <span>{{ isDownloading ? 'Downloading...' : 'Download CSV' }}</span>
        </button>
      </div>

      <!-- Date range controls -->
      <div class="flex flex-wrap items-center gap-2">
        <!-- Presets -->
        <div class="flex bg-slate-100 rounded-lg p-1 gap-1">
          <button
            v-for="p in (['today', 'week', 'month', 'last_month'] as Preset[])"
            :key="p"
            class="px-3 py-1.5 rounded-md text-xs font-medium transition-all"
            :class="preset === p
              ? 'bg-white text-slate-800 shadow-sm'
              : 'text-slate-500 hover:text-slate-700'"
            @click="applyPreset(p)"
          >
            {{ presetLabels[p] }}
          </button>
          <button
            class="px-3 py-1.5 rounded-md text-xs font-medium transition-all"
            :class="preset === 'custom'
              ? 'bg-white text-slate-800 shadow-sm'
              : 'text-slate-500 hover:text-slate-700'"
            @click="preset = 'custom'"
          >
            Custom
          </button>
        </div>

        <!-- Custom inputs -->
        <template v-if="preset === 'custom'">
          <input
            v-model="customFrom"
            type="date"
            class="h-9 border border-slate-300 rounded-lg px-3 text-sm outline-none focus:border-indigo-500 transition-all"
          />
          <span class="text-slate-400 text-sm">to</span>
          <input
            v-model="customTo"
            type="date"
            class="h-9 border border-slate-300 rounded-lg px-3 text-sm outline-none focus:border-indigo-500 transition-all"
          />
          <button
            class="h-9 px-4 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-all"
            @click="applyCustom"
          >
            Apply
          </button>
        </template>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="isLoading" class="flex items-center justify-center py-24">
      <i class="pi pi-spinner pi-spin text-3xl text-slate-300" />
    </div>

    <!-- Error -->
    <div v-else-if="error" class="bg-red-50 border border-red-200 rounded-xl p-6 text-center text-red-600">
      <i class="pi pi-exclamation-triangle text-2xl mb-2" />
      <p class="text-sm">{{ error }}</p>
      <button class="mt-3 text-sm text-red-700 underline" @click="load">Retry</button>
    </div>

    <template v-else-if="report">
      <!-- ── Summary Cards ── -->
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-slate-200 p-5">
          <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Total Revenue</p>
          <p class="text-2xl font-bold text-slate-900">{{ formatCurrency(report.summary.total_revenue) }}</p>
          <p class="text-xs text-slate-400 mt-1">
            Avg {{ formatCurrency(report.summary.avg_ticket_value) }} / session
          </p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-5">
          <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Total Tickets</p>
          <p class="text-2xl font-bold text-slate-900">{{ report.summary.total_tickets.toLocaleString() }}</p>
          <p class="text-xs text-slate-400 mt-1">{{ report.summary.completed_tickets }} completed</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-5">
          <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Avg Duration</p>
          <p class="text-2xl font-bold text-slate-900">
            {{ report.summary.avg_duration_minutes ? formatDuration(report.summary.avg_duration_minutes) : '—' }}
          </p>
          <p class="text-xs text-slate-400 mt-1">Per completed session</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-5">
          <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Completion Rate</p>
          <p class="text-2xl font-bold text-slate-900">
            {{ report.summary.total_tickets > 0
              ? Math.round((report.summary.completed_tickets / report.summary.total_tickets) * 100)
              : 0 }}%
          </p>
          <p class="text-xs text-slate-400 mt-1">Checked out vs total</p>
        </div>
      </div>

      <!-- ── Revenue by Day ── -->
      <div class="bg-white rounded-xl border border-slate-200 p-6 mb-6">
        <h2 class="text-sm font-semibold text-slate-700 mb-5">Revenue by Day</h2>
        <div v-if="report.revenue_by_day.length === 0" class="py-10 text-center text-sm text-slate-400">
          No revenue data for this period
        </div>
        <div v-else>
          <!-- Bar chart -->
          <div class="flex items-end gap-1 h-40">
            <div
              v-for="day in report.revenue_by_day"
              :key="day.date"
              class="flex-1 flex flex-col items-center gap-1 group min-w-0"
            >
              <!-- Tooltip -->
              <div class="relative flex flex-col items-center">
                <div
                  class="opacity-0 group-hover:opacity-100 absolute bottom-full mb-2 bg-slate-800 text-white text-[11px] rounded-lg px-2.5 py-1.5 whitespace-nowrap pointer-events-none z-10 shadow-lg transition-opacity"
                >
                  <p class="font-semibold">{{ formatCurrency(day.revenue) }}</p>
                  <p class="text-white/60">{{ day.tickets }} tickets</p>
                  <p class="text-white/60">{{ new Date(day.date + 'T00:00:00').toLocaleDateString('en-US', { month: 'short', day: 'numeric' }) }}</p>
                </div>
                <!-- Bar -->
                <div
                  class="w-full rounded-t-sm bg-indigo-500 hover:bg-indigo-600 transition-colors cursor-default min-h-[2px]"
                  :style="{ height: `${Math.max(2, (day.revenue / maxDayRevenue) * 140)}px` }"
                />
              </div>
            </div>
          </div>
          <!-- X labels -->
          <div class="flex items-center gap-1 mt-2">
            <div
              v-for="day in report.revenue_by_day"
              :key="day.date"
              class="flex-1 text-center text-[10px] text-slate-400 truncate min-w-0"
            >
              {{ new Date(day.date + 'T00:00:00').toLocaleDateString('en-US', { month: 'short', day: 'numeric' }) }}
            </div>
          </div>
        </div>
      </div>

      <!-- ── Middle row: Payment Methods + Vehicle Types ── -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">

        <!-- Payment Methods -->
        <div class="bg-white rounded-xl border border-slate-200 p-6">
          <h2 class="text-sm font-semibold text-slate-700 mb-4">Payment Methods</h2>
          <div v-if="report.by_payment_method.length === 0" class="py-6 text-center text-sm text-slate-400">
            No payment data
          </div>
          <div v-else class="space-y-3">
            <div
              v-for="m in report.by_payment_method"
              :key="m.method"
              class="flex items-center gap-3"
            >
              <div
                class="w-2.5 h-2.5 rounded-full shrink-0"
                :class="methodColors[m.method] ?? 'bg-slate-400'"
              />
              <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between mb-1">
                  <span class="text-sm font-medium text-slate-700 capitalize">{{ m.method }}</span>
                  <span class="text-xs text-slate-500">
                    {{ m.count }} · {{ formatCurrency(m.amount) }}
                  </span>
                </div>
                <div class="h-2 bg-slate-100 rounded-full overflow-hidden">
                  <div
                    class="h-full rounded-full transition-all"
                    :class="methodColors[m.method] ?? 'bg-slate-400'"
                    :style="{ width: `${(m.count / totalMethodCount) * 100}%` }"
                  />
                </div>
              </div>
              <span class="text-xs font-semibold text-slate-600 w-10 text-right shrink-0">
                {{ Math.round((m.count / totalMethodCount) * 100) }}%
              </span>
            </div>
          </div>
        </div>

        <!-- Vehicle Types -->
        <div class="bg-white rounded-xl border border-slate-200 p-6">
          <h2 class="text-sm font-semibold text-slate-700 mb-4">Vehicle Types</h2>
          <div v-if="report.by_vehicle_type.length === 0" class="py-6 text-center text-sm text-slate-400">
            No vehicle data
          </div>
          <div v-else class="space-y-3">
            <div
              v-for="v in report.by_vehicle_type"
              :key="v.type"
              class="flex items-center gap-3"
            >
              <div
                class="w-2.5 h-2.5 rounded-full shrink-0"
                :class="vehicleColors[v.type] ?? 'bg-slate-400'"
              />
              <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between mb-1">
                  <span class="text-sm font-medium text-slate-700 capitalize">{{ v.type }}</span>
                  <span class="text-xs text-slate-500">{{ v.count }} tickets</span>
                </div>
                <div class="h-2 bg-slate-100 rounded-full overflow-hidden">
                  <div
                    class="h-full rounded-full transition-all"
                    :class="vehicleColors[v.type] ?? 'bg-slate-400'"
                    :style="{ width: `${(v.count / totalVehicleCount) * 100}%` }"
                  />
                </div>
              </div>
              <span class="text-xs font-semibold text-slate-600 w-10 text-right shrink-0">
                {{ Math.round((v.count / totalVehicleCount) * 100) }}%
              </span>
            </div>
          </div>
        </div>
      </div>

      <!-- ── Peak Hours ── -->
      <div class="bg-white rounded-xl border border-slate-200 p-6 mb-6">
        <h2 class="text-sm font-semibold text-slate-700 mb-5">Peak Hours</h2>
        <div class="flex items-end gap-0.5 h-24">
          <div
            v-for="h in report.by_hour"
            :key="h.hour"
            class="flex-1 flex flex-col items-center group"
          >
            <div class="relative w-full flex justify-center">
              <!-- Tooltip -->
              <div
                class="opacity-0 group-hover:opacity-100 absolute bottom-full mb-1.5 bg-slate-800 text-white text-[11px] rounded-lg px-2 py-1 whitespace-nowrap pointer-events-none z-10 shadow-lg transition-opacity"
              >
                {{ h.count }} cars · {{ hourLabel(h.hour) }}
              </div>
              <!-- Bar -->
              <div
                class="w-full rounded-t-sm min-h-[2px] transition-colors"
                :class="h.count > 0
                  ? (h.count === maxHourCount ? 'bg-indigo-600' : 'bg-indigo-300 hover:bg-indigo-400')
                  : 'bg-slate-100'"
                :style="{ height: `${Math.max(2, (h.count / maxHourCount) * 88)}px` }"
              />
            </div>
          </div>
        </div>
        <!-- Hour labels (every 3rd) -->
        <div class="flex items-center gap-0.5 mt-1">
          <div
            v-for="h in report.by_hour"
            :key="h.hour"
            class="flex-1 text-center"
          >
            <span
              v-if="showHourLabel(h.hour)"
              class="text-[10px] text-slate-400"
            >{{ hourLabel(h.hour) }}</span>
          </div>
        </div>
      </div>

      <!-- ── Top Vehicles ── -->
      <div class="bg-white rounded-xl border border-slate-200">
        <div class="px-6 py-4 border-b border-slate-100">
          <h2 class="text-sm font-semibold text-slate-700">Top Vehicles</h2>
          <p class="text-xs text-slate-400 mt-0.5">Most frequent visitors this period</p>
        </div>
        <div v-if="report.top_vehicles.length === 0" class="p-10 text-center text-sm text-slate-400">
          No vehicle data for this period
        </div>
        <div v-else>
          <div class="overflow-x-auto">
            <table class="w-full min-w-[360px]">
              <thead>
                <tr class="border-b border-slate-100">
                  <th class="text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider px-6 py-3">#</th>
                  <th class="text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider px-6 py-3">Plate</th>
                  <th class="text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider px-6 py-3">Type</th>
                  <th class="text-right text-[11px] font-semibold text-slate-400 uppercase tracking-wider px-6 py-3">Visits</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100">
                <tr
                  v-for="(v, i) in report.top_vehicles"
                  :key="v.plate"
                  class="hover:bg-slate-50/50 transition-colors"
                >
                  <td class="px-6 py-3 text-sm text-slate-400 font-medium">{{ i + 1 }}</td>
                  <td class="px-6 py-3 text-sm font-bold text-slate-900 font-mono">{{ v.plate }}</td>
                  <td class="px-6 py-3">
                    <div class="flex items-center gap-1.5">
                      <div class="w-2 h-2 rounded-full" :class="vehicleColors[v.type] ?? 'bg-slate-400'" />
                      <span class="text-sm text-slate-600 capitalize">{{ v.type }}</span>
                    </div>
                  </td>
                  <td class="px-6 py-3 text-right">
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700">
                      {{ v.visit_count }} visit{{ v.visit_count !== 1 ? 's' : '' }}
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </template>

    <!-- No data yet -->
    <div
      v-else
      class="bg-white rounded-xl border border-slate-200 p-16 text-center"
    >
      <i class="pi pi-chart-bar text-5xl text-slate-200 mb-4" />
      <h3 class="text-lg font-semibold text-slate-600">Select a date range</h3>
      <p class="text-sm text-slate-400 mt-1">Choose a period above to view your analytics</p>
    </div>
  </div>
</template>
