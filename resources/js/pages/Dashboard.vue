<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Badge } from '@/components/ui/badge';
import { Plus, Edit, FileText, BarChart3 } from 'lucide-vue-next';
import { computed } from 'vue';
import StatsCard from '@/components/common/StatsCard.vue';
import FormCard from '@/components/forms/FormCard.vue';
import EmptyState from '@/components/common/EmptyState.vue';
import DashboardContainer from '@/components/common/DashboardContainer.vue';

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
        title: 'Forms',
        href: '/forms',
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
</script>

<template>
    <Head title="Forms" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <!-- Stats Cards -->
            <div class="grid auto-rows-min gap-4 md:grid-cols-3">
                <StatsCard
                    title="Total Forms"
                    :value="forms.length"
                    :description="`${activeForms.length} active`"
                    :icon="FileText"
                />
                <StatsCard
                    title="Submissions"
                    :value="totalSubmissions"
                    description="Across all forms"
                    :icon="BarChart3"
                />
                <StatsCard
                    title="Drafts"
                    :value="draftForms.length"
                    description="Awaiting completion"
                    :icon="Edit"
                />
            </div>

            <!-- Forms List with Tabs -->
            <DashboardContainer 
                title="Forms"
                description="Manage your forms and submissions"
            >
                <template #actions>
                    <Button @click="createForm" class="gap-2">
                        <Plus :size="18" />
                        Create Form
                    </Button>
                </template>

                <!-- Empty State -->
                <EmptyState
                    v-if="forms.length === 0"
                    :icon="FileText"
                    title="No forms yet"
                    description="Get started by creating your first form"
                    action-label="Create Your First Form"
                    @action="createForm"
                >
                    <template #action-icon>
                        <Plus :size="20" />
                    </template>
                </EmptyState>

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
                        <FormCard
                            v-for="form in forms"
                            :key="form.id"
                            :form="form"
                            @edit="editForm"
                            @view-submissions="viewSubmissions"
                            @delete="deleteForm"
                        />
                    </TabsContent>

                    <!-- Active Forms -->
                    <TabsContent value="active" class="space-y-4">
                        <div v-if="activeForms.length === 0" class="text-center py-12 text-muted-foreground">
                            No active forms
                        </div>
                        <FormCard
                            v-else
                            v-for="form in activeForms"
                            :key="form.id"
                            :form="form"
                            @edit="editForm"
                            @view-submissions="viewSubmissions"
                            @delete="deleteForm"
                        />
                    </TabsContent>

                    <!-- Draft Forms -->
                    <TabsContent value="drafts" class="space-y-4">
                        <div v-if="draftForms.length === 0" class="text-center py-12 text-muted-foreground">
                            No draft forms
                        </div>
                        <FormCard
                            v-else
                            v-for="form in draftForms"
                            :key="form.id"
                            :form="form"
                            @edit="editForm"
                            @view-submissions="viewSubmissions"
                            @delete="deleteForm"
                        />
                    </TabsContent>

                    <!-- Closed Forms -->
                    <TabsContent value="closed" class="space-y-4">
                        <div v-if="closedForms.length === 0" class="text-center py-12 text-muted-foreground">
                            No closed forms
                        </div>
                        <FormCard
                            v-else
                            v-for="form in closedForms"
                            :key="form.id"
                            :form="form"
                            @edit="editForm"
                            @view-submissions="viewSubmissions"
                            @delete="deleteForm"
                        />
                    </TabsContent>
                </Tabs>
            </DashboardContainer>
        </div>
    </AppLayout>
</template>