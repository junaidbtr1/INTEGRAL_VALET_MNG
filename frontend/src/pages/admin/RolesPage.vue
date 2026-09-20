<script setup lang="ts">
import { ref, onMounted, watch } from 'vue'
import AppConfirmDialog from '@/components/ui/AppConfirmDialog.vue'
import BuildingSelector from '@/components/ui/BuildingSelector.vue'
import { tenantService } from '@/services/tenantService'
import type { IRole, IPermissionsGrouped } from '@/services/tenantService'
import { useToast } from 'primevue/usetoast'
import { isAxiosError } from 'axios'
import { useBuildingGuard } from '@/composables/useBuildingGuard'
import { usePermission } from '@/composables/usePermission'

const { validateBuilding, isBuildingOwner, activeTenantId } = useBuildingGuard()
const { can } = usePermission()

const toast = useToast()
const systemRoles = ref<IRole[]>([])
const customRoles = ref<IRole[]>([])
const allPermissions = ref<IPermissionsGrouped>({})
const isLoading = ref(true)

// Dialog state
const showDialog = ref(false)
const isEditing = ref(false)
const editingRoleId = ref<number | null>(null)
const showDeleteDialog = ref(false)
const pendingDelete = ref<IRole | null>(null)
const isSaving = ref(false)

// Form
const formName = ref('')
const formDescription = ref('')
const formPermissions = ref<string[]>([])
const formErrors = ref<Record<string, string>>({})

// Detail panel (click to expand permissions)
const expandedRole = ref<number | null>(null)

const systemRoleColors: Record<string, { bg: string; text: string; icon: string }> = {
  supervisor:  { bg: 'bg-blue-100',  text: 'text-blue-700',  icon: 'pi-eye' },
  valet_staff: { bg: 'bg-green-100', text: 'text-green-700', icon: 'pi-car' },
  cashier:     { bg: 'bg-amber-100', text: 'text-amber-700', icon: 'pi-wallet' },
  viewer:      { bg: 'bg-gray-100',  text: 'text-gray-600',  icon: 'pi-chart-bar' },
}

async function fetchData() {
  isLoading.value = true
  try {
    const [rolesRes, permsRes] = await Promise.all([
      tenantService.getRoles(),
      tenantService.getPermissions(),
    ])
    systemRoles.value = rolesRes.data.system_roles
    customRoles.value = rolesRes.data.custom_roles
    allPermissions.value = permsRes.data
  } finally {
    isLoading.value = false
  }
}

function openCreateDialog() {
  isEditing.value = false
  editingRoleId.value = null
  formName.value = ''
  formDescription.value = ''
  formPermissions.value = []
  formErrors.value = {}
  showDialog.value = true
}

function openEditDialog(role: IRole) {
  isEditing.value = true
  editingRoleId.value = role.id
  formName.value = role.name
  formDescription.value = role.description ?? ''
  formPermissions.value = [...role.permissions]
  formErrors.value = {}
  showDialog.value = true
}

function togglePermission(perm: string) {
  const idx = formPermissions.value.indexOf(perm)
  if (idx >= 0) {
    formPermissions.value.splice(idx, 1)
  } else {
    formPermissions.value.push(perm)
  }
}

function toggleGroup(group: string) {
  const groupPerms = allPermissions.value[group] ?? []
  const allSelected = groupPerms.every(p => formPermissions.value.includes(p))
  if (allSelected) {
    formPermissions.value = formPermissions.value.filter(p => !groupPerms.includes(p))
  } else {
    formPermissions.value.push(...groupPerms.filter(p => !formPermissions.value.includes(p)))
  }
}

function isGroupSelected(group: string): boolean {
  return (allPermissions.value[group] ?? []).every(p => formPermissions.value.includes(p))
}

function isGroupPartial(group: string): boolean {
  const g = allPermissions.value[group] ?? []
  const n = g.filter(p => formPermissions.value.includes(p)).length
  return n > 0 && n < g.length
}

async function saveRole() {
  formErrors.value = {}
  if (!await validateBuilding()) return
  if (!isEditing.value && !formName.value.trim()) {
    formErrors.value.name = 'Role name is required'
    return
  }
  if (formPermissions.value.length === 0) {
    formErrors.value.permissions = 'Select at least one permission'
    return
  }

  isSaving.value = true
  try {
    if (isEditing.value && editingRoleId.value) {
      await tenantService.updateRole(editingRoleId.value, {
        description: formDescription.value || undefined,
        permissions: formPermissions.value,
      })
      toast.add({ severity: 'success', summary: 'Updated', detail: 'Role updated', life: 3000 })
    } else {
      await tenantService.createRole({
        name: formName.value,
        description: formDescription.value || undefined,
        permissions: formPermissions.value,
      })
      toast.add({ severity: 'success', summary: 'Created', detail: 'Role created', life: 3000 })
    }
    showDialog.value = false
    await fetchData()
  } catch (error) {
    if (isAxiosError(error) && error.response?.status === 422) {
      const errs = error.response.data.errors ?? {}
      for (const key in errs) formErrors.value[key] = errs[key][0]
    } else {
      toast.add({ severity: 'error', summary: 'Error', detail: 'Failed to save role', life: 5000 })
    }
  } finally {
    isSaving.value = false
  }
}

function openDeleteDialog(role: IRole) {
  pendingDelete.value = role
  showDeleteDialog.value = true
}

async function confirmDeleteRole() {
  if (!pendingDelete.value) return
  try {
    await tenantService.deleteRole(pendingDelete.value.id)
    toast.add({ severity: 'success', summary: 'Deleted', detail: 'Role deleted', life: 3000 })
    await fetchData()
  } catch (error) {
    if (isAxiosError(error) && error.response?.data?.message) {
      toast.add({ severity: 'error', summary: 'Error', detail: error.response.data.message, life: 5000 })
    }
  } finally {
    pendingDelete.value = null
  }
}

function formatPermName(perm: string): string {
  return perm.split('.').pop()?.replace(/_/g, ' ') ?? perm
}

function formatRoleName(name: string): string {
  return name.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase())
}

function toggleExpand(id: number) {
  expandedRole.value = expandedRole.value === id ? null : id
}

watch(activeTenantId, (newId) => { if (isBuildingOwner.value && newId) fetchData() })
onMounted(fetchData)
</script>

<template>
  <div>
    <BuildingSelector mode="filter" />

    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Roles & Permissions</h1>
        <p class="text-sm text-gray-500 mt-1">General staff roles are built-in and available to all tenants. Create custom roles for specific needs.</p>
      </div>
      <button
        v-if="can('settings.update')"
        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium flex items-center gap-2"
        @click="openCreateDialog"
      >
        <i class="pi pi-plus" /> New Role
      </button>
    </div>

    <!-- Loading -->
    <div v-if="isLoading" class="p-12 text-center">
      <i class="pi pi-spinner pi-spin text-3xl text-gray-400" />
    </div>

    <div v-else class="space-y-8">

      <!-- ─── System Roles ─── -->
      <div>
        <div class="flex items-center gap-2 mb-4">
          <i class="pi pi-shield text-gray-400" />
          <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wide">General Staff Roles</h2>
          <span class="text-xs text-gray-400 bg-gray-100 px-2 py-0.5 rounded-full">Built-in · Read-only</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          <div
            v-for="role in systemRoles"
            :key="role.id"
            class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden"
          >
            <!-- Card header -->
            <div class="p-5">
              <div class="flex items-center gap-3 mb-3">
                <div
                  class="w-10 h-10 rounded-full flex items-center justify-center shrink-0"
                  :class="systemRoleColors[role.name]?.bg ?? 'bg-gray-100'"
                >
                  <i
                    class="pi text-base"
                    :class="[systemRoleColors[role.name]?.icon ?? 'pi-user', systemRoleColors[role.name]?.text ?? 'text-gray-600']"
                  />
                </div>
                <div class="min-w-0">
                  <div class="flex items-center gap-2">
                    <h3 class="font-semibold text-gray-900 text-sm">{{ formatRoleName(role.name) }}</h3>
                    <span class="text-xs bg-gray-100 text-gray-500 px-1.5 py-0.5 rounded">system</span>
                  </div>
                  <p v-if="role.description" class="text-xs text-gray-500 mt-0.5 truncate">{{ role.description }}</p>
                </div>
              </div>

              <div class="flex items-center gap-4 text-xs text-gray-500 mb-3">
                <span><i class="pi pi-users mr-1" />{{ role.users_count }} users</span>
                <span><i class="pi pi-key mr-1" />{{ role.permissions.length }} permissions</span>
              </div>

              <!-- Permission preview tags -->
              <div class="flex flex-wrap gap-1">
                <span
                  v-for="perm in role.permissions.slice(0, 5)"
                  :key="perm"
                  class="inline-flex px-1.5 py-0.5 rounded text-xs"
                  :class="[systemRoleColors[role.name]?.bg ?? 'bg-gray-100', systemRoleColors[role.name]?.text ?? 'text-gray-600']"
                >
                  {{ formatPermName(perm) }}
                </span>
                <button
                  v-if="role.permissions.length > 5"
                  class="inline-flex px-1.5 py-0.5 rounded text-xs bg-gray-100 text-gray-500 hover:bg-gray-200"
                  @click="toggleExpand(role.id)"
                >
                  {{ expandedRole === role.id ? 'show less' : `+${role.permissions.length - 5} more` }}
                </button>
              </div>

              <!-- Expanded permissions -->
              <div v-if="expandedRole === role.id" class="mt-3 pt-3 border-t border-gray-100">
                <div
                  v-for="(perms, group) in allPermissions"
                  :key="String(group)"
                  class="mb-2"
                >
                  <p class="text-xs font-semibold text-gray-400 uppercase mb-1">{{ String(group).replace(/_/g, ' ') }}</p>
                  <div class="flex flex-wrap gap-1">
                    <span
                      v-for="perm in perms"
                      :key="perm"
                      class="inline-flex px-1.5 py-0.5 rounded text-xs"
                      :class="role.permissions.includes(perm)
                        ? [systemRoleColors[role.name]?.bg ?? 'bg-gray-100', systemRoleColors[role.name]?.text ?? 'text-gray-600']
                        : 'bg-gray-50 text-gray-300'"
                    >
                      {{ formatPermName(perm) }}
                    </span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- ─── Custom Roles ─── -->
      <div>
        <div class="flex items-center gap-2 mb-4">
          <i class="pi pi-sliders-h text-gray-400" />
          <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wide">Custom Roles</h2>
          <span class="text-xs text-gray-400 bg-gray-100 px-2 py-0.5 rounded-full">{{ customRoles.length }} roles</span>
        </div>

        <div v-if="customRoles.length === 0" class="bg-white rounded-xl border border-dashed border-gray-200 p-10 text-center">
          <i class="pi pi-plus-circle text-4xl text-gray-300 mb-3" />
          <p class="text-sm text-gray-500">No custom roles yet</p>
          <p class="text-xs text-gray-400 mt-1 mb-4">Create roles tailored to your operation</p>
          <button v-if="can('settings.update')" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-blue-700" @click="openCreateDialog">
            Create Custom Role
          </button>
        </div>

        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          <div
            v-for="role in customRoles"
            :key="role.id"
            class="bg-white rounded-xl border border-gray-100 shadow-sm p-5"
          >
            <div class="flex items-start justify-between mb-3">
              <div>
                <h3 class="font-semibold text-gray-900 text-sm">{{ formatRoleName(role.name) }}</h3>
                <p v-if="role.description" class="text-xs text-gray-500 mt-0.5">{{ role.description }}</p>
              </div>
              <div class="flex items-center gap-2 shrink-0 ml-2">
                <button v-if="can('settings.update')" class="text-blue-600 hover:text-blue-700" @click="openEditDialog(role)">
                  <i class="pi pi-pencil text-sm" />
                </button>
                <button v-if="can('settings.update')" class="text-red-600 hover:text-red-700" @click="openDeleteDialog(role)">
                  <i class="pi pi-trash text-sm" />
                </button>
              </div>
            </div>

            <div class="flex items-center gap-4 text-xs text-gray-500 mb-3">
              <span><i class="pi pi-users mr-1" />{{ role.users_count }} users</span>
              <span><i class="pi pi-key mr-1" />{{ role.permissions.length }} permissions</span>
            </div>

            <div class="flex flex-wrap gap-1">
              <span
                v-for="perm in role.permissions.slice(0, 6)"
                :key="perm"
                class="inline-flex px-1.5 py-0.5 rounded text-xs bg-blue-50 text-blue-600"
              >
                {{ formatPermName(perm) }}
              </span>
              <span v-if="role.permissions.length > 6" class="inline-flex px-1.5 py-0.5 rounded text-xs bg-gray-100 text-gray-500">
                +{{ role.permissions.length - 6 }} more
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Create/Edit Dialog -->
    <div v-if="showDialog" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
      <div class="bg-white rounded-2xl w-full max-w-2xl mx-4 max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b border-gray-100 flex items-center justify-between sticky top-0 bg-white z-10">
          <h2 class="text-lg font-semibold">{{ isEditing ? `Edit — ${formatRoleName(formName)}` : 'Create Custom Role' }}</h2>
          <button class="text-gray-400 hover:text-gray-600" @click="showDialog = false">
            <i class="pi pi-times" />
          </button>
        </div>

        <div class="p-6 space-y-5">
          <!-- Building selector for building owners -->
          <BuildingSelector />

          <!-- Name -->
          <div v-if="!isEditing">
            <label class="block text-sm font-medium text-gray-700 mb-1">Role Name <span class="text-red-500">*</span></label>
            <input
              v-model="formName"
              type="text"
              placeholder="e.g., floor_manager"
              class="w-full border rounded-lg px-4 py-2 text-sm"
              :class="formErrors.name ? 'border-red-500' : 'border-gray-300'"
            />
            <p v-if="formErrors.name" class="mt-1 text-sm text-red-500">{{ formErrors.name }}</p>
            <p class="mt-1 text-xs text-gray-400">Lowercase letters and underscores only</p>
          </div>

          <!-- Description -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
            <input
              v-model="formDescription"
              type="text"
              placeholder="Brief description of this role"
              class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm"
            />
          </div>

          <!-- Permissions -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Permissions <span class="text-red-500">*</span>
              <span class="ml-2 text-xs text-gray-400 font-normal">({{ formPermissions.length }} selected)</span>
            </label>
            <p v-if="formErrors.permissions" class="mb-2 text-sm text-red-500">{{ formErrors.permissions }}</p>

            <div class="space-y-3">
              <div
                v-for="(perms, group) in allPermissions"
                :key="String(group)"
                class="border border-gray-200 rounded-lg p-4"
              >
                <label class="flex items-center gap-2 cursor-pointer mb-3">
                  <input
                    type="checkbox"
                    class="w-4 h-4 rounded border-gray-300"
                    :checked="isGroupSelected(String(group))"
                    :indeterminate="isGroupPartial(String(group))"
                    @change="toggleGroup(String(group))"
                  />
                  <span class="text-sm font-semibold text-gray-700 capitalize">{{ String(group).replace(/_/g, ' ') }}</span>
                  <span class="text-xs text-gray-400">({{ perms.length }})</span>
                </label>

                <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                  <label v-for="perm in perms" :key="perm" class="flex items-center gap-2 cursor-pointer">
                    <input
                      type="checkbox"
                      class="w-3.5 h-3.5 rounded border-gray-300"
                      :checked="formPermissions.includes(perm)"
                      @change="togglePermission(perm)"
                    />
                    <span class="text-xs text-gray-600">{{ formatPermName(perm) }}</span>
                  </label>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="p-6 border-t border-gray-100 flex justify-end gap-3 sticky bottom-0 bg-white">
          <button
            type="button"
            class="px-4 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50"
            @click="showDialog = false"
          >
            Cancel
          </button>
          <button
            type="button"
            :disabled="isSaving"
            class="px-4 py-2 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 flex items-center gap-2"
            @click="saveRole"
          >
            <i v-if="isSaving" class="pi pi-spinner pi-spin" />
            {{ isEditing ? 'Update Role' : 'Create Role' }}
          </button>
        </div>
      </div>
    </div>

    <AppConfirmDialog
      v-model="showDeleteDialog"
      title="Delete Role"
      :message="pendingDelete ? `Delete role &quot;${pendingDelete.name}&quot;? This cannot be undone.` : ''"
      confirm-label="Delete"
      :danger="true"
      @confirm="confirmDeleteRole"
    />
  </div>
</template>
