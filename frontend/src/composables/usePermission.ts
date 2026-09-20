import { useAuthStore } from '@/stores/authStore'

export function usePermission() {
  const authStore = useAuthStore()

  function can(permission: string): boolean {
    return authStore.can(permission)
  }

  function hasRole(role: string): boolean {
    return authStore.roles?.includes(role) ?? false
  }

  function hasAnyRole(roles: string[]): boolean {
    return roles.some((role) => authStore.roles?.includes(role) ?? false)
  }

  function canAny(permissions: string[]): boolean {
    return permissions.some((p) => authStore.can(p))
  }

  function canAll(permissions: string[]): boolean {
    return permissions.every((p) => authStore.can(p))
  }

  return { can, hasRole, hasAnyRole, canAny, canAll }
}
