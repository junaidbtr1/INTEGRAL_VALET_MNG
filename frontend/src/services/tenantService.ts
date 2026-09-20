import api from '@/services/api'
import type { IApiResponse, IPaginatedResponse } from '@/types'
import type { IUser } from '@/types'

// ─── Types ───

export interface IRole {
  id: number
  name: string
  description: string | null
  permissions: string[]
  users_count: number
  is_system: boolean
  created_at: string
}

export interface IRolesResponse {
  system_roles: IRole[]
  custom_roles: IRole[]
}

export interface IPermissionsGrouped {
  [group: string]: string[]
}

export interface IShift {
  id: number
  name: string
  start_time: string
  end_time: string
  is_active: boolean
  staff_count: number
  created_at: string
}

export interface IParkingSlot {
  id: number
  tenant_id: number
  floor: string
  zone: string
  slot_number: string
  slot_type: string
  status: string
  vehicle_types_allowed: string[] | null
  is_covered: boolean
  current_ticket_id: number | null
  sort_order: number
}

export interface ISlotSummary {
  total: number
  available: number
  occupied: number
  reserved: number
  maintenance: number
  occupancy_percent: number
  floors: { floor: string; total: number; available: number }[]
}

export interface IVehicle {
  id: number
  plate_number: string
  vehicle_type: string
  color: string | null
  make: string | null
  model: string | null
  owner_name: string | null
  owner_phone: string | null
  is_vip: boolean
  is_blacklisted: boolean
  blacklist_reason: string | null
  visit_count: number
  last_visit_at: string | null
}

export interface ITicket {
  id: number
  ticket_number: string
  vehicle_plate: string
  vehicle_type: string
  vehicle_color: string | null
  vehicle_make: string | null
  status: string
  entry_at: string
  exit_at: string | null
  duration_minutes: number | null
  base_amount: number
  tax_amount: number
  discount_amount: number
  surcharge_amount: number
  total_amount: number
  notes: string | null
  parking_slot_id: number | null
  vehicle_id: number | null
  created_by: number
  closed_by: number | null
  creator?: { id: number; name: string }
  closer?: { id: number; name: string }
  parking_slot?: { id: number; slot_number: string; floor: string; zone: string }
  vehicle?: IVehicle
  created_at: string
}

export interface IPricing {
  duration_minutes: number
  base_amount: number
  tax_amount: number
  surcharge_amount: number
  discount_amount: number
  total_amount: number
}

export interface IPayment {
  id: number
  receipt_number: string
  amount: number
  currency: string
  payment_method: string
  status: string
  coupon_id: number | null
  coupon_discount_amount: number
  refund_of: number | null
  refund_reason: string | null
  notes: string | null
  ticket?: { id: number; ticket_number: string; vehicle_plate: string }
  processed_by?: { id: number; name: string }
  created_at: string
}

export interface ICoupon {
  id: number
  code: string
  type: string
  value: number
  min_amount: number
  max_discount: number | null
  usage_limit: number | null
  per_user_limit: number
  times_used: number
  applicable_vehicle_types: string[] | null
  valid_from: string | null
  valid_until: string | null
  is_active: boolean
  description: string | null
}

export interface IShiftReportEntry {
  staff: { id: number; name: string; role: string }
  shift: { id: number; name: string; start_time: string; end_time: string } | null
  stats: {
    tickets_created: number
    tickets_closed: number
    total_revenue: number
    avg_duration_minutes: number | null
    by_payment_method: { method: string; count: number; amount: number }[]
    by_vehicle_type: { type: string; count: number }[]
  }
}

export interface IReportSummary {
  total_revenue: number
  total_tickets: number
  completed_tickets: number
  avg_duration_minutes: number | null
  avg_ticket_value: number
}

export interface IRevenueByDay {
  date: string
  revenue: number
  tickets: number
}

export interface IByPaymentMethod {
  method: string
  count: number
  amount: number
}

export interface IByVehicleType {
  type: string
  count: number
}

export interface IByHour {
  hour: number
  count: number
}

export interface ITopVehicle {
  plate: string
  type: string
  visit_count: number
}

export interface IReportData {
  summary: IReportSummary
  revenue_by_day: IRevenueByDay[]
  by_payment_method: IByPaymentMethod[]
  by_vehicle_type: IByVehicleType[]
  by_hour: IByHour[]
  top_vehicles: ITopVehicle[]
}

export interface ITenantDashboard {
  stats: {
    tickets_today: number
    tickets_month: number
    active_vehicles: number
    total_slots: number
    available_slots: number
    occupied_slots: number
    occupancy_percent: number
    revenue_today: number
    revenue_yesterday: number
    revenue_month: number
  }
  recent_tickets: ITicket[]
}

// ─── Service ───

export const tenantService = {
  // Dashboard
  getDashboard: () =>
    api.get<unknown, IApiResponse<ITenantDashboard>>('/tenant/dashboard'),

  // Reports
  getReports: (params: { from: string; to: string }) =>
    api.get<unknown, IApiResponse<IReportData>>('/tenant/reports', { params }),
  downloadReport: (params: { from: string; to: string }) =>
    api.get('/tenant/reports/download', { params, responseType: 'blob' }),
  getShiftReport: (params: { from: string; to: string; staff_id?: number; shift_id?: number }) =>
    api.get<unknown, IApiResponse<IShiftReportEntry[]>>('/tenant/shift-reports', { params }),

  // Roles
  getRoles: () =>
    api.get<unknown, IApiResponse<IRolesResponse>>('/tenant/roles'),
  createRole: (data: { name: string; description?: string; permissions: string[] }) =>
    api.post<unknown, IApiResponse<IRole>>('/tenant/roles', data),
  updateRole: (id: number, data: { description?: string; permissions: string[] }) =>
    api.put<unknown, IApiResponse<IRole>>(`/tenant/roles/${id}`, data),
  deleteRole: (id: number) =>
    api.delete(`/tenant/roles/${id}`),
  getPermissions: () =>
    api.get<unknown, IApiResponse<IPermissionsGrouped>>('/tenant/permissions'),

  // Shifts
  getShifts: () =>
    api.get<unknown, IApiResponse<IShift[]>>('/tenant/shifts'),
  createShift: (data: { name: string; start_time: string; end_time: string; is_active?: boolean }) =>
    api.post<unknown, IApiResponse<IShift>>('/tenant/shifts', data),
  updateShift: (id: number, data: Partial<{ name: string; start_time: string; end_time: string; is_active: boolean }>) =>
    api.put<unknown, IApiResponse<IShift>>(`/tenant/shifts/${id}`, data),
  deleteShift: (id: number) =>
    api.delete(`/tenant/shifts/${id}`),

  // Staff
  getStaff: (params?: Record<string, unknown>) =>
    api.get<unknown, IPaginatedResponse<IUser>>('/tenant/staff', { params }),
  createStaff: (data: { name: string; email: string; phone?: string; password: string; role: string; shift_id: number }) =>
    api.post<unknown, IApiResponse<IUser>>('/tenant/staff', data),
  updateStaff: (id: number, data: { name?: string; email?: string; phone?: string; password?: string; role?: string; shift_id?: number | null; is_active?: boolean }) =>
    api.put<unknown, IApiResponse<IUser>>(`/tenant/staff/${id}`, data),
  deleteStaff: (id: number) =>
    api.delete(`/tenant/staff/${id}`),

  // Parking Slots
  getSlots: (params?: Record<string, unknown>) =>
    api.get<unknown, IApiResponse<IParkingSlot[]>>('/tenant/slots', { params }),
  getSlotSummary: () =>
    api.get<unknown, IApiResponse<ISlotSummary>>('/tenant/slots/summary'),
  createSlot: (data: Record<string, unknown>) =>
    api.post<unknown, IApiResponse<IParkingSlot>>('/tenant/slots', data),
  bulkCreateSlots: (data: Record<string, unknown>) =>
    api.post<unknown, IApiResponse<{ created: number; skipped: number }>>('/tenant/slots/bulk', data),
  updateSlot: (id: number, data: Record<string, unknown>) =>
    api.put<unknown, IApiResponse<IParkingSlot>>(`/tenant/slots/${id}`, data),
  updateSlotStatus: (id: number, status: string) =>
    api.patch<unknown, IApiResponse<IParkingSlot>>(`/tenant/slots/${id}/status`, { status }),
  deleteSlot: (id: number) =>
    api.delete(`/tenant/slots/${id}`),

  // Vehicles
  getVehicles: (params?: Record<string, unknown>) =>
    api.get<unknown, IPaginatedResponse<IVehicle>>('/tenant/vehicles', { params }),
  searchVehicles: (q: string) =>
    api.get<unknown, IApiResponse<IVehicle[]>>('/tenant/vehicles/search', { params: { q } }),
  createVehicle: (data: Record<string, unknown>) =>
    api.post<unknown, IApiResponse<IVehicle>>('/tenant/vehicles', data),
  updateVehicle: (id: number, data: Record<string, unknown>) =>
    api.put<unknown, IApiResponse<IVehicle>>(`/tenant/vehicles/${id}`, data),
  deleteVehicle: (id: number) =>
    api.delete(`/tenant/vehicles/${id}`),

  // Tickets
  getTickets: (params?: Record<string, unknown>) =>
    api.get<unknown, IPaginatedResponse<ITicket>>('/tenant/tickets', { params }),
  getTicket: (id: number) =>
    api.get<unknown, IApiResponse<{ ticket: ITicket; pricing: IPricing | null }>>(`/tenant/tickets/${id}`),
  createTicket: (data: { vehicle_plate: string; vehicle_type: string; vehicle_color?: string; vehicle_make?: string; parking_slot_id?: number; notes?: string }) =>
    api.post<unknown, IApiResponse<ITicket>>('/tenant/tickets', data),
  updateTicketStatus: (id: number, data: { status: string; notes?: string }) =>
    api.patch<unknown, IApiResponse<ITicket>>(`/tenant/tickets/${id}/status`, data),
  calculatePrice: (id: number) =>
    api.get<unknown, IApiResponse<IPricing>>(`/tenant/tickets/${id}/price`),
  scanTicket: (ticket_number: string) =>
    api.post<unknown, IApiResponse<{ ticket: ITicket; pricing: IPricing | null }>>('/tenant/tickets/scan', { ticket_number }),

  // Payments
  getPayments: (params?: Record<string, unknown>) =>
    api.get<unknown, IPaginatedResponse<IPayment>>('/tenant/payments', { params }),
  getPayment: (id: number) =>
    api.get<unknown, IApiResponse<IPayment>>(`/tenant/payments/${id}`),
  processPayment: (data: { ticket_id: number; payment_method: string; coupon_code?: string; notes?: string }) =>
    api.post<unknown, IApiResponse<IPayment>>('/tenant/payments', data),
  refundPayment: (id: number, reason: string) =>
    api.post<unknown, IApiResponse<IPayment>>(`/tenant/payments/${id}/refund`, { reason }),

  // Settings
  getSettings: () =>
    api.get<unknown, IApiResponse<Record<string, unknown>>>('/tenant/settings'),
  updateSettings: (data: Record<string, unknown>) =>
    api.patch<unknown, IApiResponse<Record<string, unknown>>>('/tenant/settings', data),

  // Coupons
  getCoupons: (params?: Record<string, unknown>) =>
    api.get<unknown, IApiResponse<ICoupon[]>>('/tenant/coupons', { params }),
  createCoupon: (data: Record<string, unknown>) =>
    api.post<unknown, IApiResponse<ICoupon>>('/tenant/coupons', data),
  updateCoupon: (id: number, data: Record<string, unknown>) =>
    api.put<unknown, IApiResponse<ICoupon>>(`/tenant/coupons/${id}`, data),
  deleteCoupon: (id: number) =>
    api.delete(`/tenant/coupons/${id}`),
  validateCoupon: (code: string) =>
    api.post<unknown, IApiResponse<ICoupon>>('/tenant/coupons/validate', { code }),
}
