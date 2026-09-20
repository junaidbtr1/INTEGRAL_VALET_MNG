import { computed } from 'vue'
import * as yup from 'yup'
import { useAuthStore } from '@/stores/authStore'

export function useBuildingGuard() {
  const authStore = useAuthStore()

  const buildingSchema = yup
    .number()
    .nullable()
    .test(
      'building-required',
      'Please select a building before continuing',
      (value) => {
        if (!authStore.isBuildingOwner) return true
        return value !== null && value !== undefined && value > 0
      },
    )

  const isBuildingOwner = computed(() => authStore.isBuildingOwner)
  const activeTenantId = computed(() => authStore.activeTenantId)

  const buildingError = computed<string>(() => {
    if (authStore.isBuildingOwner && !authStore.activeTenantId) {
      return 'Please select a building before continuing'
    }
    return ''
  })

  async function validateBuilding(): Promise<boolean> {
    try {
      await buildingSchema.validate(authStore.activeTenantId)
      return true
    } catch {
      return false
    }
  }

  return { buildingError, validateBuilding, isBuildingOwner, activeTenantId }
}
