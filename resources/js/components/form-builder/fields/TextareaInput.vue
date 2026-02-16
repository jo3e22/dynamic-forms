<script setup lang="ts">
import { ref, onMounted, nextTick } from 'vue';
import type { FieldComponentProps } from '@/types/forms';

const props = defineProps<FieldComponentProps>();

const emit = defineEmits<{
  'update:modelValue': [value: string];
}>();

const textareaRef = ref<HTMLTextAreaElement | null>(null);

function adjustHeight() {
  const el = textareaRef.value;
  if (!el) return;
  el.style.height = 'auto';
  el.style.height = `${el.scrollHeight}px`;
}

function handleInput(event: Event) {
  const target = event.target as HTMLTextAreaElement;
  emit('update:modelValue', target.value);
  adjustHeight();
}

onMounted(() => {
  nextTick(() => adjustHeight());
});
</script>

<template>
  <div>
    <textarea
      v-if="mode === 'edit' || mode === 'preview'"
      ref="textareaRef"
      disabled
      rows="3"
      placeholder="Long answer text"
      class="w-full py-2 text-sm border-b border-dotted border-gray-300 bg-transparent resize-none cursor-default pointer-events-none"
    />
    
    <textarea
      v-else
      ref="textareaRef"
      :value="submissionField?.answer"
      @input="handleInput"
      rows="4"
      placeholder="Your answer"
      :required="field.required"
      class="w-full py-2 px-3 text-base border-2 rounded-md focus:outline-none focus:ring-2 transition-colors resize-none"
      :style="{ 
        borderColor: form_primary_color,
        '--tw-ring-color': form_primary_color 
      }"
    />
  </div>
</template>