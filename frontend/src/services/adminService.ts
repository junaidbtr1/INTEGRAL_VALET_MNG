import api from '@/services/api'
import type {
  IApiResponse,
  IPaginatedResponse,
  ICreateTenantPayload,
  IUpdateTenantPayload,
  ICreateUserPayload,
  IUpdateUserPayload,
} from '@/types'
import type { ITenant, IPlan, IUser, IDashboardStats } from '@/types'

export const adminService = {
  // Dashboard
  getDashboard: () =>
    api.get<unknown, IApiResponse<{ stats: IDashboardStats; recent_tenants: ITenant[] }>>('/admin/dashboard'),

  // Tenants
  getTenants: (params?: Record<string, unknown>) =>
    api.get<unknown, IPaginatedResponse<ITenant>>('/admin/tenants', { params }),

  getTenant: (id: number) =>
    api.get<unknown, IApiResponse<ITenant>>(`/admin/tenants/${id}`),

  createTenant: (data: ICreateTenantPayload) =>
    api.post<unknown, IApiResponse<ITenant>>('/admin/tenants', data),

  updateTenant: (id: number, data: IUpdateTenantPayload) =>
    api.put<unknown, IApiResponse<ITenant>>(`/admin/tenants/${id}`, data),

  deleteTenant: (id: number) =>
    api.delete(`/admin/tenants/${id}`),

  // Plans
  getPlans: () =>
    api.get<unknown, IApiResponse<IPlan[]>>('/admin/plans'),

  createPlan: (data: Partial<IPlan>) =>
    api.post<unknown, IApiResponse<IPlan>>('/admin/plans', data),

  updatePlan: (id: number, data: Partial<IPlan>) =>
    api.put<unknown, IApiResponse<IPlan>>(`/admin/plans/${id}`, data),

  deletePlan: (id: number) =>
    api.delete(`/admin/plans/${id}`),

  // Users
  getUsers: (params?: Record<string, unknown>) =>
    api.get<unknown, IPaginatedResponse<IUser>>('/admin/users', { params }),

  getUser: (id: number) =>
    api.get<unknown, IApiResponse<IUser>>(`/admin/users/${id}`),

  createUser: (data: ICreateUserPayload) =>
    api.post<unknown, IApiResponse<IUser>>('/admin/users', data),

  updateUser: (id: number, data: IUpdateUserPayload) =>
    api.put<unknown, IApiResponse<IUser>>(`/admin/users/${id}`, data),

  deleteUser: (id: number) =>
    api.delete(`/admin/users/${id}`),
}
