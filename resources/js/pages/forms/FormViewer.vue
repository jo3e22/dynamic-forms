<script setup lang="ts">
import { ref, computed } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { ArrowLeft } from 'lucide-vue-next';
import type { FormDTO, FormBuilderData, SubmissionDTO } from '@/types';
import { getFieldComponent } from '@/components/form-builder/fields';

interface Props {
  form: FormDTO;
  data: FormBuilderData;
  submission: SubmissionDTO;
  submissionFields: any[];
}

const props = defineProps<Props>();
const page = usePage();

// Form colors
const form_primary_color = props.form.primary_color ?? '#3B82F6';
const form_secondary_color = props.form.secondary_color ?? '#EFF6FF';

// Create a map of field answers
const answers = ref<Record<number, any>>({});

// Initialize answers from existing submission fields
props.submissionFields.forEach((sf) => {
  if (sf.form_field_id && sf.answer) {
    try {
      answers.value[sf.form_field_id] = JSON.parse(sf.answer);
    } catch {
      answers.value[sf.form_field_id] = sf.answer;
    }
  }
});

// Form validation errors
const errors = computed(() => (page.props.errors as any) ?? {});

function getFieldError(fieldId: number): string | undefined {
  return errors.value[`fields.${fieldId}`];
}

function hasFieldError(fieldId: number): boolean {
  return !!getFieldError(fieldId);
}

// Submit form
function submitForm() {
  // Convert answers to submission fields format
  const submissionFieldsData = Object.entries(answers.value).map(([fieldId, answer]) => {
    const existingField = props.submissionFields.find(sf => sf.form_field_id === Number(fieldId));
    
    return {
      id: existingField?.id ?? null,
      form_field_id: Number(fieldId),
      submission_id: props.submission.id,
      answer: typeof answer === 'string' ? answer : JSON.stringify(answer),
    };
  });

  router.put(`/forms/${props.form.code}/viewform/${props.submission.code}`, {
    submissionFields: submissionFieldsData,
  }, {
    preserveScroll: true,
    onSuccess: () => {
      console.log('Form submitted successfully');
    },
    onError: (errors) => {
      console.error('Submission errors:', errors);
    },
  });
}

function goBack() {
  router.visit('/forms');
}
</script>

<template>
  <div 
    :style="{ backgroundColor: form_secondary_color }" 
    class="min-h-screen"
  >
    <!-- Header -->
    <header class="bg-white shadow-sm px-6 py-4 sticky top-0 z-10">
      <div class="max-w-4xl mx-auto flex items-center justify-between">
        <button 
          @click="goBack"
          class="flex items-center gap-2 text-gray-600 hover:text-gray-800"
        >
          <ArrowLeft :size="20" />
          <span>Back</span>
        </button>
        
        <Button
          @click="submitForm"
          :style="{ backgroundColor: form_primary_color }"
          class="hover:opacity-90"
        >
          Submit Form
        </Button>
      </div>
    </header>

    <!-- Form Content -->
    <main class="max-w-4xl mx-auto px-6 py-8">
      <div 
        v-for="(section, sIdx) in data" 
        :key="section.id ?? sIdx"
        class="mb-12"
      >
        <!-- Section Header -->
        <div class="bg-white rounded-lg p-8 mb-6 shadow-sm">
          <h1 
            v-if="section.title"
            class="text-4xl font-bold mb-2"
            :style="{ color: form_primary_color }"
          >
            {{ section.title }}
          </h1>
          <p 
            v-if="section.description" 
            class="text-gray-600 text-lg"
          >
            {{ section.description }}
          </p>
        </div>

        <!-- Fields -->
        <div class="space-y-6">
          <div
            v-for="(field, fIdx) in section.fields"
            :key="field.id ?? fIdx"
            class="bg-white rounded-lg p-6 shadow-sm"
            :class="hasFieldError(field.id!) ? 'ring-2 ring-red-500' : ''"
          >
            <!-- Question Label -->
            <div class="mb-4">
              <label class="block text-lg font-medium text-gray-900">
                {{ field.label }}
                <span v-if="field.required" class="text-red-500 ml-1">*</span>
              </label>
            </div>

            <!-- Field Error -->
            <p v-if="hasFieldError(field.id!)" class="text-sm text-red-600 mb-3">
              {{ getFieldError(field.id!) }}
            </p>

            <!-- Field Input Component -->
            <component
              :is="getFieldComponent(field.type)"
              :field="field"
              :submissionField="{ answer: answers[field.id!] }"
              mode="fill"
              :form_primary_color="form_primary_color"
              :form_secondary_color="form_secondary_color"
              v-model="answers[field.id!]"
            />
          </div>
        </div>
      </div>

      <!-- Submit Button (bottom) -->
      <div class="flex justify-center pt-8">
        <Button
          @click="submitForm"
          size="lg"
          :style="{ backgroundColor: form_primary_color }"
          class="hover:opacity-90 px-12"
        >
          Submit Form
        </Button>
      </div>
    </main>
  </div>
</template>