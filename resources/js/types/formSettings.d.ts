export type SharingType = 'authenticated_only' | 'guest_allowed' | 'guest_email_required';
export type ConfirmationEmailType = 'none' | 'confirmation_only' | 'linked_copy_of_responses' | 'detailed_copy_of_responses';
export type PublishMode = 'manual' | 'scheduled';

export interface FormSettingsDTO {
  id: number;
  form_id: number;

  // Publishing
  publish_mode: PublishMode;
  is_published: boolean;
  open_at: string | null;
  close_at: string | null;
  max_submissions: number | null;

  // Sharing / Access
  sharing_type: SharingType;

  // Submissions
  allow_duplicate_responses: boolean;
  allow_response_editing: boolean;

  // Confirmation
  confirmation_email: ConfirmationEmailType;
  confirmation_message: string | null;

  created_at: string;
  updated_at: string;
}