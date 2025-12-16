<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Edit, Eye, Trash2, FileText, CheckCircle2, Clock } from 'lucide-vue-next';
import StatsCard from '@/components/common/StatsCard.vue';
import SubmissionCard from '@/components/submissions/SubmissionCard.vue';
import EmptyState from '@/components/common/EmptyState.vue';
import DashboardContainer from '@/components/common/DashboardContainer.vue';
import { useFormStatus } from '@/composables/useFormStatus';
import { useDateTime } from '@/composables/useDateTime';

interface Form {
  id: number;
  code: string;
  status: string;
  title: string;
  created_at: string;
  updated_at: string;
  submissions_count?: number;
}

interface Submission {
  id: number;
  code: string;
  status: string;
  created_at: string;
  user?: {
    name: string;
    email: string;
  };
}

const props = defineProps<{
  form: Form;
  recentSubmissions: Submission[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Forms',
        href: '/forms',
    },
    {
        title: props.form.title,
        href: `/forms/${props.form.code}`,
    },
];

const { getStatusColor } = useFormStatus();
const { formatDate } = useDateTime();

function editForm() {
  router.visit(`/forms/${props.form.code}/edit`);
}

function viewAllSubmissions() {
  router.visit(`/forms/${props.form.code}/submissions`);
}

function viewSubmission(code: string) {
  router.visit(`/forms/${props.form.code}/submissions/${code}`);
}

function deleteForm() {
  if (confirm(`Are you sure you want to delete "${props.form.title}"?`)) {
    router.delete(`/forms/${props.form.code}`, {
      onSuccess: () => {
        router.visit('/forms');
      },
    });
  }
}
</script>

<template>
    <Head :title="form.title" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <!-- Header -->
            <div class="flex items-start justify-between">
                <div>
                    <div class="flex items-center gap-3 mb-2">
                        <h1 class="text-3xl font-bold">{{ form.title }}</h1>
                        <Badge :class="getStatusColor(form.status)">
                            {{ form.status }}
                        </Badge>
                    </div>
                    <p class="text-sm text-muted-foreground">
                        Created {{ formatDate(form.created_at) }}
                    </p>
                </div>
                <div class="flex gap-2">
                    <Button @click="editForm" variant="default" class="gap-2">
                        <Edit :size="18" />
                        Edit Form
                    </Button>
                    <Button @click="deleteForm" variant="ghost" class="gap-2 text-destructive hover:text-destructive">
                        <Trash2 :size="18" />
                        Delete
                    </Button>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid auto-rows-min gap-4 md:grid-cols-3">
                <StatsCard
                    title="Total Submissions"
                    :value="form.submissions_count || 0"
                    description="All time responses"
                    :icon="CheckCircle2"
                />
                <StatsCard
                    title="Form Status"
                    :value="form.status"
                    description="Current state"
                    :icon="FileText"
                />
                <StatsCard
                    title="Last Updated"
                    :value="formatDate(form.updated_at)"
                    description="Form modified"
                    :icon="Clock"
                />
            </div>

            <!-- Recent Submissions -->
            <DashboardContainer
                title="Recent Submissions"
                description="Latest responses to this form"
            >
                <template #actions>
                    <Button @click="viewAllSubmissions" variant="outline" class="gap-2">
                        <Eye :size="18" />
                        View All
                    </Button>
                </template>

                <!-- Empty State -->
                <EmptyState
                    v-if="recentSubmissions.length === 0"
                    :icon="CheckCircle2"
                    title="No submissions yet"
                    description="Responses will appear here once users submit the form"
                />

                <!-- Submissions List -->
                <div v-else class="space-y-3">
                    <SubmissionCard
                        v-for="submission in recentSubmissions"
                        :key="submission.id"
                        :submission="submission"
                        @click="viewSubmission"
                    />
                </div>
            </DashboardContainer>
        </div>
    </AppLayout>
</template>