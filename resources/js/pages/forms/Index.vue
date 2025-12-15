<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Plus, Edit, Eye, Trash2, Calendar, FileText } from 'lucide-vue-next';

interface Form {
  id: number;
  code: string;
  status: string;
  title: string;
}

defineProps<{
  forms: Form[];
}>();

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
  <div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto py-8 px-6">
      <!-- Header -->
      <div class="mb-8">
        <div class="flex items-center justify-between mb-2">
          <div>
            <h1 class="text-4xl font-bold text-gray-900">Forms</h1>
            <p class="text-gray-600 mt-2">Create and manage your forms</p>
          </div>
          <Button @click="createForm" class="gap-2">
            <Plus :size="20" />
            Create New Form
          </Button>
        </div>
      </div>

      <!-- Empty State -->
      <div v-if="forms.length === 0" class="text-center py-20">
        <div class="w-24 h-24 rounded-full bg-blue-100 mx-auto mb-6 flex items-center justify-center">
          <FileText class="w-12 h-12 text-blue-600" />
        </div>
        <h3 class="text-2xl font-semibold text-gray-900 mb-2">No forms yet</h3>
        <p class="text-gray-600 mb-6">Get started by creating your first form</p>
        <Button @click="createForm" size="lg" class="gap-2">
          <Plus :size="20" />
          Create Your First Form
        </Button>
      </div>

      <!-- Forms Grid -->
      <div v-else class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
        <Card 
          v-for="form in forms" 
          :key="form.id"
          class="hover:shadow-lg transition-shadow cursor-pointer group"
        >
          <CardHeader>
            <div class="flex items-start justify-between mb-2">
              <CardTitle class="text-xl line-clamp-2">
                {{ form.title }}
              </CardTitle>
              <Badge :class="getStatusColor(form.status)">
                {{ form.status }}
              </Badge>
            </div>
            <CardDescription class="flex items-center gap-2 text-sm">
              <Calendar :size="14" />
              Form ID: {{ form.code.split('-')[0] }}...
            </CardDescription>
          </CardHeader>

          <CardContent>
            <div class="flex flex-col gap-2">
              <Button
                @click="editForm(form.code)"
                variant="default"
                class="w-full gap-2 justify-start"
              >
                <Edit :size="16" />
                Edit Form
              </Button>

              <Button
                @click="viewSubmissions(form.code)"
                variant="outline"
                class="w-full gap-2 justify-start"
              >
                <Eye :size="16" />
                View Submissions
              </Button>

              <Button
                @click.stop="deleteForm(form.code, form.title)"
                variant="ghost"
                class="w-full gap-2 justify-start text-red-600 hover:text-red-700 hover:bg-red-50"
              >
                <Trash2 :size="16" />
                Delete
              </Button>
            </div>
          </CardContent>
        </Card>
      </div>
    </div>
  </div>
</template>