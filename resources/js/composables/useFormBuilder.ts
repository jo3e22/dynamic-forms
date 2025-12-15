import { ref, Ref } from 'vue';
import type { FormBuilderData, FormField } from '@/types/forms';
import { FormFieldModel } from '@/models/FormModel';

export function useFormBuilder(initialData: FormBuilderData) {
  const data = ref<FormBuilderData>(initialData);
  const selectedId = ref<string | null>(null);

  function addSection(afterIndex: number) {
    const newSection = {
      id: null,
      section_order: data.value.length,
      titlesec: { title: null, description: null },
      fields: [],
    };
    data.value.splice(afterIndex + 1, 0, newSection);
    return newSection;
  }

  function removeSection(index: number) {
    data.value.splice(index, 1);
    // Reorder remaining sections
    data.value.forEach((section, idx) => {
      section.section_order = idx;
    });
  }

  function addField(sectionIndex: number, type: FormField['type'] = 'short-answer') {
    const section = data.value[sectionIndex];
    const newField = FormFieldModel.create(
      section.id ?? 0,
      section.fields.length,
      type
    );
    section.fields.push(newField);
    return newField;
  }

  function removeField(sectionIndex: number, fieldIndex: number) {
    const section = data.value[sectionIndex];
    section.fields.splice(fieldIndex, 1);
    // Reorder remaining fields
    section.fields.forEach((field, idx) => {
      field.field_order = idx;
    });
  }

  function moveField(sectionIndex: number, fieldIndex: number, direction: 'up' | 'down') {
    const section = data.value[sectionIndex];
    const targetIndex = direction === 'up' ? fieldIndex - 1 : fieldIndex + 1;
    
    if (targetIndex < 0 || targetIndex >= section.fields.length) return;
    
    const temp = section.fields[fieldIndex];
    section.fields[fieldIndex] = section.fields[targetIndex];
    section.fields[targetIndex] = temp;
    
    // Update field_order
    section.fields.forEach((field, idx) => {
      field.field_order = idx;
    });
  }

  return {
    data,
    selectedId,
    addSection,
    removeSection,
    addField,
    removeField,
    moveField,
  };
}