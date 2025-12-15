<script setup lang="ts">
import { computed } from 'vue';

interface Props {
  selected: boolean;
  selectKey: string;
  formPrimaryColor: string;
  formSecondaryColor: string;
  showToolbar?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
  showToolbar: true,
});

const emit = defineEmits<{
  select: [key: string];
  unselect: [];
}>();

function handleClick(event: MouseEvent) {
  const target = event.target as HTMLElement;
  
  // Don't interfere with input/textarea/button clicks
  if (
    target.tagName === 'INPUT' || 
    target.tagName === 'TEXTAREA' || 
    target.tagName === 'BUTTON' ||
    target.closest('button')
  ) {
    return;
  }
  
  // Always emit select when clicking the container
  emit('select', props.selectKey);
  event.stopPropagation();
}

const containerClasses = computed(() => [
  'selectable-container',
  'relative rounded-lg transition-all duration-150 cursor-pointer',
  props.selected 
    ? 'ring-2 ring-offset-2' 
    : 'hover:shadow-sm',
]);

const containerStyle = computed(() => ({
  backgroundColor: 'white',
  ...(props.selected && {
    '--tw-ring-color': props.formPrimaryColor,
  }),
}));
</script>

<template>
  <div
    :class="containerClasses"
    :style="containerStyle"
    @click="handleClick"
  >
    <!-- Toolbar (shown when selected) -->
    <div 
      v-if="showToolbar && selected" 
      class="absolute -top-3 right-4 z-10 flex items-center gap-1 px-2 py-1 bg-white border border-gray-200 rounded-md shadow-sm"
      @click.stop
    >
      <slot name="toolbar" />
    </div>

    <!-- Main Content -->
    <div class="p-6">
      <slot />
    </div>

    <!-- Options Bar (shown when selected) -->
    <div v-if="selected && $slots.optionbar" @click.stop>
      <slot name="optionbar" />
    </div>
  </div>
</template>