<!-- resources/js/components/public-form/FormFieldCard.vue -->
<script setup lang="ts">
import { computed } from 'vue';

interface Props {
  hasError?: boolean;
  errorMessage?: string;
}

const props = defineProps<Props>();

const cardClasses = computed(() => [
  'bg-white rounded-lg p-6 shadow-sm',
  props.hasError ? 'ring-2 ring-red-500' : ''
]);
</script>

<template>
  <div :class="cardClasses">
    <slot />
    
    <!-- Error Message -->
    <p v-if="hasError && errorMessage" class="text-sm text-red-600 mt-3">
      {{ errorMessage }}
    </p>
  </div>
</template>