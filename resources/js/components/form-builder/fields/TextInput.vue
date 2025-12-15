<script setup lang="ts">
import type { FieldComponentProps } from '@/types/forms';

const props = defineProps<FieldComponentProps>();

const placeholderText = props.field.type === 'email' 
  ? 'Email address' 
  : 'Short answer text';
</script>

<template>
  <div>
    <input
      v-if="mode === 'edit' || mode === 'preview'"
      type="text"
      disabled
      :placeholder="placeholderText"
      class="w-1/2 py-2 text-sm border-b border-dotted border-gray-300 bg-transparent cursor-default pointer-events-none"
    />
    
    <input
      v-else
      type="text"
      :placeholder="placeholderText"
      class="w-full py-2 text-sm border-b-2 border-gray-300 focus:outline-none focus:border-current transition-colors"
      :style="{ borderColor: form_primary_color }"
    />
  </div>
</template>