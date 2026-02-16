<!-- resources/js/layouts/PublicFormLayout.vue -->
<script setup lang="ts">
import { computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import type { FormDTO } from '@/types';

interface Props {
  form: FormDTO;
  formTitle: string;
}

const props = defineProps<Props>();

const form_primary_color = computed(() => props.form.primary_color ?? '#3B82F6');
const form_secondary_color = computed(() => props.form.secondary_color ?? '#EFF6FF');
</script>

<template>
  <Head :title="formTitle" />
  
  <div 
    :style="{ backgroundColor: form_secondary_color }" 
    class="min-h-screen"
  >
    <slot 
      :form="form"
      :formTitle="formTitle"
      :primaryColor="form_primary_color"
      :secondaryColor="form_secondary_color"
    />
  </div>
</template>