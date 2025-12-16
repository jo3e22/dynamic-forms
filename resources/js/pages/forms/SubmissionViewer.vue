<script setup lang="ts">
import { ref, computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { ArrowLeft, User, Calendar, CheckCircle2, Clock, XCircle, List, LayoutGrid, Table, Users, FileText } from 'lucide-vue-next';
import type { FormDTO, FormBuilderData } from '@/types';
import StatsCard from '@/components/common/StatsCard.vue';
import SubmissionCard from '@/components/submissions/SubmissionCard.vue';
import EmptyState from '@/components/common/EmptyState.vue';
import DashboardContainer from '@/components/common/DashboardContainer.vue';
import { useFormStatus } from '@/composables/useFormStatus';
import { useDateTime } from '@/composables/useDateTime';

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
const { getStatusColor } = useFormStatus();
const { formatDate, formatDateTime, formatDateShort } = useDateTime();

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

// Computed stats
const completedCount = computed(() => 
  props.submissions.filter(s => s.status === 'complete').length
);

const incompleteCount = computed(() => 
  props.submissions.filter(s => s.status === 'incomplete').length
);

const breadcrumbs: BreadcrumbItem[] = props.selectedSubmission ? [
    {
        title: 'Forms',
        href: '/forms',
    },
    {
        title: formTitle.value,
        href: `/forms/${props.form.code}`,
    },
    {
        title: 'Submissions',
        href: `/forms/${props.form.code}/submissions`,
    },
    {
        title: `#${props.selectedSubmission.id}`,
        href: `/forms/${props.form.code}/submissions/${props.selectedSubmission.code}`,
    },
] : [
    {
        title: 'Forms',
        href: '/forms',
    },
    {
        title: formTitle.value,
        href: `/forms/${props.form.code}`,
    },
    {
        title: 'Submissions',
        href: `/forms/${props.form.code}/submissions`,
    },
];

// Status badge styling
function getStatusStyle(status: string) {
  const colors = {
    draft: 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-100',
    pending: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-100',
    open: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-100',
    closed: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-100',
    incomplete: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-100',
    complete: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-100',
  };
  return colors[status as keyof typeof colors] || colors.draft;
}


// View submission detail
function viewSubmission(submission: Submission) {
  router.visit(`/forms/${props.form.code}/submissions/${submission.code}`);
}

// Go back to list
function backToList() {
  router.visit(`/forms/${props.form.code}/submissions`);
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
  <Head :title="`Submissions - ${formTitle}`" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
      <!-- List View -->
      <div v-if="!selectedSubmission">
        <!-- Stats Cards -->
        <div class="grid auto-rows-min gap-4 md:grid-cols-3 mb-4">
          <StatsCard
            title="Total Submissions"
            :value="submissions.length"
            description="All responses"
            :icon="FileText"
          />
          <StatsCard
            title="Completed"
            :value="completedCount"
            description="Fully submitted"
            :icon="CheckCircle2"
          />
          <StatsCard
            title="In Progress"
            :value="incompleteCount"
            description="Incomplete forms"
            :icon="Clock"
          />
        </div>

        <!-- Submissions List -->
        <DashboardContainer
          title="Submissions"
          description="All responses to this form"
        >
          <template #actions>
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
          </template>

          <!-- Empty State -->
          <EmptyState
            v-if="submissions.length === 0"
            :icon="XCircle"
            title="No Submissions Yet"
            description="This form hasn't received any submissions yet."
          />

          <!-- Card View -->
          <div v-else-if="viewMode === 'cards'" class="space-y-3">
            <SubmissionCard
              v-for="submission in submissions"
              :key="submission.id"
              :submission="submission"
              @click="viewSubmission"
            />
          </div>

          <!-- Table View -->
          <div v-else class="rounded-lg border border-sidebar-border/70 overflow-hidden">
            <div class="overflow-x-auto">
              <table class="w-full">
                <thead class="bg-muted/50 border-b border-sidebar-border/70">
                  <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">
                      ID
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">
                      Submitter
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">
                      Date
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">
                      Status
                    </th>
                    <th 
                      v-for="field in allFields" 
                      :key="field.id"
                      class="px-4 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider min-w-[200px]"
                    >
                      <div class="flex flex-col">
                        <span>{{ field.label }}</span>
                        <span class="text-muted-foreground/70 font-normal normal-case text-xs">{{ field.sectionTitle }}</span>
                      </div>
                    </th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-sidebar-border/70">
                  <tr 
                    v-for="submission in submissions" 
                    :key="submission.id"
                    class="hover:bg-muted/50 cursor-pointer transition-colors"
                    @click="viewSubmission(submission)"
                  >
                    <td class="px-4 py-3 text-sm font-medium">
                      #{{ submission.id }}
                    </td>
                    <td class="px-4 py-3 text-sm">
                      {{ getSubmitterName(submission) }}
                    </td>
                    <td class="px-4 py-3 text-sm text-muted-foreground whitespace-nowrap">
                      {{ formatDateShort(submission.created_at) }}
                    </td>
                    <td class="px-4 py-3 text-sm">
                      <Badge :class="getStatusStyle(submission.status)">
                        {{ submission.status }}
                      </Badge>
                    </td>
                    <td 
                      v-for="field in allFields" 
                      :key="field.id"
                      class="px-4 py-3 text-sm"
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
        </DashboardContainer>
      </div>

      <!-- Detail View -->
      <DashboardContainer v-else>
        <template #header>
          <div class="flex items-start justify-between w-full">
            <div>
              <div class="flex items-center gap-3 mb-2">
                <h2 class="text-2xl font-bold">Submission #{{ selectedSubmission.id }}</h2>
                <Badge :class="getStatusStyle(selectedSubmission.status)">
                  {{ selectedSubmission.status }}
                </Badge>
              </div>
              <div class="flex flex-col gap-1 text-sm text-muted-foreground">
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
                  {{ formatDateTime(selectedSubmission.created_at) }}
                </span>
              </div>
            </div>
            <Button @click="backToList" variant="outline" class="gap-2">
              <List class="w-4 h-4" />
              All Submissions
            </Button>
          </div>
        </template>

        <div class="space-y-8">
          <!-- Iterate through sections -->
          <div 
            v-for="(section, sectionIndex) in data" 
            :key="section.id ?? sectionIndex"
            class="space-y-4"
          >
            <!-- Section Header -->
            <div class="border-l-4 pl-4 py-2 border-primary">
              <h3 class="text-xl font-semibold">{{ section.title }}</h3>
              <p v-if="section.description" class="text-sm text-muted-foreground mt-1">
                {{ section.description }}
              </p>
            </div>

            <!-- Fields and Answers -->
            <div class="ml-4 space-y-4">
              <div 
                v-for="field in section.fields" 
                :key="field.id"
                class="border-b border-sidebar-border/70 pb-4 last:border-0"
              >
                <div class="flex items-start justify-between gap-4">
                  <div class="flex-1">
                    <label class="block text-sm font-medium mb-2">
                      {{ field.label }}
                      <span v-if="field.required" class="text-destructive ml-1">*</span>
                    </label>
                    <div class="text-sm">
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
      </DashboardContainer>
    </div>
  </AppLayout>
</template>