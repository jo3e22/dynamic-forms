<!-- resources/js/pages/forms/SubmissionSuccess.vue -->
<script setup lang="ts">
import { CheckCircle2, ArrowLeft } from 'lucide-vue-next';
import type { FormDTO } from '@/types';
import { Button } from '@/components/ui/button';
import PublicFormLayout from '@/layouts/PublicFormLayout.vue';
import PublicFormHeader from '@/components/public-form/PublicFormHeader.vue';
import PublicFormContainer from '@/components/public-form/PublicFormContainer.vue';

interface Props {
  form: FormDTO;
  formTitle: string;
}

defineProps<Props>();
</script>

<template>
  <PublicFormLayout 
    :form="form"
    :formTitle="formTitle"
    v-slot="{ primaryColor, secondaryColor }"
  >
    <PublicFormHeader 
      :formTitle="formTitle"
      :primaryColor="primaryColor"
    />

    <PublicFormContainer>
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-12 text-center">
        <div 
          class="w-20 h-20 rounded-full mx-auto mb-6 flex items-center justify-center"
          :style="{ backgroundColor: secondaryColor }"
        >
          <CheckCircle2 
            class="w-12 h-12"
            :style="{ color: primaryColor }"
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
            as-child
            :style="{ backgroundColor: primaryColor }"
            class="text-white hover:opacity-90"
          >
            <a href="/forms">
              <ArrowLeft class="w-4 h-4 mr-2" />
              Back to Forms
            </a>
          </Button>
        </div>
      </div>
    </PublicFormContainer>
  </PublicFormLayout>
</template>