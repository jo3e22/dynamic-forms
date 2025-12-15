<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Plus, Edit, Eye, Trash2, Calendar, FileText, BarChart3, CheckCircle2 } from 'lucide-vue-next';
import { computed } from 'vue';

interface Form {
  id: number;
  code: string;
  status: string;
  title: string;
  created_at: string;
  submissions_count?: number;
}

const props = defineProps<{
  forms: Form[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
];

const draftForms = computed(() => props.forms.filter(f => f.status === 'draft'));
const activeForms = computed(() => props.forms.filter(f => f.status === 'open'));
const closedForms = computed(() => props.forms.filter(f => f.status === 'closed'));

const totalSubmissions = computed(() => 
  props.forms.reduce((sum, form) => sum + (form.submissions_count || 0), 0)
);

function createForm() {
  router.visit('/forms/create');
}

function editForm(code: string) {
  router.visit(`/forms/${code}/edit`);
}

function viewSubmissions(code: string) {
  router.visit(`/forms/${code}/submissions`);
}

function deleteForm(code: string, title: string) {
  if (confirm(`Are you sure you want to delete "${title}"?`)) {
    router.delete(`/forms/${code}`, {
      onSuccess: () => {
        console.log('Form deleted');
      },
    });
  }
}

function getStatusColor(status: string) {
  const colors = {
    draft: 'bg-gray-100 text-gray-800',
    pending: 'bg-yellow-100 text-yellow-800',
    open: 'bg-green-100 text-green-800',
    closed: 'bg-red-100 text-red-800',
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
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <!-- Stats Cards -->
            <div class="grid auto-rows-min gap-4 md:grid-cols-3">
                <Card class="border-sidebar-border/70 dark:border-sidebar-border">
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Total Forms</CardTitle>
                        <FileText class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ forms.length }}</div>
                        <p class="text-xs text-muted-foreground">
                            {{ activeForms.length }} active
                        </p>
                    </CardContent>
                </Card>

                <Card class="border-sidebar-border/70 dark:border-sidebar-border">
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Submissions</CardTitle>
                        <BarChart3 class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ totalSubmissions }}</div>
                        <p class="text-xs text-muted-foreground">
                            Across all forms
                        </p>
                    </CardContent>
                </Card>

                <Card class="border-sidebar-border/70 dark:border-sidebar-border">
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Drafts</CardTitle>
                        <Edit class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ draftForms.length }}</div>
                        <p class="text-xs text-muted-foreground">
                            Awaiting completion
                        </p>
                    </CardContent>
                </Card>
            </div>

            <!-- Forms List with Tabs -->
            <div class="relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border bg-card">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h2 class="text-2xl font-bold">Forms</h2>
                            <p class="text-sm text-muted-foreground">Manage your forms and submissions</p>
                        </div>
                        <Button @click="createForm" class="gap-2">
                            <Plus :size="18" />
                            Create Form
                        </Button>
                    </div>

                    <!-- Empty State -->
                    <div v-if="forms.length === 0" class="text-center py-16">
                        <div class="w-20 h-20 rounded-full bg-muted mx-auto mb-4 flex items-center justify-center">
                            <FileText class="w-10 h-10 text-muted-foreground" />
                        </div>
                        <h3 class="text-xl font-semibold mb-2">No forms yet</h3>
                        <p class="text-muted-foreground mb-6">Get started by creating your first form</p>
                        <Button @click="createForm" size="lg" class="gap-2">
                            <Plus :size="20" />
                            Create Your First Form
                        </Button>
                    </div>

                    <!-- Forms Tabs -->
                    <Tabs v-else default-value="all">
                        <TabsList class="mb-6">
                            <TabsTrigger value="all">
                                All <Badge variant="secondary" class="ml-2">{{ forms.length }}</Badge>
                            </TabsTrigger>
                            <TabsTrigger value="active">
                                Active <Badge variant="secondary" class="ml-2">{{ activeForms.length }}</Badge>
                            </TabsTrigger>
                            <TabsTrigger value="drafts">
                                Drafts <Badge variant="secondary" class="ml-2">{{ draftForms.length }}</Badge>
                            </TabsTrigger>
                            <TabsTrigger value="closed">
                                Closed <Badge variant="secondary" class="ml-2">{{ closedForms.length }}</Badge>
                            </TabsTrigger>
                        </TabsList>

                        <!-- All Forms -->
                        <TabsContent value="all" class="space-y-4">
                            <Card 
                                v-for="form in forms" 
                                :key="form.id"
                                class="hover:shadow-md transition-shadow"
                            >
                                <CardHeader>
                                    <div class="flex items-start justify-between">
                                        <div class="space-y-1 flex-1">
                                            <CardTitle class="text-lg">{{ form.title }}</CardTitle>
                                            <CardDescription class="flex items-center gap-4 text-sm">
                                                <span class="flex items-center gap-1">
                                                    <Calendar :size="14" />
                                                    {{ formatDate(form.created_at) }}
                                                </span>
                                                <span v-if="form.submissions_count" class="flex items-center gap-1">
                                                    <CheckCircle2 :size="14" />
                                                    {{ form.submissions_count }} submission{{ form.submissions_count !== 1 ? 's' : '' }}
                                                </span>
                                            </CardDescription>
                                        </div>
                                        <Badge :class="getStatusColor(form.status)">
                                            {{ form.status }}
                                        </Badge>
                                    </div>
                                </CardHeader>
                                <CardContent>
                                    <div class="flex gap-2">
                                        <Button
                                            @click="editForm(form.code)"
                                            variant="default"
                                            size="sm"
                                            class="gap-2"
                                        >
                                            <Edit :size="16" />
                                            Edit
                                        </Button>
                                        <Button
                                            @click="viewSubmissions(form.code)"
                                            variant="outline"
                                            size="sm"
                                            class="gap-2"
                                        >
                                            <Eye :size="16" />
                                            Submissions
                                        </Button>
                                        <Button
                                            @click.stop="deleteForm(form.code, form.title)"
                                            variant="ghost"
                                            size="sm"
                                            class="gap-2 text-destructive hover:text-destructive"
                                        >
                                            <Trash2 :size="16" />
                                            Delete
                                        </Button>
                                    </div>
                                </CardContent>
                            </Card>
                        </TabsContent>

                        <!-- Active Forms -->
                        <TabsContent value="active" class="space-y-4">
                            <div v-if="activeForms.length === 0" class="text-center py-12 text-muted-foreground">
                                No active forms
                            </div>
                            <Card 
                                v-else
                                v-for="form in activeForms" 
                                :key="form.id"
                                class="hover:shadow-md transition-shadow"
                            >
                                <CardHeader>
                                    <div class="flex items-start justify-between">
                                        <div class="space-y-1 flex-1">
                                            <CardTitle class="text-lg">{{ form.title }}</CardTitle>
                                            <CardDescription class="flex items-center gap-4 text-sm">
                                                <span class="flex items-center gap-1">
                                                    <Calendar :size="14" />
                                                    {{ formatDate(form.created_at) }}
                                                </span>
                                                <span v-if="form.submissions_count" class="flex items-center gap-1">
                                                    <CheckCircle2 :size="14" />
                                                    {{ form.submissions_count }} submission{{ form.submissions_count !== 1 ? 's' : '' }}
                                                </span>
                                            </CardDescription>
                                        </div>
                                        <Badge :class="getStatusColor(form.status)">
                                            {{ form.status }}
                                        </Badge>
                                    </div>
                                </CardHeader>
                                <CardContent>
                                    <div class="flex gap-2">
                                        <Button
                                            @click="editForm(form.code)"
                                            variant="default"
                                            size="sm"
                                            class="gap-2"
                                        >
                                            <Edit :size="16" />
                                            Edit
                                        </Button>
                                        <Button
                                            @click="viewSubmissions(form.code)"
                                            variant="outline"
                                            size="sm"
                                            class="gap-2"
                                        >
                                            <Eye :size="16" />
                                            Submissions
                                        </Button>
                                        <Button
                                            @click.stop="deleteForm(form.code, form.title)"
                                            variant="ghost"
                                            size="sm"
                                            class="gap-2 text-destructive hover:text-destructive"
                                        >
                                            <Trash2 :size="16" />
                                            Delete
                                        </Button>
                                    </div>
                                </CardContent>
                            </Card>
                        </TabsContent>

                        <!-- Draft Forms -->
                        <TabsContent value="drafts" class="space-y-4">
                            <div v-if="draftForms.length === 0" class="text-center py-12 text-muted-foreground">
                                No draft forms
                            </div>
                            <Card 
                                v-else
                                v-for="form in draftForms" 
                                :key="form.id"
                                class="hover:shadow-md transition-shadow"
                            >
                                <CardHeader>
                                    <div class="flex items-start justify-between">
                                        <div class="space-y-1 flex-1">
                                            <CardTitle class="text-lg">{{ form.title }}</CardTitle>
                                            <CardDescription class="flex items-center gap-4 text-sm">
                                                <span class="flex items-center gap-1">
                                                    <Calendar :size="14" />
                                                    {{ formatDate(form.created_at) }}
                                                </span>
                                            </CardDescription>
                                        </div>
                                        <Badge :class="getStatusColor(form.status)">
                                            {{ form.status }}
                                        </Badge>
                                    </div>
                                </CardHeader>
                                <CardContent>
                                    <div class="flex gap-2">
                                        <Button
                                            @click="editForm(form.code)"
                                            variant="default"
                                            size="sm"
                                            class="gap-2"
                                        >
                                            <Edit :size="16" />
                                            Edit
                                        </Button>
                                        <Button
                                            @click="viewSubmissions(form.code)"
                                            variant="outline"
                                            size="sm"
                                            class="gap-2"
                                        >
                                            <Eye :size="16" />
                                            Submissions
                                        </Button>
                                        <Button
                                            @click.stop="deleteForm(form.code, form.title)"
                                            variant="ghost"
                                            size="sm"
                                            class="gap-2 text-destructive hover:text-destructive"
                                        >
                                            <Trash2 :size="16" />
                                            Delete
                                        </Button>
                                    </div>
                                </CardContent>
                            </Card>
                        </TabsContent>

                        <!-- Closed Forms -->
                        <TabsContent value="closed" class="space-y-4">
                            <div v-if="closedForms.length === 0" class="text-center py-12 text-muted-foreground">
                                No closed forms
                            </div>
                            <Card 
                                v-else
                                v-for="form in closedForms" 
                                :key="form.id"
                                class="hover:shadow-md transition-shadow"
                            >
                                <CardHeader>
                                    <div class="flex items-start justify-between">
                                        <div class="space-y-1 flex-1">
                                            <CardTitle class="text-lg">{{ form.title }}</CardTitle>
                                            <CardDescription class="flex items-center gap-4 text-sm">
                                                <span class="flex items-center gap-1">
                                                    <Calendar :size="14" />
                                                    {{ formatDate(form.created_at) }}
                                                </span>
                                                <span v-if="form.submissions_count" class="flex items-center gap-1">
                                                    <CheckCircle2 :size="14" />
                                                    {{ form.submissions_count }} submission{{ form.submissions_count !== 1 ? 's' : '' }}
                                                </span>
                                            </CardDescription>
                                        </div>
                                        <Badge :class="getStatusColor(form.status)">
                                            {{ form.status }}
                                        </Badge>
                                    </div>
                                </CardHeader>
                                <CardContent>
                                    <div class="flex gap-2">
                                        <Button
                                            @click="editForm(form.code)"
                                            variant="default"
                                            size="sm"
                                            class="gap-2"
                                        >
                                            <Edit :size="16" />
                                            Edit
                                        </Button>
                                        <Button
                                            @click="viewSubmissions(form.code)"
                                            variant="outline"
                                            size="sm"
                                            class="gap-2"
                                        >
                                            <Eye :size="16" />
                                            Submissions
                                        </Button>
                                        <Button
                                            @click.stop="deleteForm(form.code, form.title)"
                                            variant="ghost"
                                            size="sm"
                                            class="gap-2 text-destructive hover:text-destructive"
                                        >
                                            <Trash2 :size="16" />
                                            Delete
                                        </Button>
                                    </div>
                                </CardContent>
                            </Card>
                        </TabsContent>
                    </Tabs>
                </div>
            </div>
        </div>
    </AppLayout>
</template>