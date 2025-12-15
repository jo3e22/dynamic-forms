// Re-export all types from individual type files
export * from './forms';
export * from './submissions';

// Also export commonly used types directly
export type {
  // Form types
  Form,
  FormSection,
  FormField,
  FormBuilderData,
  FormBuilderSection,
  FieldType,
  FormStatus,
  
  // DTO types
  FormDTO,
  FormSectionDTO,
  FormFieldDTO,
  
  // Component prop types
  FormBuilderProps,
  FieldComponentProps,
} from './forms';

export type {
  // Submission types
  Submission,
  SubmissionField,
  SubmissionStatus,
  
  // DTO types
  SubmissionDTO,
  SubmissionFieldDTO,
} from './submissions';

// Global types
export type {
  User,
  Auth,
  AppPageProps,
  BreadcrumbItem,
  NavItem,
} from './index.d';