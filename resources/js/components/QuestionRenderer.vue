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
        <!-- Answer Input -->
        <input
          type="text"
          disabled
          placeholder="Text answer"
          class="w-full rounded-md px-3 py-2 bg-gray-100"
        />
      </div>

    </div>
  </div>
</template>
  
<script lang="ts" setup>
import { defineProps, defineEmits } from 'vue';
import TextQuestion from './questions/TextQuestion.vue';
import SelectQuestion from './questions/SelectQuestion.vue';

const props = defineProps({
  field: Object,
  index: Number,
  total: Number
});

</script>
  