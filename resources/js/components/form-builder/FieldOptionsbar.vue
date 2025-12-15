<script setup lang="ts">
import { computed } from 'vue';
import type { FormField, FieldType } from '@/types/forms';

interface Props {
  field: FormField;
  index: number;
  total: number;
  form_primary_color: string;
  form_secondary_color: string;
}

const props = defineProps<Props>();

const emit = defineEmits<{
  changeType: [type: FieldType];
}>();

const fieldTypes: { value: FieldType; label: string }[] = [
  { value: 'short-answer', label: 'Short Answer' },
  { value: 'long-answer', label: 'Long Answer' },
  { value: 'email', label: 'Email' },
  { value: 'multiple-choice', label: 'Multiple Choice' },
  { value: 'checkbox', label: 'Checkboxes' },
  { value: 'textarea', label: 'Paragraph' },
];

function handleTypeChange(event: Event) {
  const target = event.target as HTMLSelectElement;
  emit('changeType', target.value as FieldType);
}

function toggleRequired() {
  props.field.required = !props.field.required;
}
</script>

<template>
  <div class="flex items-center gap-4 px-4 py-2 border-t border-gray-200">
    <!-- Field Type Selector -->
    <div class="flex items-center gap-2">
      <label class="text-sm text-gray-600">Type:</label>
      <select
        :value="field.type"
        @change="handleTypeChange"
        class="px-3 py-1 text-sm border border-gray-300 rounded focus:outline-none focus:ring-2"
        :style="{ '--tw-ring-color': form_primary_color }"
      >
        <option 
          v-for="type in fieldTypes" 
          :key="type.value" 
          :value="type.value"
        >
          {{ type.label }}
        </option>
      </select>
    </div>

    <!-- Required Toggle -->
    <div class="flex items-center gap-2 ml-auto">
      <label class="text-sm text-gray-600">Required</label>
      <button
        @click="toggleRequired"
        class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2"
        :class="field.required ? 'bg-blue-600' : 'bg-gray-200'"
        :style="field.required ? { backgroundColor: form_primary_color } : {}"
        role="switch"
        :aria-checked="field.required"
      >
        <span
          class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform"
          :class="field.required ? 'translate-x-6' : 'translate-x-1'"
        />
      </button>
    </div>
  </div>
</template>