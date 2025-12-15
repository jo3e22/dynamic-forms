import { ref, Ref, computed } from 'vue';
import type { FormBuilderData, FormSection, FormField, FieldType } from '@/types/forms';
import { FormFieldModel, FormSectionModel } from '@/models/FormModel';

export function useFormBuilder(initialData: FormBuilderData) {
  const data = ref<FormBuilderData>(initialData);
  const selectedId = ref<string | null>(null);

  // Selection helpers - use indices for stable keys
  function sectionKey(sectionIndex: number): string {
    const section = data.value[sectionIndex];
    return `section-${section.id ?? sectionIndex}`;
  }

  function fieldKey(sectionIndex: number, fieldIndex: number): string {
    const section = data.value[sectionIndex];
    const field = section.fields[fieldIndex];
    // Use both section and field IDs if available, otherwise use indices
    const sectionPart = section.id ?? `s${sectionIndex}`;
    const fieldPart = field.id ?? `f${fieldIndex}`;
    return `field-${sectionPart}-${fieldPart}`;
  }

  function select(key: string): void {
    selectedId.value = key;
  }

  function clearSelection(): void {
    selectedId.value = null;
  }

  function isSelected(key: string): boolean {
    return selectedId.value === key;
  }

  // Section operations
  function addSection(afterIndex: number): FormSection {
    const newSection = FormSectionModel.create(data.value.length);
    data.value.splice(afterIndex + 1, 0, newSection);
    reorderSections();
    select(sectionKey(afterIndex + 1));
    return newSection;
  }

  function removeSection(sectionIndex: number): void {
    if (data.value.length <= 1) {
      console.warn('Cannot remove the last section');
      return;
    }
    
    data.value.splice(sectionIndex, 1);
    reorderSections();
    clearSelection();
  }

  function moveSection(fromIndex: number, toIndex: number): void {
    if (toIndex < 0 || toIndex >= data.value.length) return;
    
    const [movedSection] = data.value.splice(fromIndex, 1);
    data.value.splice(toIndex, 0, movedSection);
    reorderSections();
  }

  function duplicateSection(sectionIndex: number): void {
    const section = data.value[sectionIndex];
    const newSection: FormSection = {
      id: null,
      section_order: data.value.length,
      title: section.title ? `${section.title} (Copy)` : null,
      description: section.description,
      settings: section.settings ? { ...section.settings } : undefined,
      fields: section.fields.map(field => FormFieldModel.clone(field)),
    };
    
    data.value.splice(sectionIndex + 1, 0, newSection);
    reorderSections();
    select(sectionKey(sectionIndex + 1));
  }

  function reorderSections(): void {
    data.value.forEach((section, index) => {
      section.section_order = index;
    });
  }

  // Field operations
  function addField(
    sectionIndex: number, 
    type: FieldType = 'short-answer',
    afterFieldIndex?: number
  ): FormField {
    const section = data.value[sectionIndex];
    const newField = FormFieldModel.create(type, section.fields.length);
    
    if (afterFieldIndex !== undefined) {
      section.fields.splice(afterFieldIndex + 1, 0, newField);
    } else {
      section.fields.push(newField);
    }
    
    reorderFields(sectionIndex);
    const newFieldIndex = afterFieldIndex !== undefined ? afterFieldIndex + 1 : section.fields.length - 1;
    select(fieldKey(sectionIndex, newFieldIndex));
    return newField;
  }

  function removeField(sectionIndex: number, fieldIndex: number): void {
    const section = data.value[sectionIndex];
    section.fields.splice(fieldIndex, 1);
    reorderFields(sectionIndex);
    clearSelection();
  }

  function moveField(sectionIndex: number, fieldIndex: number, direction: 'up' | 'down'): void {
    const section = data.value[sectionIndex];
    const targetIndex = direction === 'up' ? fieldIndex - 1 : fieldIndex + 1;
    
    if (targetIndex < 0 || targetIndex >= section.fields.length) return;
    
    FormSectionModel.moveField(section, fieldIndex, targetIndex);
    
    // Update selection to follow the moved field
    select(fieldKey(sectionIndex, targetIndex));
  }

  function duplicateField(sectionIndex: number, fieldIndex: number): void {
    const section = data.value[sectionIndex];
    const field = section.fields[fieldIndex];
    const newField = FormFieldModel.clone(field);
    
    section.fields.splice(fieldIndex + 1, 0, newField);
    reorderFields(sectionIndex);
    select(fieldKey(sectionIndex, fieldIndex + 1));
  }

  function changeFieldType(sectionIndex: number, fieldIndex: number, newType: FieldType): void {
    const section = data.value[sectionIndex];
    const field = section.fields[fieldIndex];
    
    // Reset options when changing type
    field.type = newType;
    field.options = null;
    
    // Set default options for certain types
    if (newType === 'multiple-choice' || newType === 'checkbox') {
      field.options = ['Option 1', 'Option 2', 'Option 3'];
    }
  }

  function reorderFields(sectionIndex: number): void {
    const section = data.value[sectionIndex];
    section.fields.forEach((field, index) => {
      field.field_order = index;
    });
  }

  // Computed properties
  const totalFields = computed(() => {
    return data.value.reduce((total, section) => total + section.fields.length, 0);
  });

  const totalSections = computed(() => data.value.length);

  const isEmpty = computed(() => {
    return data.value.length === 0 || 
           (data.value.length === 1 && data.value[0].fields.length === 0);
  });

  return {
    // State
    data,
    selectedId,
    
    // Selection
    sectionKey,
    fieldKey,
    select,
    clearSelection,
    isSelected,
    
    // Section operations
    addSection,
    removeSection,
    moveSection,
    duplicateSection,
    
    // Field operations
    addField,
    removeField,
    moveField,
    duplicateField,
    changeFieldType,
    
    // Computed
    totalFields,
    totalSections,
    isEmpty,
  };
}