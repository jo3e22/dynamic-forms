<script setup lang="ts">
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { ArrowLeft, User, Calendar, CheckCircle2, Clock, XCircle, List, LayoutGrid, Table } from 'lucide-vue-next';
import type { FormDTO, FormBuilderData } from '@/types';

interface SubmissionField {
  id: number;
  form_field_id: number;
  answer: any;
}

interface Submission {
  id: number;
  code: string;
  status: string;
  user_id: number | null;
  email: string | null;
  created_at: string;
  updated_at: string;
  user?: {
    id: number;
    name: string;
    email: string;
  };
  submissionFields?: SubmissionField[];
}

interface Props {
  form: FormDTO;
  data: FormBuilderData;
  submissions: Submission[];
  selectedSubmission?: Submission;
}

const props = defineProps<Props>();

const form_primary_color = props.form.primary_color ?? '#3B82F6';
const form_secondary_color = props.form.secondary_color ?? '#EFF6FF';

// View mode: 'cards' or 'table'
const viewMode = ref<'cards' | 'table'>('cards');

// Get form title from first section
const formTitle = computed(() => {
  return props.data[0]?.title ?? 'Form';
});

// Get all form fields in order
const allFields = computed(() => {
  const fields: any[] = [];
  props.data.forEach(section => {
    section.fields.forEach(field => {
      fields.push({
        ...field,
        sectionTitle: section.title,
      });
    });
  });
  return fields;
});

// Status badge styling
function getStatusStyle(status: string) {
  const styles = {
    draft: { bg: 'bg-gray-100', text: 'text-gray-800', icon: Clock },
    pending: { bg: 'bg-yellow-100', text: 'text-yellow-800', icon: Clock },
    open: { bg: 'bg-blue-100', text: 'text-blue-800', icon: CheckCircle2 },
    closed: { bg: 'bg-green-100', text: 'text-green-800', icon: CheckCircle2 },
  };
  return styles[status as keyof typeof styles] || styles.draft;
}

// Format date
function formatDate(dateString: string): string {
  const date = new Date(dateString);
  return new Intl.DateTimeFormat('en-GB', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  }).format(date);
}

// Format date - short version for table
function formatDateShort(dateString: string): string {
  const date = new Date(dateString);
  return new Intl.DateTimeFormat('en-GB', {
    day: '2-digit',
    month: 'short',
    year: '2-digit',
  }).format(date);
}

// View submission detail
function viewSubmission(submission: Submission) {
  router.visit(`/forms/${props.form.code}/submissions/${submission.code}`);
}

// Go back to submissions list
function backToList() {
  router.visit(`/forms/${props.form.code}/submissions`);
}

// Go back to form list
function goBack() {
  router.visit('/forms');
}

// Get field label from form data
function getFieldLabel(fieldId: number): string {
  for (const section of props.data) {
    const field = section.fields.find(f => f.id === fieldId);
    if (field) return field.label;
  }
  return 'Unknown Field';
}

// Get field type from form data
function getFieldType(fieldId: number): string {
  for (const section of props.data) {
    const field = section.fields.find(f => f.id === fieldId);
    if (field) return field.type;
  }
  return 'text';
}

// Format answer based on field type
function formatAnswer(answer: any, fieldId: number): string {
  if (answer === null || answer === undefined || answer === '') {
    return '(No answer)';
  }
  
  const fieldType = getFieldType(fieldId);
  
  if (fieldType === 'checkbox' && Array.isArray(answer)) {
    return answer.join(', ');
  }
  
  if (typeof answer === 'object') {
    return JSON.stringify(answer);
  }
  
  return String(answer);
}

// Get answer for a specific submission and field
function getAnswer(submission: Submission, fieldId: number): string {
  const submissionField = submission.submissionFields?.find(sf => sf.form_field_id === fieldId);
  return formatAnswer(submissionField?.answer, fieldId);
}

// Get submitter name
function getSubmitterName(submission: Submission): string {
  if (submission.user) {
    return submission.user.name;
  }
  if (submission.email) {
    return submission.email;
  }
  return 'Anonymous';
}
</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div 
      class="w-full py-4 px-6 shadow-sm"
      :style="{ backgroundColor: form_primary_color }"
    >
      <div class="max-w-7xl mx-auto flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-semibold text-white">
            {{ formTitle }}
          </h1>
          <p class="text-sm text-white/80 mt-1">
            {{ selectedSubmission ? 'Submission Detail' : 'Submissions' }}
          </p>
        </div>
        <div class="flex gap-2">
          <Button
            v-if="selectedSubmission"
            variant="ghost"
            size="sm"
            @click="backToList"
            class="text-white hover:bg-white/10"
          >
            <List class="w-4 h-4 mr-2" />
            All Submissions
          </Button>
          <Button
            variant="ghost"
            size="sm"
            @click="goBack"
            class="text-white hover:bg-white/10"
          >
            <ArrowLeft class="w-4 h-4 mr-2" />
            Back to Forms
          </Button>
        </div>
      </div>
    </div>

    <div class="max-w-7xl mx-auto px-6 py-8">
      <!-- List View -->
      <div v-if="!selectedSubmission">
        <div class="mb-6 flex items-center justify-between">
          <h2 class="text-lg font-semibold text-gray-900">
            All Submissions ({{ submissions.length }})
          </h2>
          
          <!-- View Toggle -->
          <div v-if="submissions.length > 0" class="flex gap-2">
            <Button
              :variant="viewMode === 'cards' ? 'default' : 'outline'"
              size="sm"
              @click="viewMode = 'cards'"
            >
              <LayoutGrid class="w-4 h-4 mr-2" />
              Cards
            </Button>
            <Button
              :variant="viewMode === 'table' ? 'default' : 'outline'"
              size="sm"
              @click="viewMode = 'table'"
            >
              <Table class="w-4 h-4 mr-2" />
              Table
            </Button>
          </div>
        </div>

        <div v-if="submissions.length === 0" class="text-center py-16">
          <div 
            class="w-20 h-20 rounded-full mx-auto mb-4 flex items-center justify-center bg-gray-100"
          >
            <XCircle class="w-10 h-10 text-gray-400" />
          </div>
          <h3 class="text-xl font-semibold text-gray-900 mb-2">
            No Submissions Yet
          </h3>
          <p class="text-gray-600">
            This form hasn't received any submissions yet.
          </p>
        </div>

        <!-- Card View -->
        <div v-else-if="viewMode === 'cards'" class="grid gap-4">
          <Card 
            v-for="submission in submissions" 
            :key="submission.id"
            class="hover:shadow-md transition-shadow cursor-pointer"
            @click="viewSubmission(submission)"
          >
            <CardHeader>
              <div class="flex items-start justify-between">
                <div class="flex-1">
                  <div class="flex items-center gap-3 mb-2">
                    <CardTitle class="text-lg">
                      Submission #{{ submission.id }}
                    </CardTitle>
                    <Badge 
                      :class="`${getStatusStyle(submission.status).bg} ${getStatusStyle(submission.status).text}`"
                    >
                      {{ submission.status }}
                    </Badge>
                  </div>
                  <CardDescription class="flex flex-col gap-1">
                    <span v-if="submission.user" class="flex items-center gap-2">
                      <User class="w-4 h-4" />
                      {{ submission.user.name }} ({{ submission.user.email }})
                    </span>
                    <span v-else-if="submission.email" class="flex items-center gap-2">
                      <User class="w-4 h-4" />
                      {{ submission.email }}
                    </span>
                    <span class="flex items-center gap-2">
                      <Calendar class="w-4 h-4" />
                      {{ formatDate(submission.created_at) }}
                    </span>
                  </CardDescription>
                </div>
                <component 
                  :is="getStatusStyle(submission.status).icon" 
                  class="w-5 h-5"
                  :class="getStatusStyle(submission.status).text"
                />
              </div>
            </CardHeader>
          </Card>
        </div>

        <!-- Table View -->
        <div v-else class="bg-white rounded-lg border border-gray-200 overflow-hidden">
          <div class="overflow-x-auto">
            <table class="w-full">
              <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider sticky left-0 bg-gray-50 z-10">
                    ID
                  </th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                    Submitter
                  </th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                    Date
                  </th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                    Status
                  </th>
                  <th 
                    v-for="field in allFields" 
                    :key="field.id"
                    class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider min-w-[200px]"
                  >
                    <div class="flex flex-col">
                      <span>{{ field.label }}</span>
                      <span class="text-gray-500 font-normal normal-case text-xs">{{ field.sectionTitle }}</span>
                    </div>
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr 
                  v-for="submission in submissions" 
                  :key="submission.id"
                  class="hover:bg-gray-50 cursor-pointer"
                  @click="viewSubmission(submission)"
                >
                  <td class="px-4 py-3 text-sm font-medium text-gray-900 sticky left-0 bg-white group-hover:bg-gray-50">
                    #{{ submission.id }}
                  </td>
                  <td class="px-4 py-3 text-sm text-gray-900">
                    {{ getSubmitterName(submission) }}
                  </td>
                  <td class="px-4 py-3 text-sm text-gray-600 whitespace-nowrap">
                    {{ formatDateShort(submission.created_at) }}
                  </td>
                  <td class="px-4 py-3 text-sm">
                    <Badge 
                      :class="`${getStatusStyle(submission.status).bg} ${getStatusStyle(submission.status).text}`"
                    >
                      {{ submission.status }}
                    </Badge>
                  </td>
                  <td 
                    v-for="field in allFields" 
                    :key="field.id"
                    class="px-4 py-3 text-sm text-gray-900"
                  >
                    <div class="max-w-xs truncate" :title="getAnswer(submission, field.id)">
                      {{ getAnswer(submission, field.id) }}
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Detail View -->
      <div v-else>
        <Card>
          <CardHeader>
            <div class="flex items-start justify-between mb-4">
              <div>
                <div class="flex items-center gap-3 mb-2">
                  <CardTitle class="text-2xl">
                    Submission #{{ selectedSubmission.id }}
                  </CardTitle>
                  <Badge 
                    :class="`${getStatusStyle(selectedSubmission.status).bg} ${getStatusStyle(selectedSubmission.status).text}`"
                  >
                    {{ selectedSubmission.status }}
                  </Badge>
                </div>
                <CardDescription class="flex flex-col gap-1">
                  <span v-if="selectedSubmission.user" class="flex items-center gap-2">
                    <User class="w-4 h-4" />
                    {{ selectedSubmission.user.name }} ({{ selectedSubmission.user.email }})
                  </span>
                  <span v-else-if="selectedSubmission.email" class="flex items-center gap-2">
                    <User class="w-4 h-4" />
                    {{ selectedSubmission.email }}
                  </span>
                  <span class="flex items-center gap-2">
                    <Calendar class="w-4 h-4" />
                    Submitted: {{ formatDate(selectedSubmission.created_at) }}
                  </span>
                </CardDescription>
              </div>
            </div>
          </CardHeader>

          <CardContent>
            <div class="space-y-8">
              <!-- Iterate through sections -->
              <div 
                v-for="(section, sectionIndex) in data" 
                :key="section.id ?? sectionIndex"
                class="space-y-4"
              >
                <!-- Section Header -->
                <div 
                  class="border-l-4 pl-4 py-2"
                  :style="{ borderColor: form_primary_color }"
                >
                  <h3 class="text-xl font-semibold text-gray-900">
                    {{ section.title }}
                  </h3>
                  <p v-if="section.description" class="text-sm text-gray-600 mt-1">
                    {{ section.description }}
                  </p>
                </div>

                <!-- Fields and Answers -->
                <div class="ml-4 space-y-4">
                  <div 
                    v-for="field in section.fields" 
                    :key="field.id"
                    class="border-b border-gray-200 pb-4 last:border-0"
                  >
                    <div class="flex items-start justify-between gap-4">
                      <div class="flex-1">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                          {{ field.label }}
                          <span v-if="field.required" class="text-red-500 ml-1">*</span>
                        </label>
                        <div class="text-gray-900">
                          {{ formatAnswer(
                            selectedSubmission.submissionFields?.find(sf => sf.form_field_id === field.id)?.answer,
                            field.id
                          ) }}
                        </div>
                      </div>
                      <Badge variant="outline" class="text-xs">
                        {{ field.type }}
                      </Badge>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>
    </div>
  </div>
</template>