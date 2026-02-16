<!-- resources/js/components/public-form/FormSection.vue -->
<script setup lang="ts">
interface Props {
  title?: string;
  description?: string;
  primaryColor?: string;
  class?: string;
}

const props = defineProps<Props>();
</script>

<template>
  <div :class="['mb-12', props.class]">
    <!-- Section Header -->
    <div v-if="title || description" class="bg-white rounded-lg p-8 mb-6 shadow-sm">
      <h1 
        v-if="title"
        class="text-4xl font-bold mb-2"
        :style="primaryColor ? { color: primaryColor } : undefined"
      >
        {{ title }}
      </h1>
      <p 
        v-if="description" 
        class="text-gray-600 text-lg"
      >
        {{ description }}
      </p>
    </div>

    <!-- Section Content -->
    <slot />
  </div>
</template>