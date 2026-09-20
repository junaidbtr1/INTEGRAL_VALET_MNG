export interface ITenant {
  id: number
  name: string
  slug: string
  domain: string | null
  email: string | null
  phone: string | null
  address: string | null
  logo_url: string | null
  primary_color: string
  secondary_color: string
  accent_color: string
  status: string
  owner_id: number | null
  owner?: { id: number; name: string; email: string } | null
  plan: IPlan | null
  features: Record<string, boolean> | null
  settings: Record<string, unknown> | null
  trial_ends_at: string | null
  subscription_ends_at: string | null
  users_count?: number
  tickets_count?: number
  parking_slots_count?: number
  created_at: string
  updated_at: string
}

export interface IPlan {
  id: number
  name: string
  slug: string
  description: string | null
  price_monthly: number
  price_yearly: number
  currency: string
  max_slots: number
  max_staff_users: number
  max_tickets_per_day: number
  features: Record<string, boolean> | null
  is_active: boolean
  tenants_count?: number
  created_at: string
}

export interface IUser {
  id: number
  tenant_id: number | null
  name: string
  email: string
  phone: string | null
  is_super_admin: boolean
  is_building_owner: boolean
  is_active: boolean
  avatar_url: string | null
  timezone: string
  locale: string
  two_factor_enabled: boolean
  force_password_change: boolean
  last_login_at: string | null
  email_verified_at: string | null
  shift_id?: number | null
  shift?: { id: number; name: string } | null
  roles?: string[]
  permissions?: string[]
  tenant?: ITenant | null
  owned_buildings?: ITenant[]
  created_at: string
  updated_at: string
}

export interface IDashboardStats {
  total_tenants: number
  active_tenants: number
  trial_tenants: number
  suspended_tenants: number
  total_users: number
  total_plans: number
  new_tenants_this_month: number
}
