const TICKET_STATUS_CLASSES: Record<string, string> = {
  active: 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/20',
  created: 'bg-blue-50 text-blue-700 ring-1 ring-blue-600/20',
  completed: 'bg-indigo-50 text-indigo-700 ring-1 ring-indigo-600/20',
  closed: 'bg-slate-100 text-slate-600 ring-1 ring-slate-400/20',
  cancelled: 'bg-red-50 text-red-700 ring-1 ring-red-600/20',
  lost_ticket: 'bg-amber-50 text-amber-700 ring-1 ring-amber-600/20',
  overstay: 'bg-orange-50 text-orange-700 ring-1 ring-orange-600/20',
  disputed: 'bg-rose-50 text-rose-700 ring-1 ring-rose-600/20',
}

const PAYMENT_METHOD_CLASSES: Record<string, string> = {
  cash: 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/20',
  card: 'bg-blue-50 text-blue-700 ring-1 ring-blue-600/20',
  digital: 'bg-purple-50 text-purple-700 ring-1 ring-purple-600/20',
}

const PAYMENT_STATUS_CLASSES: Record<string, string> = {
  completed: 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/20',
  refunded: 'bg-red-50 text-red-700 ring-1 ring-red-600/20',
  pending: 'bg-amber-50 text-amber-700 ring-1 ring-amber-600/20',
  failed: 'bg-slate-100 text-slate-500',
}

const TENANT_STATUS_CLASSES: Record<string, string> = {
  active: 'bg-emerald-50 text-emerald-700',
  trial: 'bg-blue-50 text-blue-700',
  suspended: 'bg-red-50 text-red-700',
  cancelled: 'bg-slate-100 text-slate-600',
}

const ROLE_CLASSES: Record<string, string> = {
  tenant_admin: 'bg-purple-100 text-purple-700',
  supervisor: 'bg-blue-100 text-blue-700',
  valet_staff: 'bg-green-100 text-green-700',
  cashier: 'bg-amber-100 text-amber-700',
  viewer: 'bg-gray-100 text-gray-700',
}

const SLOT_STATUS_BG: Record<string, string> = {
  available: 'border-emerald-200 bg-emerald-50 hover:bg-emerald-100',
  occupied: 'border-red-200 bg-red-50',
  reserved: 'border-amber-200 bg-amber-50',
  maintenance: 'border-slate-300 bg-slate-100',
  out_of_service: 'border-slate-300 bg-slate-200 opacity-50',
}

const SLOT_DOT_COLOR: Record<string, string> = {
  available: 'bg-emerald-500',
  occupied: 'bg-red-500',
  reserved: 'bg-amber-500',
  maintenance: 'bg-slate-400',
  out_of_service: 'bg-slate-300',
}

export function ticketStatusClass(status: string): string {
  return TICKET_STATUS_CLASSES[status] ?? 'bg-slate-100 text-slate-600'
}

export function paymentMethodClass(method: string): string {
  return PAYMENT_METHOD_CLASSES[method] ?? 'bg-slate-100 text-slate-600'
}

export function paymentStatusClass(status: string): string {
  return PAYMENT_STATUS_CLASSES[status] ?? 'bg-slate-100 text-slate-600'
}

export function tenantStatusClass(status: string): string {
  return TENANT_STATUS_CLASSES[status] ?? 'bg-slate-100 text-slate-600'
}

export function roleClass(role: string): string {
  return ROLE_CLASSES[role] ?? 'bg-indigo-100 text-indigo-700'
}

export function slotBgClass(status: string): string {
  return SLOT_STATUS_BG[status] ?? 'border-slate-200'
}

export function slotDotClass(status: string): string {
  return SLOT_DOT_COLOR[status] ?? 'bg-slate-300'
}
