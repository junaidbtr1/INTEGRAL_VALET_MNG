export interface IApiResponse<T> {
  success: boolean
  message: string
  data: T
}

export interface IApiErrorResponse {
  success: false
  message: string
  errors?: Record<string, string[]>
  error_code?: string
}

export interface IPaginationMeta {
  current_page: number
  from: number | null
  last_page: number
  per_page: number
  to: number | null
  total: number
}

export interface IPaginationLinks {
  first: string | null
  last: string | null
  prev: string | null
  next: string | null
}

export interface IPaginatedResponse<T> {
  success: boolean
  message: string
  data: T[]
  meta: IPaginationMeta | null
  links: IPaginationLinks | null
}

export interface ILoginPayload {
  email: string
  password: string
}

export interface ILoginResponse {
  token: string
  user: import('../models').IUser
}

export interface ICreateTenantPayload {
  name: string
  phone?: string
  address?: string
  plan_id?: number | null
  owner_id?: number | null
  trial_days?: number
}

export interface IUpdateTenantPayload {
  name?: string
  email?: string
  phone?: string
  address?: string
  plan_id?: number | null
  owner_id?: number | null
  status?: string
  logo_url?: string
  primary_color?: string
  secondary_color?: string
  accent_color?: string
  features?: Record<string, boolean>
  settings?: Record<string, unknown>
}

export interface ICreateUserPayload {
  tenant_id?: number | null
  is_building_owner?: boolean
  name: string
  email: string
  phone?: string
  password: string
  role?: 'tenant_admin'
  is_active?: boolean
}

export interface IUpdateUserPayload {
  name?: string
  email?: string
  phone?: string
  password?: string
  role?: 'tenant_admin'
  is_active?: boolean
}
