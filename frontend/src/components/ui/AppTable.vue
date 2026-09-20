<script setup lang="ts">
withDefaults(defineProps<{ minWidth?: string }>(), { minWidth: 'min-w-[600px]' })
</script>

<template>
  <div class="overflow-x-auto">
    <table class="w-full" :class="minWidth">
      <slot />
    </table>
  </div>
</template>
