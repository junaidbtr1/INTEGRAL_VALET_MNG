<script setup lang="ts">
import { computed } from 'vue'
import { useAuthStore } from '@/stores/authStore'
import { useBuildingGuard } from '@/composables/useBuildingGuard'

interface Props {
  mode?: 'form' | 'filter'
}

const props = withDefaults(defineProps<Props>(), { mode: 'form' })

const authStore = useAuthStore()
const { buildingError } = useBuildingGuard()

const buildings = computed(() => authStore.ownedBuildings)

function onSelect(event: Event) {
  const id = Number((event.target as HTMLSelectElement).value)
  if (id) {
    authStore.switchBuilding(id)
  }
}
</script>

<template>
  <!-- ── Filter mode: compact banner for list pages ── -->
  <div
    v-if="props.mode === 'filter' && authStore.isBuildingOwner"
    class="flex items-center gap-3 bg-indigo-50 border border-indigo-100 rounded-xl px-4 py-2.5 mb-6"
  >
    <i class="pi pi-building text-indigo-400 text-sm shrink-0" />
    <span class="text-sm text-slate-500 font-medium shrink-0">Viewing:</span>
    <div class="relative flex-1 max-w-xs">
      <select
        :value="authStore.activeTenantId ?? ''"
        class="w-full h-8 border-0 bg-transparent text-sm font-semibold text-slate-800 outline-none appearance-none pr-6 cursor-pointer"
        @change="onSelect"
      >
        <option value="" disabled>— Select a building —</option>
        <option v-for="b in buildings" :key="b.id" :value="b.id">
          {{ b.name }}{{ b.plan ? ` · ${b.plan.name}` : '' }}
        </option>
      </select>
      <div class="absolute right-0 top-1/2 -translate-y-1/2 pointer-events-none">
        <i class="pi pi-chevron-down text-slate-400 text-xs" />
      </div>
    </div>
    <span class="text-xs text-indigo-500 bg-indigo-100 px-2 py-0.5 rounded-full shrink-0">
      {{ buildings.length }} building{{ buildings.length !== 1 ? 's' : '' }}
    </span>
  </div>

  <!-- ── Form mode: full field with validation ── -->
  <div v-else-if="props.mode === 'form' && authStore.isBuildingOwner" class="mb-4">
    <label class="block text-sm font-medium text-slate-700 mb-1.5">
      Building <span class="text-red-500">*</span>
    </label>
    <div class="relative">
      <div class="absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none">
        <i class="pi pi-building text-slate-400 text-sm" />
      </div>
      <select
        :value="authStore.activeTenantId ?? ''"
        class="w-full h-10 border rounded-lg pl-9 pr-9 text-sm outline-none appearance-none transition-all"
        :class="!buildingError
          ? 'border-indigo-400 bg-indigo-50/40 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/10 text-slate-800'
          : 'border-red-300 bg-red-50/30 focus:border-red-400 focus:ring-2 focus:ring-red-400/10 text-slate-500'"
        @change="onSelect"
      >
        <option value="" disabled>— Select a building —</option>
        <option
          v-for="b in buildings"
          :key="b.id"
          :value="b.id"
        >
          {{ b.name }}{{ b.plan ? ` · ${b.plan.name}` : '' }}
        </option>
      </select>
      <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none">
        <i class="pi pi-chevron-down text-slate-400 text-xs" />
      </div>
    </div>
    <p v-if="buildingError" class="mt-1.5 text-[12px] text-red-500 flex items-center gap-1">
      <i class="pi pi-exclamation-circle text-[11px]" />
      {{ buildingError }}
    </p>
    <p v-else class="mt-1.5 text-[12px] text-indigo-500 flex items-center gap-1">
      <i class="pi pi-check-circle text-[11px]" />
      This action will apply to <strong class="font-semibold">{{ authStore.activeTenant?.name }}</strong>
    </p>
  </div>
</template>
