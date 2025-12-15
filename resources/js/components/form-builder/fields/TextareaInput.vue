<script setup lang="ts">
import { ref, onMounted, nextTick } from 'vue';
import type { FieldComponentProps } from '@/types/forms';

const props = defineProps<FieldComponentProps>();

const textareaRef = ref<HTMLTextAreaElement | null>(null);

function adjustHeight() {
  const el = textareaRef.value;
  if (!el) return;
  el.style.height = 'auto';
  el.style.height = `${el.scrollHeight}px`;
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
      rows="3"
      placeholder="Your answer"
      class="w-full py-2 text-sm border-b-2 border-gray-300 focus:outline-none focus:border-current transition-colors resize-none"
      :style="{ borderColor: form_primary_color }"
      @input="adjustHeight"
    />
  </div>
</template>