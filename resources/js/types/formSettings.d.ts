export type SharingType = 'authenticated_only' | 'guest_allowed' | 'guest_email_required';
export type ConfirmationEmailType = 'none' | 'confirmation_only' | 'linked_copy_of_responses' | 'detailed_copy_of_responses';

export interface FormSettingsDTO {
  id: number;
  form_id: number;
  sharing_type: SharingType;
  allow_duplicate_responses: boolean;
  confirmation_email: ConfirmationEmailType;
  open_at: string | null;
  close_at: string | null;
  max_submissions: number | null;
  allow_response_editing: boolean;
  confirmation_message: string | null;
  created_at: string;
  updated_at: string;
}