export type SubmissionStatus = 'draft' | 'pending' | 'open' | 'closed';

export interface SubmissionDTO {
  id: number;
  code: string;
  form_id: number;
  user_id: number | null;
  status: SubmissionStatus;
  email: string | null;
  created_at: string;
  updated_at: string;
}

export interface SubmissionFieldDTO {
  id: number;
  submission_id: number;
  field_id: number;
  answer: string | null; // JSON string
  created_at: string;
  updated_at: string;
}

export interface SubmissionField {
  id: number | null;
  submission_id: number | null;
  field_id: number;
  answer: any; // Parsed JSON
}

export interface Submission {
  id: number;
  code: string;
  form_id: number;
  user_id: number | null;
  status: SubmissionStatus;
  email: string | null;
  fields: SubmissionField[];
}