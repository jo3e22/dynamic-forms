import type {
  FormDTO,
  FormSectionDTO,
  FormFieldDTO,
  Form,
  FormSection,
  FormField,
  FormBuilderData,
  FormBuilderSection,
  FieldType,
  FormStatus
} from '@/types/forms';

export class FormFieldModel {
  /**
   * Convert backend DTO to frontend model
   */
  static fromDTO(dto: FormFieldDTO): FormField {
    return {
      id: dto.id,
      label: dto.label,
      type: dto.type,
      options: dto.options ? this.parseJSON(dto.options) : null,
      required: dto.required,
      field_order: dto.field_order,
    };
  }

  /**
   * Convert frontend model to backend DTO format
   */
  static toDTO(field: FormField, formId: number, sectionId: number): Partial<FormFieldDTO> {
    return {
      id: field.id ?? undefined,
      form_id: formId,
      section: sectionId,
      label: field.label,
      type: field.type,
      options: field.options !== null ? JSON.stringify(field.options) : null,
      required: field.required,
      field_order: field.field_order,
    };
  }

  /**
   * Parse JSON safely with error handling
   */
  private static parseJSON(value: string): any {
    try {
      return JSON.parse(value);
    } catch (e) {
      console.error('Failed to parse field options:', value, e);
      return null;
    }
  }

  /**
   * Create a new field instance
   */
  static create(type: FieldType = 'short-answer', field_order: number = 0): FormField {
    return {
      id: null,
      label: '',
      type,
      options: null,
      required: false,
      field_order,
    };
  }

  /**
   * Clone an existing field
   */
  static clone(field: FormField): FormField {
    return {
      ...field,
      id: null, // New field doesn't have an ID yet
    };
  }
}

export class FormSectionModel {
  /**
   * Convert backend DTO to frontend model (without fields)
   */
  static fromDTO(dto: FormSectionDTO): FormSection {
    return {
      id: dto.id,
      section_order: dto.section_order,
      title: dto.title,
      description: dto.description,
      settings: dto.settings,
      fields: [], // Fields will be added separately
    };
  }

  /**
   * Convert backend section DTO with field DTOs to full section model
   */
  static fromDTOWithFields(dto: FormSectionDTO, fieldDTOs: FormFieldDTO[]): FormSection {
    return {
      id: dto.id,
      section_order: dto.section_order,
      title: dto.title,
      description: dto.description,
      settings: dto.settings,
      fields: fieldDTOs
        .map(f => FormFieldModel.fromDTO(f))
        .sort((a, b) => a.field_order - b.field_order),
    };
  }

  /**
   * Convert frontend model to backend DTO format
   */
  static toDTO(section: FormSection, formId: number): Partial<FormSectionDTO> {
    return {
      id: section.id ?? undefined,
      form_id: formId,
      section_order: section.section_order,
      title: section.title,
      description: section.description,
      settings: section.settings,
    };
  }

  /**
   * Create a new section instance
   */
  static create(section_order: number = 0): FormSection {
    return {
      id: null,
      section_order,
      title: null,
      description: null,
      fields: [],
    };
  }

  /**
   * Add a field to a section
   */
  static addField(section: FormSection, field: FormField): void {
    field.field_order = section.fields.length;
    section.fields.push(field);
  }

  /**
   * Remove a field from a section and reorder remaining fields
   */
  static removeField(section: FormSection, fieldIndex: number): void {
    section.fields.splice(fieldIndex, 1);
    this.reorderFields(section);
  }

  /**
   * Move a field within a section
   */
  static moveField(section: FormSection, fromIndex: number, toIndex: number): void {
    if (toIndex < 0 || toIndex >= section.fields.length) return;
    
    const [movedField] = section.fields.splice(fromIndex, 1);
    section.fields.splice(toIndex, 0, movedField);
    this.reorderFields(section);
  }

  /**
   * Reorder all fields in a section based on their array index
   */
  private static reorderFields(section: FormSection): void {
    section.fields.forEach((field, index) => {
      field.field_order = index;
    });
  }
}

export class FormModel {
  /**
   * Convert full backend data to frontend Form model
   * This is the main conversion from Laravel to Vue
   */
  static fromBackendData(data: FormBuilderData): FormSection[] {
    return data.map((sectionData, index) => ({
      id: sectionData.id,
      section_order: index,
      title: sectionData.title,
      description: sectionData.description,
      fields: sectionData.fields.map((fieldData, fIndex) => ({
        id: fieldData.id,
        label: fieldData.label,
        type: fieldData.type,
        options: fieldData.options,
        required: fieldData.required,
        field_order: fIndex,
      })),
    }));
  }

  /**
   * Convert frontend sections to backend update format
   */
  static toBackendData(sections: FormSection[], formId: number): {
    sections: Partial<FormSectionDTO>[];
    fields: Partial<FormFieldDTO>[];
  } {
    const sectionDTOs: Partial<FormSectionDTO>[] = [];
    const fieldDTOs: Partial<FormFieldDTO>[] = [];

    sections.forEach((section, sIndex) => {
      section.section_order = sIndex;
      sectionDTOs.push(FormSectionModel.toDTO(section, formId));

      section.fields.forEach((field, fIndex) => {
        field.field_order = fIndex;
        fieldDTOs.push(FormFieldModel.toDTO(field, formId, section.id ?? 0));
      });
    });

    return { sections: sectionDTOs, fields: fieldDTOs };
  }

  /**
   * Create a new form with one empty section
   */
  static createEmpty(): FormSection[] {
    return [FormSectionModel.create(0)];
  }
}