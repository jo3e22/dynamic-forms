<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import { useFormBuilder } from '@/composables';
import type { FormBuilderProps, FieldType } from '@/types';
import { 
  FormBuilderHeader, 
  FormBuilderColorPicker, 
  SelectableContainer, 
  SectionHeader,
  FieldToolbar,
  FieldOptionsbar,
} from '@/components/form-builder';
import { FIELD_COMPONENTS, getFieldComponent } from '@/components/form-builder/fields';
import { Toast } from '@/components/ui/toast';
import FormSettingsPanel from '@/components/form-builder/FormSettingsPanel.vue';
import {
  Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription
} from '@/components/ui/dialog';
import { useFormStatus } from '@/composables/useFormStatus';
import { useDateTime } from '@/composables/useDateTime';
import type { FormSettingsDTO } from '@/types/formSettings';

const props = defineProps<FormBuilderProps>();

const showSettings = ref(false);
const { formatDate } = useDateTime();

const page = usePage();
const showColorPicker = ref(false);

const savedMessage = ref('');
const toastKey = ref(0);

// Initialize form builder composable
const {
  data,
  selectedId,
  sectionKey,
  fieldKey,
  select,
  clearSelection,
  isSelected,
  addSection,
  removeSection,
  duplicateSection,
  addField,
  removeField,
  moveField,
  duplicateField,
  changeFieldType,
  totalFields,
  totalSections,
} = useFormBuilder(props.data);

// Form colors
const form_primary_color = ref(props.form.primary_color ?? '#3B82F6');
const form_secondary_color = ref(props.form.secondary_color ?? '#EFF6FF');

// Errors
const errors = computed(() => (page.props.errors as any) ?? {});

function getSectionError(sIdx: number, field: 'title' | 'description') {
  return errors.value[`data.${sIdx}.${field}`];
}

function hasSectionError(sIdx: number, field: 'title' | 'description') {
  return !!getSectionError(sIdx, field);
}

function getFieldError(sIdx: number, fIdx: number, field: string = 'label') {
  return errors.value[`data.${sIdx}.fields.${fIdx}.${field}`];
}

function hasFieldError(sIdx: number, fIdx: number) {
  return !!getFieldError(sIdx, fIdx, 'label');
}

// Create fake submission field for preview
function getFakeSubmissionField(sIdx: number, fIdx: number) {
  return {
    id: null,
    submission_id: null,
    form_field_id: data.value[sIdx].fields[fIdx].id,
    answer: null,
  };
}

// Color picker
function openColorPicker() {
  showColorPicker.value = true;
}

function closeColorPicker() {
  showColorPicker.value = false;
}

function saveColors(colors: { primary: string; secondary: string }) {
  form_primary_color.value = colors.primary;
  form_secondary_color.value = colors.secondary;
  closeColorPicker();
}

// Save form
function saveForm() {
  router.put(`/forms/${props.form.code}/edit`, {
    data: data.value.map((section, sIdx) => ({
      id: section.id,
      title: section.title,
      description: section.description,
      section_order: sIdx,
      fields: section.fields.map((field, fIdx) => ({
        id: field.id,
        label: field.label,
        type: field.type,
        options: field.options,
        required: field.required,
        field_order: fIdx,
      })),
    })),
    colors: {
      primary: form_primary_color.value,
      secondary: form_secondary_color.value,
    },
  }, {
    preserveScroll: true,
    onSuccess: () => {
      toastKey.value++; // Force toast to remount
      savedMessage.value = 'Form saved successfully!';
      setTimeout(() => {
        savedMessage.value = '';
      }, 3100);
    },
    onError: (errors) => {
      console.error('Save errors:', errors);
    },
  });
}

// Click outside handler
function handleClickOutside(event: MouseEvent) {
  const target = event.target as HTMLElement;
  
  // Don't clear selection if clicking on input/textarea
  if (target.tagName === 'INPUT' || target.tagName === 'TEXTAREA') {
    return;
  }
  
  // Don't clear if clicking inside a selectable container
  if (target.closest('.selectable-container')) {
    return;
  }
  
  clearSelection();
}

// Debug helpers
function formjson() {
  console.log('Form data:', JSON.stringify(data.value, null, 2));
}

function tempPrint() {
  console.log('Current state:', {
    data: data.value,
    selectedId: selectedId.value,
    totalFields: totalFields.value,
    totalSections: totalSections.value,
  });
}

function openSettings() {
    showSettings.value = true;
}
function onSettingsSaved(updatedSettings: FormSettingsDTO) {
    // Handle any updates needed after settings are saved
    showSettings.value = false;
    alert('Settings saved successfully!');
}
</script>

<template>
  <Toast v-if="savedMessage" :key="toastKey" :message="savedMessage" type="success" />

  <div 
    :style="{ backgroundColor: form_secondary_color }" 
    class="min-h-screen pb-46"
    @click="handleClickOutside"
  >
    <FormBuilderHeader
      :form="props.form"
      @openColorPicker="openColorPicker"
      @saveForm="saveForm"
      @formjson="formjson"
      @tempPrint="tempPrint"
      @openSettings="openSettings"
    />

    <FormBuilderColorPicker
      v-if="showColorPicker"
      :form_primary_color="form_primary_color"
      :form_secondary_color="form_secondary_color"
      @close="closeColorPicker"
      @save="saveColors"
    />

    <div class="h-4"></div> <!-- Spacer for sticky header -->

    <main class="container-responsive mt-">
      <!-- Sections -->
      <div 
        v-for="(section, sIdx) in data" 
        :key="section.id ?? sIdx"
        class="mb-8"
      >
        <!-- Section Header (Title/Description) - Selectable -->
        <SelectableContainer
          :form_primary_color="form_primary_color"
          :form_secondary_color="form_secondary_color"
          :selected="isSelected(sectionKey(sIdx))"
          :select-key="sectionKey(sIdx)"
          :show-toolbar="true"
          @select="select"
          @unselect="clearSelection"
          class="mb-4"
        >
          <template #toolbar>
            <div class="flex gap-2">
              <button 
                @click="duplicateSection(sIdx)"
                class="px-3 py-1 text-sm hover:bg-gray-100 rounded"
                title="Duplicate section"
              >
                Duplicate
              </button>
              <button 
                v-if="totalSections > 1"
                @click="removeSection(sIdx)"
                class="px-3 py-1 text-sm text-red-600 hover:bg-red-50 rounded"
                title="Delete section"
              >
                Delete
              </button>
            </div>
          </template>

          <SectionHeader
            :title="section.title"
            :description="section.description"
            :is-selected="isSelected(sectionKey(sIdx))"
            :primary-color="form_primary_color"
            :has-error="hasSectionError(sIdx, 'title')"
            :error-message="getSectionError(sIdx, 'title')"
            @update:title="(v) => section.title = v"
            @update:description="(v) => section.description = v"
          />
        </SelectableContainer>

        <!-- Fields -->
        <div class="space-y-4">
          <SelectableContainer
            v-for="(field, fIdx) in section.fields"
            :key="field.id ?? `${sIdx}-${fIdx}`"
            :form_primary_color="form_primary_color"
            :form_secondary_color="form_secondary_color"
            :selected="isSelected(fieldKey(sIdx, fIdx))"
            :select-key="fieldKey(sIdx, fIdx)"
            :show-toolbar="true"
            @select="select"
            @unselect="clearSelection"
            :class="hasFieldError(sIdx, fIdx) ? 'ring-2 ring-red-500' : ''"
          >
            <template #toolbar>
              <FieldToolbar
                :field="field"
                :index="fIdx"
                :total="section.fields.length"
                @copy="duplicateField(sIdx, fIdx)"
                @delete="removeField(sIdx, fIdx)"
                @moveUp="moveField(sIdx, fIdx, 'up')"
                @moveDown="moveField(sIdx, fIdx, 'down')"
              />
            </template>

            <div class="flex-1 space-y-3">
              <!-- Field Label -->
              <input
                v-if="isSelected(fieldKey(sIdx, fIdx))"
                v-model="field.label"
                class="w-full p-2 text-lg bg-gray-100 border-b-2 border-gray-300 focus:outline-none hover:bg-gray-200"
                placeholder="Question"
                @focus="(e) => (e.target as HTMLInputElement).style.borderColor = form_primary_color"
                @blur="(e) => (e.target as HTMLInputElement).style.borderColor = 'gray'"
                @keydown.space.stop
                @keyup.space.stop
              />
              <div v-else class="flex gap-1 items-start">
                <div class="pt-2 text-lg">
                  {{ field.label || 'Question' }}
                </div>
                <div v-if="field.required" class="pt-2 text-lg text-red-500">*</div>
              </div>

              <!-- Field Error -->
              <p v-if="hasFieldError(sIdx, fIdx)" class="text-sm text-red-600">
                {{ getFieldError(sIdx, fIdx) }}
              </p>

              <!-- Field Component -->
              <component
                :is="getFieldComponent(field.type)"
                :field="field"
                :submissionField="getFakeSubmissionField(sIdx, fIdx)"
                :mode="'edit'"
                :form_primary_color="form_primary_color"
                :form_secondary_color="form_secondary_color"
              />
            </div>

            <template #optionbar>
              <FieldOptionsbar
                :field="field"
                :index="fIdx"
                :total="section.fields.length"
                :form_primary_color="form_primary_color"
                :form_secondary_color="form_secondary_color"
                @changeType="(type) => changeFieldType(sIdx, fIdx, type)"
              />
            </template>
          </SelectableContainer>

          <!-- Add Field Button -->
          <div class="flex gap-2 justify-center pt-2">
            <button
              @click="addField(sIdx)"
              class="px-4 py-2 text-sm bg-white border border-gray-300 rounded hover:bg-gray-50"
            >
              Add Question
            </button>
          </div>
        </div>

        <!-- Add Section Button -->
        <div class="flex justify-center mt-6">
          <button
            @click="addSection(sIdx)"
            class="px-4 py-2 text-sm bg-white border border-gray-300 rounded hover:bg-gray-50"
          >
            Add Section
          </button>
        </div>
      </div>
    </main>
  </div>

  <!-- Settings Dialog -->
  <Dialog v-model:open="showSettings">
      <DialogContent class="max-w-2xl max-h-[85vh] overflow-y-auto">
          <DialogHeader>
              <DialogTitle>Form Settings</DialogTitle>
              <DialogDescription>Configure publishing, sharing, and submission rules.</DialogDescription>
          </DialogHeader>
          <FormSettingsPanel
              v-if="showSettings"
              :formCode="form.code"
              @saved="onSettingsSaved"
          />
      </DialogContent>
  </Dialog>

</template>

<style scoped>
.container-responsive {
  max-width: 900px;
  margin: 0 auto;
  padding: 0 1rem;
}
</style>