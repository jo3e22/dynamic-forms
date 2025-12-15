import type {
  SubmissionDTO,
  SubmissionFieldDTO,
  Submission,
  SubmissionField,
} from '@/types/submissions';

export class SubmissionFieldModel {
  static fromDTO(dto: SubmissionFieldDTO): SubmissionField {
    return {
      id: dto.id,
      submission_id: dto.submission_id,
      field_id: dto.field_id,
      answer: dto.answer ? this.parseJSON(dto.answer) : null,
    };
  }

  static toDTO(field: SubmissionField): Partial<SubmissionFieldDTO> {
    return {
      id: field.id ?? undefined,
      submission_id: field.submission_id ?? undefined,
      field_id: field.field_id,
      answer: field.answer !== null ? JSON.stringify(field.answer) : null,
    };
  }

  private static parseJSON(value: string): any {
    try {
      return JSON.parse(value);
    } catch (e) {
      console.error('Failed to parse submission answer:', value, e);
      return null;
    }
  }

  static create(field_id: number): SubmissionField {
    return {
      id: null,
      submission_id: null,
      field_id,
      answer: null,
    };
  }
}

export class SubmissionModel {
  static fromDTO(dto: SubmissionDTO, fields: SubmissionFieldDTO[] = []): Submission {
    return {
      id: dto.id,
      code: dto.code,
      form_id: dto.form_id,
      user_id: dto.user_id,
      status: dto.status,
      email: dto.email,
      fields: fields.map(f => SubmissionFieldModel.fromDTO(f)),
    };
  }

  static toDTO(submission: Submission): Partial<SubmissionDTO> {
    return {
      id: submission.id,
      code: submission.code,
      form_id: submission.form_id,
      user_id: submission.user_id,
      status: submission.status,
      email: submission.email,
    };
  }
}