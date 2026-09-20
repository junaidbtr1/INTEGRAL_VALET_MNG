<script setup lang="ts">
import { ref, onMounted, watch } from 'vue'
import { debounce } from 'lodash-es'
import AppTable from '@/components/ui/AppTable.vue'
import AppConfirmDialog from '@/components/ui/AppConfirmDialog.vue'
import { tenantService } from '@/services/tenantService'
import type { IPayment } from '@/services/tenantService'
import BuildingSelector from '@/components/ui/BuildingSelector.vue'
import { useToast } from 'primevue/usetoast'
import { isAxiosError } from 'axios'
import { formatCurrency, formatDate } from '@/utils/formatters'
import { useBuildingGuard } from '@/composables/useBuildingGuard'
import { usePermission } from '@/composables/usePermission'

const toast = useToast()
const { isBuildingOwner, activeTenantId } = useBuildingGuard()
const { can } = usePermission()

const showRefundDialog = ref(false)
const pendingRefundPayment = ref<IPayment | null>(null)
const payments = ref<IPayment[]>([])
const isLoading = ref(true)
const search = ref('')
const methodFilter = ref('')
const meta = ref<{ current_page: number; last_page: number; total: number } | null>(null)
const currentPage = ref(1)

async function fetchPayments(page = 1) {
  isLoading.value = true
  try {
    const params: Record<string, unknown> = { page, per_page: 15 }
    if (search.value) params.search = search.value
    if (methodFilter.value) params.payment_method = methodFilter.value
    const response = await tenantService.getPayments(params)
    payments.value = response.data
    meta.value = response.meta as typeof meta.value
    currentPage.value = page
  } finally { isLoading.value = false }
}

const debouncedSearch = debounce(() => fetchPayments(1), 300)
watch(search, () => debouncedSearch())
watch(methodFilter, () => fetchPayments(1))
watch(activeTenantId, (newId) => { if (isBuildingOwner.value && newId) fetchPayments(1) })

function openRefundDialog(payment: IPayment) {
  pendingRefundPayment.value = payment
  showRefundDialog.value = true
}

async function confirmRefund(reason?: string) {
  if (!pendingRefundPayment.value || !reason) return
  try {
    await tenantService.refundPayment(pendingRefundPayment.value.id, reason)
    toast.add({ severity: 'success', summary: 'Refunded', detail: 'Payment refunded successfully', life: 3000 })
    await fetchPayments(currentPage.value)
  } catch (error) {
    if (isAxiosError(error) && error.response?.data?.message) {
      toast.add({ severity: 'error', summary: 'Error', detail: error.response.data.message, life: 5000 })
    }
  } finally {
    pendingRefundPayment.value = null
  }
}

function getMethodBadge(method: string): string {
  const map: Record<string, string> = {
    cash: 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/20',
    card: 'bg-blue-50 text-blue-700 ring-1 ring-blue-600/20',
    digital: 'bg-purple-50 text-purple-700 ring-1 ring-purple-600/20',
  }
  return map[method] ?? 'bg-slate-100 text-slate-600'
}

function getStatusBadge(status: string): string {
  const map: Record<string, string> = {
    completed: 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/20',
    refunded: 'bg-red-50 text-red-700 ring-1 ring-red-600/20',
    pending: 'bg-amber-50 text-amber-700 ring-1 ring-amber-600/20',
    failed: 'bg-slate-100 text-slate-500',
  }
  return map[status] ?? 'bg-slate-100 text-slate-600'
}

onMounted(() => fetchPayments())
</script>

<template>
  <div>
    <BuildingSelector mode="filter" />

    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
      <div>
        <h1 class="text-2xl font-bold text-slate-900">Payments</h1>
        <p class="text-sm text-slate-500 mt-1">Payment history and receipts</p>
      </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl border border-slate-200 p-4 mb-6 flex gap-4 flex-wrap">
      <div class="flex-1 min-w-[200px]">
        <input v-model="search" type="text" placeholder="Search by receipt, ticket, or plate..." class="w-full h-10 border border-slate-300 rounded-lg px-4 text-sm outline-none hover:border-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/10 transition-all" />
      </div>
      <select v-model="methodFilter" class="h-10 border border-slate-300 rounded-lg px-4 text-sm outline-none focus:border-indigo-500 transition-all">
        <option value="">All Methods</option>
        <option value="cash">Cash</option>
        <option value="card">Card</option>
        <option value="digital">Digital</option>
      </select>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl border border-slate-200">
      <div v-if="isLoading" class="p-16 text-center"><i class="pi pi-spinner pi-spin text-3xl text-slate-300" /></div>
      <div v-else-if="payments.length === 0" class="p-16 text-center">
        <i class="pi pi-wallet text-5xl text-slate-200 mb-4" />
        <h3 class="text-lg font-semibold text-slate-600">No payments found</h3>
        <p class="text-sm text-slate-400 mt-1">Payments are recorded when vehicles check out</p>
      </div>
      <template v-else>
        <AppTable min-width="min-w-[640px]">
          <thead>
            <tr class="border-b border-slate-100">
              <th class="text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider px-6 py-3">Receipt</th>
              <th class="text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider px-6 py-3">Ticket</th>
              <th class="text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider px-6 py-3">Amount</th>
              <th class="hidden sm:table-cell text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider px-6 py-3">Method</th>
              <th class="hidden md:table-cell text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider px-6 py-3">Status</th>
              <th class="hidden lg:table-cell text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider px-6 py-3">Processed By</th>
              <th class="hidden lg:table-cell text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider px-6 py-3">Date</th>
              <th class="text-right text-[11px] font-semibold text-slate-400 uppercase tracking-wider px-6 py-3">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr v-for="p in payments" :key="p.id" class="hover:bg-slate-50/50 transition-colors">
              <td class="px-6 py-4 text-sm font-semibold text-slate-900 font-mono">{{ p.receipt_number }}</td>
              <td class="px-6 py-4">
                <p class="text-sm text-slate-700">{{ p.ticket?.ticket_number ?? '—' }}</p>
                <p class="text-xs text-slate-400">{{ p.ticket?.vehicle_plate ?? '' }}</p>
              </td>
              <td class="px-6 py-4 text-sm font-semibold text-slate-900">
                {{ formatCurrency(p.amount) }}
                <span v-if="p.coupon_discount_amount > 0" class="text-xs text-emerald-600 ml-1">(-{{ formatCurrency(p.coupon_discount_amount) }})</span>
              </td>
              <td class="hidden sm:table-cell px-6 py-4">
                <span class="inline-flex px-2.5 py-1 rounded-md text-[11px] font-semibold capitalize" :class="getMethodBadge(p.payment_method)">{{ p.payment_method }}</span>
              </td>
              <td class="hidden md:table-cell px-6 py-4">
                <span class="inline-flex px-2.5 py-1 rounded-md text-[11px] font-semibold capitalize" :class="getStatusBadge(p.status)">{{ p.status }}</span>
              </td>
              <td class="hidden lg:table-cell px-6 py-4 text-sm text-slate-500">{{ p.processed_by?.name ?? '—' }}</td>
              <td class="hidden lg:table-cell px-6 py-4 text-sm text-slate-500">{{ formatDate(p.created_at) }}</td>
              <td class="px-6 py-4 text-right">
                <button
                  v-if="p.status === 'completed' && can('payments.refund')"
                  class="p-1.5 rounded-md text-slate-400 hover:text-red-600 hover:bg-red-50 transition-colors"
                  title="Refund"
                  @click="openRefundDialog(p)"
                >
                  <i class="pi pi-replay text-sm" />
                </button>
              </td>
            </tr>
          </tbody>
        </AppTable>
        <div v-if="meta && meta.last_page > 1" class="flex items-center justify-between px-6 py-4 border-t border-slate-100">
          <p class="text-sm text-slate-400">Page {{ currentPage }} of {{ meta.last_page }}</p>
          <div class="flex gap-2">
            <button class="px-3 py-1.5 text-sm border border-slate-300 rounded-lg hover:bg-slate-50 disabled:opacity-40" :disabled="currentPage <= 1" @click="fetchPayments(currentPage - 1)">Previous</button>
            <button class="px-3 py-1.5 text-sm border border-slate-300 rounded-lg hover:bg-slate-50 disabled:opacity-40" :disabled="currentPage >= meta.last_page" @click="fetchPayments(currentPage + 1)">Next</button>
          </div>
        </div>
      </template>
    </div>

    <AppConfirmDialog
      v-model="showRefundDialog"
      title="Refund Payment"
      :message="`Refund ${pendingRefundPayment ? formatCurrency(pendingRefundPayment.amount) : ''} — ${pendingRefundPayment?.receipt_number ?? ''}?`"
      confirm-label="Process Refund"
      input-label="Reason for refund"
      input-placeholder="e.g. Customer request, duplicate charge..."
      @confirm="confirmRefund"
    />
  </div>
</template>
