<!-- resources/js/components/public-form/PublicFormContainer.vue -->
<script setup lang="ts">
interface Props {
  class?: string;
}

withDefaults(defineProps<Props>(), {
  class: '',
});
</script>

<template>
  <main class="max-w-4xl mx-auto px-6 py-8">
    <slot />
  </main>
</template>