<!-- resources/js/pages/forms/FormViewer.vue -->
<script setup lang="ts">
import { ref, computed } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import type { FormDTO, FormBuilderData, SubmissionDTO } from '@/types';
import { getFieldComponent } from '@/components/form-builder/fields';
import PublicFormLayout from '@/layouts/PublicFormLayout.vue';
import PublicFormHeader from '@/components/public-form/PublicFormHeader.vue';
import PublicFormContainer from '@/components/public-form/PublicFormContainer.vue';
import FormSection from '@/components/public-form/FormSection.vue';
import FormFieldCard from '@/components/public-form/FormFieldCard.vue';

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

// Get form title
const formTitle = computed(() => props.data[0]?.title ?? 'Form');

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
    >
      <template #actions>
        <Button
          @click="submitForm"
          :style="{ backgroundColor: primaryColor }"
          class="hover:opacity-90 text-white"
        >
          Submit Form
        </Button>
      </template>
    </PublicFormHeader>

    <PublicFormContainer class="space-y-8">
      <!-- Form Sections -->
      <FormSection
        v-for="(section, sIdx) in data" 
        :key="section.id ?? sIdx"
        :title="section.title"
        :description="section.description"
        :primaryColor="primaryColor"
      >
        <!-- Fields -->
        <div class="space-y-6">
          <FormFieldCard
            v-for="(field, fIdx) in section.fields"
            :key="field.id ?? fIdx"
            :hasError="hasFieldError(field.id!)"
            :errorMessage="getFieldError(field.id!)"
          >
            <!-- Question Label -->
            <div class="mb-4">
              <label class="block text-lg font-medium text-gray-900">
                {{ field.label }}
                <span v-if="field.required" class="text-red-500 ml-1">*</span>
              </label>
            </div>

            <!-- Field Input Component -->
            <component
              :is="getFieldComponent(field.type)"
              :field="field"
              :submissionField="{ answer: answers[field.id!] }"
              mode="fill"
              :form_primary_color="primaryColor"
              :form_secondary_color="secondaryColor"
              v-model="answers[field.id!]"
            />
          </FormFieldCard>
        </div>
      </FormSection>

      <!-- Submit Button (bottom) -->
      <div class="flex justify-center pt-8">
        <Button
          @click="submitForm"
          size="lg"
          :style="{ backgroundColor: primaryColor }"
          class="hover:opacity-90 px-12 text-white"
        >
          Submit Form
        </Button>
      </div>
    </PublicFormContainer>
  </PublicFormLayout>
</template>