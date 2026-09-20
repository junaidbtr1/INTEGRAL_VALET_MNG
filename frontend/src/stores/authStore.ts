import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { authService } from '@/services/authService'
import type { IUser, ILoginPayload, ITenant } from '@/types'
import router from '@/router'

export const useAuthStore = defineStore(
  'auth',
  () => {
    const token = ref<string | null>(localStorage.getItem('token'))
    const user = ref<IUser | null>(null)
    const roles = ref<string[]>([])
    const permissions = ref<string[]>([])
    const isLoading = ref(false)
    const activeTenantId = ref<number | null>(
      sessionStorage.getItem('activeTenantId') ? Number(sessionStorage.getItem('activeTenantId')) : null,
    )

    const isAuthenticated = computed(() => !!token.value)
    const isSuperAdmin = computed(() => user.value?.is_super_admin ?? false)
    const isBuildingOwner = computed(() => user.value?.is_building_owner ?? false)
    const ownedBuildings = computed<ITenant[]>(() => user.value?.owned_buildings ?? [])
    const activeTenant = computed<ITenant | null>(
      () => ownedBuildings.value.find((t) => t.id === activeTenantId.value) ?? null,
    )

    async function login(payload: ILoginPayload) {
      isLoading.value = true
      try {
        const response = await authService.login(payload)
        token.value = response.data.token
        user.value = response.data.user
        localStorage.setItem('token', response.data.token)

        await fetchMe()

        if (isBuildingOwner.value) {
          router.push({ name: 'owner.buildings' })
        } else {
          router.push({ name: 'app.dashboard' })
        }
      } finally {
        isLoading.value = false
      }
    }

    async function fetchMe() {
      try {
        const response = await authService.me()
        user.value = response.data.user
        roles.value = response.data.roles
        permissions.value = response.data.permissions
      } catch {
        clearAuth()
      }
    }

    async function logout() {
      try {
        await authService.logout()
      } finally {
        clearAuth()
        router.push({ name: 'auth.login' })
      }
    }

    function clearAuth() {
      token.value = null
      user.value = null
      roles.value = []
      permissions.value = []
      activeTenantId.value = null
      localStorage.removeItem('token')
      sessionStorage.removeItem('activeTenantId')
    }

    function switchBuilding(tenantId: number): void {
      activeTenantId.value = tenantId
      sessionStorage.setItem('activeTenantId', String(tenantId))
    }

    function can(permission: string): boolean {
      // Super admins and building owners have full access — no permission gates
      if (user.value?.is_super_admin) return true
      if (user.value?.is_building_owner) return true
      return permissions.value.includes(permission)
    }

    function hasRole(role: string): boolean {
      // Building owners act as tenant_admin within their active building
      if (user.value?.is_building_owner) return role === 'tenant_admin' || roles.value.includes(role)
      return roles.value.includes(role)
    }

    return {
      token,
      user,
      roles,
      permissions,
      isLoading,
      activeTenantId,
      isAuthenticated,
      isSuperAdmin,
      isBuildingOwner,
      ownedBuildings,
      activeTenant,
      login,
      fetchMe,
      logout,
      clearAuth,
      switchBuilding,
      can,
      hasRole,
    }
  },
  {
    persist: {
      pick: ['token'],
    },
  },
)
