import type {
  FormDTO,
  FormSectionDTO,
  FormFieldDTO,
  Form,
  FormSection,
  FormField,
  FormBuilderData,
  FormBuilderSection,
} from '@/types/forms';

export class FormFieldModel {
  static fromDTO(dto: FormFieldDTO): FormField {
    return {
      id: dto.id,
      form_id: dto.form_id,
      section_id: dto.section,
      label: dto.label,
      type: dto.type,
      options: dto.options ? this.parseJSON(dto.options) : null,
      required: dto.required,
      field_order: dto.field_order,
    };
  }

  static toDTO(field: FormField): Partial<FormFieldDTO> {
    return {
      id: field.id ?? undefined,
      form_id: field.form_id ?? undefined,
      section: field.section_id,
      label: field.label,
      type: field.type,
      options: field.options !== null ? JSON.stringify(field.options) : null,
      required: field.required,
      field_order: field.field_order,
    };
  }

  private static parseJSON(value: string): any {
    try {
      return JSON.parse(value);
    } catch (e) {
      console.error('Failed to parse field options:', value, e);
      return null;
    }
  }

  static create(section_id: number, field_order: number, type: FormField['type'] = 'short-answer'): FormField {
    return {
      id: null,
      form_id: null,
      section_id,
      label: '',
      type,
      options: null,
      required: false,
      field_order,
    };
  }
}

export class FormSectionModel {
  static fromDTO(dto: FormSectionDTO, fields: FormFieldDTO[] = []): FormSection {
    return {
      id: dto.id,
      form_id: dto.form_id,
      section_order: dto.section_order,
      title: dto.title,
      description: dto.description,
      settings: dto.settings,
      fields: fields.map(f => FormFieldModel.fromDTO(f)).sort((a, b) => a.field_order - b.field_order),
    };
  }

  static toDTO(section: FormSection): Partial<FormSectionDTO> {
    return {
      id: section.id ?? undefined,
      form_id: section.form_id ?? undefined,
      section_order: section.section_order,
      title: section.title,
      description: section.description,
      settings: section.settings,
    };
  }

  static create(form_id: number | null, section_order: number): FormSection {
    return {
      id: null,
      form_id,
      section_order,
      title: null,
      description: null,
      fields: [],
    };
  }
}

export class FormModel {
  static fromDTO(dto: FormDTO, sections: FormSectionDTO[] = [], fields: FormFieldDTO[] = []): Form {
    // Group fields by section
    const fieldsBySection = fields.reduce((acc, field) => {
      if (!acc[field.section]) acc[field.section] = [];
      acc[field.section].push(field);
      return acc;
    }, {} as Record<number, FormFieldDTO[]>);

    return {
      id: dto.id,
      code: dto.code,
      status: dto.status,
      user_id: dto.user_id,
      primary_color: dto.primary_color ?? '#000000',
      secondary_color: dto.secondary_color ?? '#ffffff',
      sections: sections
        .map(s => FormSectionModel.fromDTO(s, fieldsBySection[s.id] || []))
        .sort((a, b) => a.section_order - b.section_order),
    };
  }

  static toBuilderData(form: Form): FormBuilderData {
    return form.sections.map(section => ({
      id: section.id,
      section_order: section.section_order,
      titlesec: {
        title: section.title,
        description: section.description,
      },
      fields: section.fields,
    }));
  }

  static fromBuilderData(builderData: FormBuilderData, formId: number): FormSection[] {
    return builderData.map((section, index) => ({
      id: section.id,
      form_id: formId,
      section_order: index,
      title: section.titlesec.title,
      description: section.titlesec.description,
      fields: section.fields.map((field, fIndex) => ({
        ...field,
        section_id: section.id ?? 0,
        field_order: fIndex,
      })),
    }));
  }
}