<script setup lang="ts">
import type { FormField } from '@/types/forms';
import { Copy, Trash2, MoveUp, MoveDown } from 'lucide-vue-next';

interface Props {
  field: FormField;
  index: number;
  total: number;
}

const props = defineProps<Props>();

const emit = defineEmits<{
  copy: [];
  delete: [];
  moveUp: [];
  moveDown: [];
}>();

const canMoveUp = props.index > 0;
const canMoveDown = props.index < props.total - 1;
</script>

<template>
  <div class="flex items-center gap-1">
    <button
      @click="emit('copy')"
      class="p-2 rounded hover:bg-gray-100 transition-colors"
      title="Duplicate question"
      aria-label="Duplicate question"
    >
      <Copy :size="16" class="text-gray-600" />
    </button>

    <button
      @click="emit('delete')"
      class="p-2 rounded hover:bg-red-50 transition-colors"
      title="Delete question"
      aria-label="Delete question"
    >
      <Trash2 :size="16" class="text-red-600" />
    </button>

    <div class="w-px h-6 bg-gray-300 mx-1" />

    <button
      v-if="canMoveUp"
      @click="emit('moveUp')"
      class="p-2 rounded hover:bg-gray-100 transition-colors"
      title="Move up"
      aria-label="Move question up"
    >
      <MoveUp :size="16" class="text-gray-600" />
    </button>
    <button
      v-else
      disabled
      class="p-2 rounded cursor-not-allowed opacity-50"
      title="Cannot move up"
      aria-label="Cannot move up"
    >
      <MoveUp :size="16" class="text-gray-400" />
    </button>

    <button
      v-if="canMoveDown"
      @click="emit('moveDown')"
      class="p-2 rounded hover:bg-gray-100 transition-colors"
      title="Move down"
      aria-label="Move question down"
    >
      <MoveDown :size="16" class="text-gray-600" />
    </button>
    <button
      v-else
      disabled
      class="p-2 rounded cursor-not-allowed opacity-50"
      title="Cannot move down"
      aria-label="Cannot move down"
    >
      <MoveDown :size="16" class="text-gray-400" />
    </button>
  </div>
</template>