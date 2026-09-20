import api from '@/services/api'
import type { IApiResponse, ILoginPayload, ILoginResponse } from '@/types'

export const authService = {
  login: (data: ILoginPayload) => api.post<unknown, IApiResponse<ILoginResponse>>('/auth/login', data),

  me: () => api.get<unknown, IApiResponse<{ user: import('@/types').IUser; roles: string[]; permissions: string[] }>>('/auth/me'),

  logout: () => api.post('/auth/logout'),

  logoutAll: () => api.post('/auth/logout-all'),

  refresh: () => api.post<unknown, IApiResponse<{ token: string }>>('/auth/refresh'),

  changePassword: (data: { current_password: string; password: string; password_confirmation: string }) =>
    api.put('/auth/change-password', data),
}
