<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Edit, Eye, Trash2, Calendar, FileText, BarChart3, CheckCircle2, Users, Clock } from 'lucide-vue-next';
import { computed } from 'vue';

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
        href: '/dashboard',
    },
    {
        title: props.form.title,
        href: `/forms/${props.form.code}`,
    },
];

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
        router.visit('/dashboard');
      },
    });
  }
}

function getStatusColor(status: string) {
  const colors = {
    draft: 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-100',
    pending: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-100',
    open: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-100',
    closed: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-100',
    incomplete: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-100',
    complete: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-100',
  };
  return colors[status as keyof typeof colors] || colors.draft;
}

function formatDate(date: string): string {
  return new Date(date).toLocaleDateString('en-GB', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
  });
}

function formatDateTime(date: string): string {
  return new Date(date).toLocaleString('en-GB', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  });
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
                <Card class="border-sidebar-border/70 dark:border-sidebar-border">
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Total Submissions</CardTitle>
                        <CheckCircle2 class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ form.submissions_count || 0 }}</div>
                        <p class="text-xs text-muted-foreground">
                            All time responses
                        </p>
                    </CardContent>
                </Card>

                <Card class="border-sidebar-border/70 dark:border-sidebar-border">
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Form Status</CardTitle>
                        <FileText class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold capitalize">{{ form.status }}</div>
                        <p class="text-xs text-muted-foreground">
                            Current state
                        </p>
                    </CardContent>
                </Card>

                <Card class="border-sidebar-border/70 dark:border-sidebar-border">
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Last Updated</CardTitle>
                        <Clock class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ formatDate(form.updated_at) }}</div>
                        <p class="text-xs text-muted-foreground">
                            Form modified
                        </p>
                    </CardContent>
                </Card>
            </div>

            <!-- Recent Submissions -->
            <div class="relative flex-1 rounded-xl border border-sidebar-border/70 dark:border-sidebar-border bg-card">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h2 class="text-2xl font-bold">Recent Submissions</h2>
                            <p class="text-sm text-muted-foreground">Latest responses to this form</p>
                        </div>
                        <Button @click="viewAllSubmissions" variant="outline" class="gap-2">
                            <Eye :size="18" />
                            View All
                        </Button>
                    </div>

                    <!-- Empty State -->
                    <div v-if="recentSubmissions.length === 0" class="text-center py-16">
                        <div class="w-20 h-20 rounded-full bg-muted mx-auto mb-4 flex items-center justify-center">
                            <CheckCircle2 class="w-10 h-10 text-muted-foreground" />
                        </div>
                        <h3 class="text-xl font-semibold mb-2">No submissions yet</h3>
                        <p class="text-muted-foreground mb-6">Responses will appear here once users submit the form</p>
                    </div>

                    <!-- Submissions List -->
                    <div v-else class="space-y-3">
                        <Card 
                            v-for="submission in recentSubmissions" 
                            :key="submission.id"
                            class="hover:shadow-md transition-shadow cursor-pointer"
                            @click="viewSubmission(submission.code)"
                        >
                            <CardHeader class="pb-3">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center">
                                            <Users :size="18" class="text-primary" />
                                        </div>
                                        <div>
                                            <CardTitle class="text-base">
                                                {{ submission.user?.name || 'Anonymous' }}
                                            </CardTitle>
                                            <CardDescription class="text-xs">
                                                {{ submission.user?.email || 'No email' }}
                                            </CardDescription>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <Badge :class="getStatusColor(submission.status)">
                                            {{ submission.status }}
                                        </Badge>
                                        <span class="text-xs text-muted-foreground">
                                            {{ formatDateTime(submission.created_at) }}
                                        </span>
                                    </div>
                                </div>
                            </CardHeader>
                        </Card>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>