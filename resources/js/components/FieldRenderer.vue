<!-- components/FieldEditor.vue -->
<template>
  <div class="border rounded bg-white shadow-sm p-4 space-y-4" :data-field-id="field.uuid">
    <!-- Toolbar -->
    <div class="flex justify-end items-center">
      <div class="flex gap-2">
        <button @click="copyField" aria-label="Copy question" class="text-gray-600 hover:text-blue-600">
          <CopyIcon />
        </button>
        <button @click="deleteField" aria-label="Delete question" class="text-gray-600 hover:text-red-600">
          <DeleteIcon />
        </button>

        <button
          @click="moveDown"
          :disabled="index === total - 1"
          :class="[
            'transition',
            props.index !== total-1 ? 'cursor-pointer' :
            index === total - 1
              ? 'text-gray-400 cursor-not-allowed'
              : 'text-gray-600 hover:text-gray-800'
          ]"
          aria-label="Move question down"
        >
          <ArrowDownIcon />
        </button>

        <button
          @click="moveUp"
          :disabled="props.index === 0"
          :class="[
            'hover:text-gray-800',
            props.index !== 0 ? 'cursor-pointer' :
            props.index === 0 ? 'text-gray-400 cursor-not-allowed' : 'text-gray-600'
          ]"
          aria-label="Move question up"
        >
          <ArrowUpIcon />
        </button>
      </div>
      
      <div class="flex gap-2">
        <button class="text-gray-500 hover:text-gray-700"><i class="fa fa-arrows-alt"></i></button>
        <button class="text-gray-500 hover:text-gray-700"><i class="fa fa-copy"></i></button>
        <button class="text-gray-500 hover:text-gray-700"><i class="fa fa-cog"></i></button>
      </div>
    </div>

    <div class="flex gap-4 items-start">
      <!-- Order Number -->
      <div class="pt-2 text-lg font-semibold text-gray-700 w-6 text-right">
        {{ field.field_order }}.
      </div>

      <!-- Question + Answer -->
      <div class="flex-1 space-y-3">
        <!-- Question Label -->
        <input
          v-model="field.label"
          class="w-full border border-gray-300 rounded-md px-3 py-2"
          :placeholder="field.label || 'Email'"
        />

        <!-- Answer Input -->
        <input
          type="email"
          disabled
          placeholder="Email answer"
          class="w-full border border-gray-300 rounded-md px-3 py-2 bg-gray-100"
        />
      </div>
    </div>

    <!-- Options Section -->
    <div class="flex items-center gap-2">
      <label class="text-sm text-gray-600">Required</label>
      <input type="checkbox" v-model="field.required" />
    </div>
  </div>
</template>

<script setup>
import { defineProps, defineEmits } from 'vue';
import CopyIcon from './icons/CopyIcon.vue';
import DeleteIcon from './icons/DeleteIcon.vue';
import ArrowDownIcon from './icons/ArrowDownIcon.vue';
import ArrowUpIcon from './icons/ArrowUpIcon.vue';

const props = defineProps({
  field: Object,
  index: Number,
  total: Number
});

const emit = defineEmits(['copy', 'delete', 'moveUp', 'moveDown']);

function copyField() {
  emit('copy', props.field, props.index);
}

function deleteField() {
  emit('delete', props.field, props.index);
}

function moveUp() {
  emit('moveUp', props.field, props.index);
}

function moveDown() {
  emit('moveDown', props.field, props.index);
}
</script>
  