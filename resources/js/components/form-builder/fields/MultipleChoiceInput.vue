<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import type { FieldComponentProps } from '@/types/forms';
import { Circle, Square, X } from 'lucide-vue-next';

const props = defineProps<FieldComponentProps>();

// Parse options safely
const editableOptions = ref<string[]>(
  Array.isArray(props.field.options) 
    ? [...props.field.options] 
    : []
);

// Watch for external changes to options
watch(() => props.field.options, (newOptions) => {
  if (Array.isArray(newOptions)) {
    editableOptions.value = [...newOptions];
  }
});

// Icon component based on field type
const IconComponent = computed(() => {
  return props.field.type === 'multiple-choice' ? Circle : Square;
});

function handleOptionEdit(index: number) {
  // Update the field's options
  props.field.options = [...editableOptions.value];
}

function addOption() {
  const newOption = `Option ${editableOptions.value.length + 1}`;
  editableOptions.value.push(newOption);
  props.field.options = [...editableOptions.value];
}

function removeOption(index: number) {
  if (editableOptions.value.length <= 1) {
    console.warn('Cannot remove the last option');
    return;
  }
  editableOptions.value.splice(index, 1);
  props.field.options = [...editableOptions.value];
}
</script>

<template>
  <div class="space-y-3">
    <!-- Edit Mode -->
    <div v-if="mode === 'edit'" class="space-y-2 pointer-events-none">
      <div 
        v-for="(option, index) in editableOptions" 
        :key="index" 
        class="flex items-center gap-3 pointer-events-auto"
      >
        <component 
          :is="IconComponent" 
          :size="20" 
          class="text-gray-400 flex-shrink-0" 
        />
        
        <input
          type="text"
          v-model="editableOptions[index]"
          @input="handleOptionEdit(index)"
          placeholder="Option text"
          class="flex-1 px-3 py-2 text-sm border-b-2 border-transparent focus:outline-none hover:border-gray-300"
          :style="{ '--tw-border-opacity': '1' }"
          @focus="(e) => (e.target as HTMLInputElement).style.borderColor = form_primary_color"
          @blur="(e) => (e.target as HTMLInputElement).style.borderColor = 'transparent'"
          @keydown.space.stop
          @keyup.space.stop
        />
        
        <button
          @click="removeOption(index)"
          class="p-1 rounded hover:bg-red-50 transition-colors flex-shrink-0"
          title="Remove option"
          :disabled="editableOptions.length <= 1"
        >
          <X :size="16" class="text-red-500" />
        </button>
      </div>

      <button
        @click="addOption"
        class="flex items-center gap-2 px-3 py-2 text-sm text-gray-600 hover:bg-gray-50 rounded transition-colors pointer-events-auto"
      >
        <component :is="IconComponent" :size="20" class="text-gray-400" />
        <span>Add option</span>
      </button>
    </div>

    <!-- Preview/Fill Mode -->
    <div v-else class="space-y-2">
      <label 
        v-for="(option, index) in editableOptions" 
        :key="index"
        class="flex items-center gap-3 px-3 py-2 rounded hover:bg-gray-50 cursor-pointer"
      >
        <input
          v-if="field.type === 'multiple-choice'"
          type="radio"
          :name="`field-${field.id}`"
          :value="option"
          class="w-4 h-4"
          :style="{ accentColor: form_primary_color }"
        />
        <input
          v-else
          type="checkbox"
          :value="option"
          class="w-4 h-4"
          :style="{ accentColor: form_primary_color }"
        />
        <span class="text-sm">{{ option }}</span>
      </label>
    </div>
  </div>
</template>