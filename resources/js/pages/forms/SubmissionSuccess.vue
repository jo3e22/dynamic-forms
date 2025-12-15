<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { CheckCircle2, ArrowLeft } from 'lucide-vue-next';
import type { FormDTO } from '@/types';

interface Props {
  form: FormDTO;
  formTitle: string;
}

const props = defineProps<Props>();

const form_primary_color = props.form.primary_color ?? '#3B82F6';
const form_secondary_color = props.form.secondary_color ?? '#EFF6FF';

function goBack() {
  router.visit('/forms');
}
</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header with branding -->
    <div 
      class="w-full py-4 px-6 shadow-sm"
      :style="{ backgroundColor: form_primary_color }"
    >
      <div class="max-w-4xl mx-auto flex items-center justify-between">
        <h1 class="text-2xl font-semibold text-white">
          {{ formTitle }}
        </h1>
        <Button
          variant="ghost"
          size="sm"
          @click="goBack"
          class="text-white hover:bg-white/10"
        >
          <ArrowLeft class="w-4 h-4 mr-2" />
          Back to Forms
        </Button>
      </div>
    </div>

    <!-- Success Message -->
    <div class="max-w-4xl mx-auto px-6 py-16">
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-12 text-center">
        <div 
          class="w-20 h-20 rounded-full mx-auto mb-6 flex items-center justify-center"
          :style="{ backgroundColor: form_secondary_color }"
        >
          <CheckCircle2 
            class="w-12 h-12"
            :style="{ color: form_primary_color }"
          />
        </div>
        
        <h2 class="text-3xl font-bold text-gray-900 mb-4">
          Submission Received
        </h2>
        
        <p class="text-lg text-gray-600 mb-8">
          Thank you for completing this form. Your response has been recorded successfully.
        </p>

        <div class="flex gap-4 justify-center">
          <Button
            @click="goBack"
            :style="{ backgroundColor: form_primary_color }"
            class="text-white hover:opacity-90"
          >
            <ArrowLeft class="w-4 h-4 mr-2" />
            Back to Forms
          </Button>
        </div>
      </div>
    </div>
  </div>
</template>