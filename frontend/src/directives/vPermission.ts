import { useAuthStore } from '@/stores/authStore'
import type { Directive } from 'vue'

/**
 * v-permission directive — hides element if user lacks the permission.
 * Usage: <button v-permission="'tickets.create'">Create</button>
 * Multi: <button v-permission="['tickets.create', 'tickets.update']">...</button>
 */
export const vPermission: Directive<HTMLElement, string | string[]> = {
  mounted(el, binding) {
    applyPermission(el, binding.value)
  },
  updated(el, binding) {
    applyPermission(el, binding.value)
  },
}

function applyPermission(el: HTMLElement, value: string | string[]): void {
  const authStore = useAuthStore()
  const permissions = Array.isArray(value) ? value : [value]
  const hasPermission = permissions.some((p) => authStore.can(p))

  if (!hasPermission) {
    el.style.display = 'none'
  } else {
    el.style.display = ''
  }
}
