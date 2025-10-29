<template>
  <div class="selectable p-4 space-y-4" :data-field-id="field.uuid">

    <div class="flex-col gap-1 space-y-3">
      <div class="flex gap-1 items-start">
        <!-- Order Number -->
        <div class="pt-2 text-lg text-gray-700 w-6 pr-6px text-right">
          {{ index +1 }}.
        </div>

        <!-- Question Label -->
        <div class="pt-2 text-lg text-gray-700 w-auto text-left">
          {{ field.label }}
        </div>

        <div v-if="field.required" class="pt-2 text-lg text-red-500">
          *
        </div>
      </div>

      <div class="flex gap-1 items-start">
        <div class="w-6"></div>
        <!-- submissionField Input -->
        <input
          v-if="mode === 'preview'"
          type="text"
          disabled
          placeholder="Text submissionField"
          class="w-full rounded-md px-3 py-2 bg-gray-100"
        />
        <input
          v-if="mode === 'view'"
          v-model="textInput"
          type="text"
          placeholder="Text answer"
          class="w-full rounded-md px-3 py-2 bg-white border border-gray-300"
        />
      </div>

    </div>
  </div>
</template>
  
<script lang="ts" setup>
import { defineProps, defineEmits, computed } from 'vue';
import TextQuestion from './questions/TextQuestion.vue';
import SelectQuestion from './questions/SelectQuestion.vue';

const props = defineProps({
  field: Object,
  index: Number,
  submissionField: Object,
  mode: {
    type: String,
    default: 'edit',
  },
});

console.log('submissionField:', props.submissionField);

// Computed property to handle JSON conversion
const textInput = computed({
  // Getter: Parse the JSON value into plain text for the input
  get() {
    try {
      return JSON.parse(props.submissionField.answer || '""'); // Parse JSON, default to an empty string if null/undefined
    } catch (e) {
      console.error('Invalid JSON in props.submissionField.answer:', props.submissionField.answer);
      return ''; // Fallback to an empty string if parsing fails
    }
  },
  // Setter: Convert the plain text input into JSON and update props.submissionField.answer
  set(value) {
    try {
      props.submissionField.answer = JSON.stringify(value); // Convert plain text to JSON
    } catch (e) {
      console.error('Failed to stringify value:', value);
    }
  },
});

</script>
  