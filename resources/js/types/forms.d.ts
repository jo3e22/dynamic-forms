export type FieldType = 
  | 'short-answer'
  | 'email'
  | 'long-answer'
  | 'checkbox'
  | 'multiple-choice'
  | 'textarea';

export type FormStatus = 'draft' | 'pending' | 'open' | 'closed';

// Backend DTOs (Data Transfer Objects - what comes from Laravel)
export interface FormDTO {
  id: number;
  code: string;
  status: FormStatus;
  user_id: number;
  primary_color?: string;
  secondary_color?: string;
  created_at: string;
  updated_at: string;
  deleted_at?: string | null;
}

export interface FormSectionDTO {
  id: number;
  form_id: number;
  section_order: number;
  title: string | null;
  description: string | null;
  settings?: Record<string, any>;
  created_at: string;
  updated_at: string;
}

export interface FormFieldDTO {
  id: number;
  form_id: number;
  section: number; // section_id in database
  label: string;
  type: FieldType;
  options: string | null; // JSON string from backend
  required: boolean;
  field_order: number;
  created_at: string;
  updated_at: string;
}

// Frontend Models (what you work with in Vue components)
export interface FormField {
  id: number | null;
  label: string;
  type: FieldType;
  options: any; // Parsed JSON
  required: boolean;
  field_order: number;
}

export interface FormSection {
  id: number | null;
  section_order: number;
  title: string | null;
  description: string | null;
  settings?: Record<string, any>;
  fields: FormField[];
}

export interface Form {
  id: number;
  code: string;
  status: FormStatus;
  user_id: number;
  primary_color: string;
  secondary_color: string;
  sections: FormSection[];
}

// Builder-specific types (for FormBuilder component)
export interface FormBuilderField extends FormField {
  // Can add builder-specific properties here if needed
}

export interface FormBuilderSection extends FormSection {
  // Can add builder-specific properties here if needed
}

export type FormBuilderData = FormBuilderSection[];

// Props interfaces for components
export interface FormBuilderProps {
  form: FormDTO;
  data: FormBuilderData;
}

export interface FieldComponentProps {
  field: FormField;
  submissionField?: any;
  mode: 'edit' | 'preview' | 'fill';
  form_primary_color: string;
  form_secondary_color: string;
}