export type FieldType = 
  | 'short-answer'
  | 'email'
  | 'long-answer'
  | 'checkbox'
  | 'multiple-choice'
  | 'textarea';  // Remove title-primary and title

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
  section: number; // section_id
  label: string;
  type: FieldType;
  options: string | null; // JSON string
  required: boolean;
  field_order: number;
  created_at: string;
  updated_at: string;
}

// Frontend Models (what you work with in Vue)
export interface FormField {
  id: number | null;
  label: string;
  type: FieldType;
  options: any; // Parsed JSON (could be string[], object, etc.)
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

// Builder data structure (for editing - sections are selectable items too)
export interface FormBuilderField {
  id: number | null;
  label: string;
  type: FieldType;
  options: any;
  required: boolean;
  field_order: number;
}

export interface FormBuilderSection {
  id: number | null;
  section_order: number;
  title: string | null;
  description: string | null;
  fields: FormBuilderField[];
}

export type FormBuilderData = FormBuilderSection[];