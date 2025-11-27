<template>

    <!--:class="mode === 'preview' || mode === 'edit' ? 'border-b-1 border-dotted border-gray-300' : 'bg-white border border-gray-300'"-->

  <textarea
    ref="descRef"
    v-model="descriptionInput"
    rows="1"
    placeholder="Form Description"
    class="block w-full border-b-2 focus:outline-none resize-none overflow-hidden"
    :class="[
      'w-full border',
      descriptionError ? 'border-red-500 ring-1 ring-red-500' : 'border-gray-300'
    ]"
    :style="{
      '--tw-border-opacity': '1',
      borderTop: 'none',
      borderLeft: 'none',
      borderRight: 'none',
      borderColor: 'transparent',
      backgroundColor: 'transparent',
    }"
    @focus="(e) => (e.target as HTMLTextAreaElement).style.borderColor = form_primary_color"
    @blur="(e) => (e.target as HTMLTextAreaElement).style.borderColor = 'transparent'"
    @input="adjustTextareaHeight"
    @keydown.space="(e) => e.stopImmediatePropagation()"
    @keyup.space="(e) => e.stopImmediatePropagation()"
  />
</template>

<script setup lang="ts">
import { computed, ref, watch, onMounted, nextTick } from 'vue';

const props = defineProps({
  field: Object,
  submissionField: Object,
  mode: String,
  form_primary_color: String,
  form_secondary_color: String,
  descriptionError: Boolean,
});

// Local state for the textarea
const descriptionInput = ref('');

// Initialize from field.options
onMounted(() => {
  try {
    descriptionInput.value = JSON.parse(props.field?.options || '""');
  } catch (e) {
    console.error('Invalid JSON in props.field.options:', props.field?.options);
    descriptionInput.value = '';
  }
  nextTick(() => adjustTextareaHeight());
});

// Watch for changes and update field.options
watch(descriptionInput, (newValue) => {
  try {
    if (props.field) {
      props.field.options = JSON.stringify(newValue);
    }
    nextTick(() => adjustTextareaHeight());
  } catch (e) {
    console.error('Failed to stringify value:', newValue);
  }
});

const descRef = ref<HTMLTextAreaElement | null>(null);

function adjustTextareaHeight(e?: Event) {
  const el = (e?.target as HTMLTextAreaElement) ?? descRef.value;
  if (!el) return;
  el.style.height = 'auto';
  el.style.height = `${el.scrollHeight}px`;
}
</script>