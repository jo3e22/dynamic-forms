<!-- filepath: resources/js/pages/forms/FormClosed.vue -->
<script setup lang="ts">
import PublicFormLayout from '@/layouts/PublicFormLayout.vue';
import { Badge } from '@/components/ui/badge';
import { computed } from 'vue';

const props = defineProps<{
  form: { id: number; code: string; status: string; title: string };
  message: string;
}>();

const statusLabel = computed(() => {
  const map: Record<string, string> = {
    draft: 'Not Published',
    scheduled: 'Scheduled',
    closed: 'Closed',
  };
  return map[props.form.status] ?? 'Unavailable';
});
</script>

<template>
  <PublicFormLayout :form="form" :formTitle="form.title">
    <div class="flex flex-col items-center justify-center py-20 text-center">
      <Badge class="mb-4 text-sm" variant="secondary">{{ statusLabel }}</Badge>
      <h1 class="text-2xl font-bold text-gray-800 mb-4">{{ form.title }}</h1>
      <p class="text-gray-600 text-lg max-w-md">{{ message }}</p>
    </div>
  </PublicFormLayout>
</template>