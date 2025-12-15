<script setup lang="ts">
import type { FieldComponentProps } from '@/types/forms';

const props = defineProps<FieldComponentProps>();

const emit = defineEmits<{
  'update:modelValue': [value: string];
}>();

const placeholderText = props.field.type === 'email' 
  ? 'Email address' 
  : 'Short answer text';

function handleInput(event: Event) {
  const target = event.target as HTMLInputElement;
  emit('update:modelValue', target.value);
}
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
      :type="field.type === 'email' ? 'email' : 'text'"
      :value="submissionField?.answer"
      @input="handleInput"
      :placeholder="placeholderText"
      :required="field.required"
      class="w-full py-2 px-3 text-base border-2 rounded-md focus:outline-none focus:ring-2 transition-colors"
      :style="{ 
        borderColor: form_primary_color,
        '--tw-ring-color': form_primary_color 
      }"
    />
  </div>
</template>