<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useAuthStore } from '@/stores/authStore'
import { tenantService } from '@/services/tenantService'
import type { IShiftReportEntry, IShift } from '@/services/tenantService'
import { formatCurrency, formatDuration } from '@/utils/formatters'

const authStore = useAuthStore()

// ─── Date range ───
type Preset = 'today' | 'yesterday' | 'week'
const preset = ref<Preset>('today')

function toLocalDate(d: Date): string {
  return d.toISOString().slice(0, 10)
}

const dateRange = computed(() => {
  const now = new Date()
  if (preset.value === 'today') {
    const d = toLocalDate(now)
    return { from: d, to: d }
  }
  if (preset.value === 'yesterday') {
    const y = new Date(now)
    y.setDate(now.getDate() - 1)
    const d = toLocalDate(y)
    return { from: d, to: d }
  }
  const mon = new Date(now)
  mon.setDate(now.getDate() - now.getDay() + 1)
  return { from: toLocalDate(mon), to: toLocalDate(now) }
})

const presetLabels: Record<Preset, string> = {
  today: 'Today',
  yesterday: 'Yesterday',
  week: 'This Week',
}

// ─── Filters ───
const canViewAll = computed(() => authStore.can('users.view') || authStore.isBuildingOwner)
const selectedStaffId = ref<number | ''>('')
const selectedShiftId = ref<number | ''>('')

// ─── Static data (shifts + staff list for filters) ───
const shifts = ref<IShift[]>([])
const staffList = ref<{ id: number; name: string }[]>([])

// ─── Report data ───
const entries = ref<IShiftReportEntry[]>([])
const isLoading = ref(false)
const error = ref('')
const expandedStaffId = ref<number | null>(null)

async function load() {
  const { from, to } = dateRange.value
  isLoading.value = true
  error.value = ''
  try {
    const params: { from: string; to: string; staff_id?: number; shift_id?: number } = { from, to }
    if (selectedStaffId.value !== '') params.staff_id = selectedStaffId.value as number
    if (selectedShiftId.value !== '') params.shift_id = selectedShiftId.value as number
    const res = await tenantService.getShiftReport(params)
    entries.value = res.data
  } catch {
    error.value = 'Failed to load shift report.'
  } finally {
    isLoading.value = false
  }
}

function applyPreset(p: Preset) {
  preset.value = p
  load()
}

function toggleExpand(staffId: number) {
  expandedStaffId.value = expandedStaffId.value === staffId ? null : staffId
}

// ─── Color maps ───
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

const roleColors: Record<string, string> = {
  tenant_admin: 'bg-violet-100 text-violet-700',
  supervisor: 'bg-blue-100 text-blue-700',
  valet_staff: 'bg-emerald-100 text-emerald-700',
  cashier: 'bg-amber-100 text-amber-700',
  viewer: 'bg-slate-100 text-slate-600',
  staff: 'bg-slate-100 text-slate-600',
}

function roleLabel(role: string): string {
  return role.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase())
}

function totalMethodCount(entry: IShiftReportEntry): number {
  return entry.stats.by_payment_method.reduce((s, m) => s + m.count, 0) || 1
}

function totalVehicleCount(entry: IShiftReportEntry): number {
  return entry.stats.by_vehicle_type.reduce((s, v) => s + v.count, 0) || 1
}

onMounted(async () => {
  load()
  const [shiftRes] = await Promise.all([
    tenantService.getShifts(),
  ])
  shifts.value = shiftRes.data

  if (canViewAll.value) {
    const staffRes = await tenantService.getStaff({ per_page: 100 })
    staffList.value = staffRes.data.map((u: { id: number; name: string }) => ({ id: u.id, name: u.name }))
  }
})
</script>

<template>
  <div>
    <!-- Header -->
    <div class="flex items-start justify-between mb-6 flex-wrap gap-4">
      <div>
        <h1 class="text-2xl font-bold text-slate-900">Shift Reports</h1>
        <p class="text-sm text-slate-500 mt-1">Staff performance for {{ presetLabels[preset] }}</p>
      </div>

      <!-- Controls -->
      <div class="flex flex-wrap items-center gap-2">
        <!-- Filters (admin/owner only) -->
        <template v-if="canViewAll">
          <select
            v-model="selectedShiftId"
            class="h-9 border border-slate-300 rounded-lg px-3 text-sm outline-none focus:border-indigo-500 bg-white transition-all"
            @change="load"
          >
            <option value="">All Shifts</option>
            <option v-for="s in shifts" :key="s.id" :value="s.id">{{ s.name }}</option>
          </select>
          <select
            v-model="selectedStaffId"
            class="h-9 border border-slate-300 rounded-lg px-3 text-sm outline-none focus:border-indigo-500 bg-white transition-all"
            @change="load"
          >
            <option value="">All Staff</option>
            <option v-for="s in staffList" :key="s.id" :value="s.id">{{ s.name }}</option>
          </select>
        </template>

        <!-- Date presets -->
        <div class="flex bg-slate-100 rounded-lg p-1 gap-1">
          <button
            v-for="p in (['today', 'yesterday', 'week'] as Preset[])"
            :key="p"
            class="px-3 py-1.5 rounded-md text-xs font-medium transition-all"
            :class="preset === p
              ? 'bg-white text-slate-800 shadow-sm'
              : 'text-slate-500 hover:text-slate-700'"
            @click="applyPreset(p)"
          >
            {{ presetLabels[p] }}
          </button>
        </div>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="isLoading" class="flex items-center justify-center py-24">
      <i class="pi pi-spinner pi-spin text-3xl text-slate-300" />
    </div>

    <!-- Error -->
    <div v-else-if="error" class="bg-red-50 border border-red-200 rounded-xl p-6 text-center text-red-600">
      <i class="pi pi-exclamation-triangle text-2xl mb-2 block" />
      <p class="text-sm">{{ error }}</p>
      <button class="mt-3 text-sm text-red-700 underline" @click="load">Retry</button>
    </div>

    <!-- Empty -->
    <div
      v-else-if="entries.length === 0"
      class="bg-white rounded-xl border border-slate-200 p-16 text-center"
    >
      <i class="pi pi-users text-5xl text-slate-200 mb-4 block" />
      <h3 class="text-lg font-semibold text-slate-600">No activity found</h3>
      <p class="text-sm text-slate-400 mt-1">No staff recorded any tickets or payments for this period</p>
    </div>

    <!-- Cards -->
    <div v-else class="space-y-4">
      <div
        v-for="entry in entries"
        :key="entry.staff.id"
        class="bg-white rounded-xl border border-slate-200 overflow-hidden"
      >
        <!-- Card header -->
        <div class="px-6 py-4">
          <div class="flex items-start justify-between gap-4 flex-wrap">
            <!-- Staff info -->
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-full bg-indigo-100 flex items-center justify-center shrink-0">
                <span class="text-sm font-bold text-indigo-600">
                  {{ entry.staff.name.split(' ').map((n: string) => n[0]).join('').substring(0, 2).toUpperCase() }}
                </span>
              </div>
              <div>
                <div class="flex items-center gap-2 flex-wrap">
                  <span class="font-semibold text-slate-900">{{ entry.staff.name }}</span>
                  <span
                    class="text-[11px] font-medium px-2 py-0.5 rounded-full"
                    :class="roleColors[entry.staff.role] ?? 'bg-slate-100 text-slate-600'"
                  >
                    {{ roleLabel(entry.staff.role) }}
                  </span>
                  <span
                    v-if="entry.shift"
                    class="text-[11px] font-medium px-2 py-0.5 rounded-full bg-slate-100 text-slate-600"
                  >
                    <i class="pi pi-clock text-[10px] mr-1" />{{ entry.shift.name }}
                    ({{ entry.shift.start_time }} – {{ entry.shift.end_time }})
                  </span>
                  <span v-else class="text-[11px] text-slate-400">No shift assigned</span>
                </div>
              </div>
            </div>

            <!-- Quick stats -->
            <div class="flex items-center gap-5 flex-wrap text-center">
              <div>
                <p class="text-xl font-bold text-slate-900">{{ entry.stats.tickets_created }}</p>
                <p class="text-[11px] text-slate-400 mt-0.5">Tickets</p>
              </div>
              <div class="w-px h-8 bg-slate-200" />
              <div>
                <p class="text-xl font-bold text-slate-900">{{ formatCurrency(entry.stats.total_revenue) }}</p>
                <p class="text-[11px] text-slate-400 mt-0.5">Revenue</p>
              </div>
              <div class="w-px h-8 bg-slate-200" />
              <div>
                <p class="text-xl font-bold text-slate-900">{{ entry.stats.tickets_closed }}</p>
                <p class="text-[11px] text-slate-400 mt-0.5">Closed</p>
              </div>
              <div class="w-px h-8 bg-slate-200" />
              <div>
                <p class="text-xl font-bold text-slate-900">
                  {{ entry.stats.avg_duration_minutes ? formatDuration(entry.stats.avg_duration_minutes) : '—' }}
                </p>
                <p class="text-[11px] text-slate-400 mt-0.5">Avg Duration</p>
              </div>
            </div>
          </div>

          <!-- Tag row -->
          <div class="flex flex-wrap gap-2 mt-3">
            <span
              v-for="m in entry.stats.by_payment_method"
              :key="m.method"
              class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-slate-100 text-slate-700"
            >
              <span class="w-1.5 h-1.5 rounded-full" :class="methodColors[m.method] ?? 'bg-slate-400'" />
              {{ m.method.charAt(0).toUpperCase() + m.method.slice(1) }}: {{ m.count }} · {{ formatCurrency(m.amount) }}
            </span>
            <span
              v-for="v in entry.stats.by_vehicle_type"
              :key="v.type"
              class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-slate-50 text-slate-600 border border-slate-200"
            >
              <span class="w-1.5 h-1.5 rounded-full" :class="vehicleColors[v.type] ?? 'bg-slate-400'" />
              {{ v.type.charAt(0).toUpperCase() + v.type.slice(1) }}: {{ v.count }}
            </span>
          </div>

          <!-- Expand toggle -->
          <button
            v-if="entry.stats.by_payment_method.length > 0 || entry.stats.by_vehicle_type.length > 0"
            class="mt-3 flex items-center gap-1 text-xs text-slate-400 hover:text-slate-600 transition-colors"
            @click="toggleExpand(entry.staff.id)"
          >
            <i
              class="pi transition-transform"
              :class="expandedStaffId === entry.staff.id ? 'pi-angle-up' : 'pi-angle-down'"
            />
            {{ expandedStaffId === entry.staff.id ? 'Hide details' : 'View details' }}
          </button>
        </div>

        <!-- Expanded details -->
        <div
          v-if="expandedStaffId === entry.staff.id"
          class="border-t border-slate-100 px-6 py-5 bg-slate-50/60"
        >
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Payment methods breakdown -->
            <div v-if="entry.stats.by_payment_method.length > 0">
              <h3 class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-3">Payment Methods</h3>
              <div class="space-y-2.5">
                <div
                  v-for="m in entry.stats.by_payment_method"
                  :key="m.method"
                  class="flex items-center gap-3"
                >
                  <div class="w-2 h-2 rounded-full shrink-0" :class="methodColors[m.method] ?? 'bg-slate-400'" />
                  <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between mb-1">
                      <span class="text-sm font-medium text-slate-700 capitalize">{{ m.method }}</span>
                      <span class="text-xs text-slate-500">{{ m.count }} · {{ formatCurrency(m.amount) }}</span>
                    </div>
                    <div class="h-1.5 bg-slate-200 rounded-full overflow-hidden">
                      <div
                        class="h-full rounded-full"
                        :class="methodColors[m.method] ?? 'bg-slate-400'"
                        :style="{ width: `${(m.count / totalMethodCount(entry)) * 100}%` }"
                      />
                    </div>
                  </div>
                  <span class="text-xs font-semibold text-slate-600 w-9 text-right shrink-0">
                    {{ Math.round((m.count / totalMethodCount(entry)) * 100) }}%
                  </span>
                </div>
              </div>
            </div>

            <!-- Vehicle types breakdown -->
            <div v-if="entry.stats.by_vehicle_type.length > 0">
              <h3 class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-3">Vehicle Types</h3>
              <div class="space-y-2.5">
                <div
                  v-for="v in entry.stats.by_vehicle_type"
                  :key="v.type"
                  class="flex items-center gap-3"
                >
                  <div class="w-2 h-2 rounded-full shrink-0" :class="vehicleColors[v.type] ?? 'bg-slate-400'" />
                  <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between mb-1">
                      <span class="text-sm font-medium text-slate-700 capitalize">{{ v.type }}</span>
                      <span class="text-xs text-slate-500">{{ v.count }} tickets</span>
                    </div>
                    <div class="h-1.5 bg-slate-200 rounded-full overflow-hidden">
                      <div
                        class="h-full rounded-full"
                        :class="vehicleColors[v.type] ?? 'bg-slate-400'"
                        :style="{ width: `${(v.count / totalVehicleCount(entry)) * 100}%` }"
                      />
                    </div>
                  </div>
                  <span class="text-xs font-semibold text-slate-600 w-9 text-right shrink-0">
                    {{ Math.round((v.count / totalVehicleCount(entry)) * 100) }}%
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
