<script setup lang="ts">
import { ref, nextTick } from 'vue';

interface Props {
  title: string | null;
  description: string | null;
  isSelected: boolean;
  primaryColor: string;
  hasError?: boolean;
  errorMessage?: string;
}

const props = withDefaults(defineProps<Props>(), {
  hasError: false,
  errorMessage: '',
});

const emit = defineEmits<{
  'update:title': [value: string | null];
  'update:description': [value: string | null];
}>();

const titleRef = ref<HTMLTextAreaElement | null>(null);
const descRef = ref<HTMLTextAreaElement | null>(null);

const titleValue = ref(props.title ?? '');
const descriptionValue = ref(props.description ?? '');

// Auto-resize textareas
function adjustHeight(el: HTMLTextAreaElement | null) {
  if (!el) return;
  el.style.height = 'auto';
  el.style.height = `${el.scrollHeight}px`;
}

function onTitleInput(e: Event) {
  const target = e.target as HTMLTextAreaElement;
  titleValue.value = target.value;
  emit('update:title', target.value || null);
  adjustHeight(target);
}

function onDescriptionInput(e: Event) {
  const target = e.target as HTMLTextAreaElement;
  descriptionValue.value = target.value;
  emit('update:description', target.value || null);
  adjustHeight(target);
}

// Initialize heights
nextTick(() => {
  adjustHeight(titleRef.value);
  adjustHeight(descRef.value);
});
</script>

<template>
  <div class="space-y-2">
    <!-- Title -->
    <div>
      <textarea
        v-if="isSelected"
        ref="titleRef"
        :value="titleValue"
        @input="onTitleInput"
        rows="1"
        placeholder="Section Title"
        class="w-full text-4xl font-semibold border-b-2 border-transparent focus:outline-none hover:bg-gray-100 px-2 py-1 resize-none overflow-hidden"
        :class="hasError ? 'border-red-500' : ''"
        :style="{ 
          borderBottomColor: isSelected ? primaryColor : 'transparent',
        }"
        @focus="(e) => (e.target as HTMLTextAreaElement).style.borderColor = primaryColor"
        @blur="(e) => (e.target as HTMLTextAreaElement).style.borderColor = 'transparent'"
        @keydown.space.stop
        @keyup.space.stop
      />
      <div v-else class="text-4xl font-semibold px-2 py-1">
        {{ titleValue || 'Untitled Section' }}
      </div>
      
      <p v-if="hasError && errorMessage" class="text-sm text-red-600 mt-1 px-2">
        {{ errorMessage }}
      </p>
    </div>

    <!-- Description -->
    <div>
      <textarea
        v-if="isSelected"
        ref="descRef"
        :value="descriptionValue"
        @input="onDescriptionInput"
        rows="1"
        placeholder="Section description (optional)"
        class="w-full text-base text-gray-600 border-b border-transparent focus:outline-none hover:bg-gray-50 px-2 py-1 resize-none overflow-hidden"
        @focus="(e) => (e.target as HTMLTextAreaElement).style.borderColor = primaryColor"
        @blur="(e) => (e.target as HTMLTextAreaElement).style.borderColor = 'transparent'"
        @keydown.space.stop
        @keyup.space.stop
      />
      <div v-else-if="descriptionValue" class="text-base text-gray-600 px-2 py-1">
        {{ descriptionValue }}
      </div>
    </div>
  </div>
</template>