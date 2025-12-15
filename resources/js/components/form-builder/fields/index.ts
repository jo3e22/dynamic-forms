import type { FieldType } from '@/types/forms';
import TextInput from './TextInput.vue';
import TextareaInput from './TextareaInput.vue';
import MultipleChoiceInput from './MultipleChoiceInput.vue';

// Export individual components
export { default as TextInput } from './TextInput.vue';
export { default as TextareaInput } from './TextareaInput.vue';
export { default as MultipleChoiceInput } from './MultipleChoiceInput.vue';

// Field component registry - maps field types to Vue components
export const FIELD_COMPONENTS: Record<FieldType, any> = {
  'short-answer': TextInput,
  'email': TextInput,
  'long-answer': TextareaInput,
  'textarea': TextareaInput,
  'checkbox': MultipleChoiceInput,
  'multiple-choice': MultipleChoiceInput,
};

// Helper function to get component by type
export function getFieldComponent(type: FieldType) {
  return FIELD_COMPONENTS[type] ?? TextInput;
}