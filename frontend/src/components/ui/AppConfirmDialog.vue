<script setup lang="ts">
import { ref, watch } from 'vue'

interface Props {
  modelValue: boolean
  title: string
  message: string
  confirmLabel?: string
  cancelLabel?: string
  danger?: boolean
  inputLabel?: string
  inputPlaceholder?: string
}

const props = withDefaults(defineProps<Props>(), {
  confirmLabel: 'Confirm',
  cancelLabel: 'Cancel',
  danger: false,
})

const emit = defineEmits<{
  'update:modelValue': [value: boolean]
  confirm: [value?: string]
  cancel: []
}>()

const inputValue = ref('')

watch(() => props.modelValue, (val) => {
  if (val) inputValue.value = ''
})

function onConfirm() {
  if (props.inputLabel && !inputValue.value.trim()) return
  emit('confirm', props.inputLabel ? inputValue.value.trim() : undefined)
  emit('update:modelValue', false)
}

function onCancel() {
  emit('cancel')
  emit('update:modelValue', false)
}
</script>

<template>
  <Teleport to="body">
    <Transition
      enter-active-class="transition-opacity duration-200"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition-opacity duration-150"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div
        v-if="modelValue"
        class="fixed inset-0 z-50 flex items-center justify-center p-4"
        @click.self="onCancel"
      >
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="onCancel" />

        <!-- Dialog -->
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md p-6">
          <!-- Icon -->
          <div
            class="w-12 h-12 rounded-full flex items-center justify-center mb-4"
            :class="danger ? 'bg-red-50' : 'bg-amber-50'"
          >
            <i
              class="text-xl"
              :class="danger ? 'pi pi-trash text-red-500' : 'pi pi-exclamation-triangle text-amber-500'"
            />
          </div>

          <!-- Title & Message -->
          <h3 class="text-base font-semibold text-slate-900 mb-1">{{ title }}</h3>
          <p class="text-sm text-slate-500 mb-5">{{ message }}</p>

          <!-- Optional input -->
          <div v-if="inputLabel" class="mb-5">
            <label class="block text-sm font-medium text-slate-700 mb-1.5">{{ inputLabel }}</label>
            <input
              v-model="inputValue"
              :placeholder="inputPlaceholder ?? ''"
              type="text"
              class="w-full h-10 border border-slate-300 rounded-lg px-4 text-sm outline-none hover:border-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/10 transition-all"
              @keyup.enter="onConfirm"
              @keyup.escape="onCancel"
            />
          </div>

          <!-- Actions -->
          <div class="flex gap-3 justify-end">
            <button
              class="px-4 py-2 text-sm font-medium text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-lg transition-all"
              @click="onCancel"
            >
              {{ cancelLabel }}
            </button>
            <button
              class="px-4 py-2 text-sm font-semibold rounded-lg transition-all"
              :class="danger
                ? 'bg-red-600 hover:bg-red-700 text-white'
                : 'bg-indigo-600 hover:bg-indigo-700 text-white'"
              :disabled="!!inputLabel && !inputValue.trim()"
              @click="onConfirm"
            >
              {{ confirmLabel }}
            </button>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>
