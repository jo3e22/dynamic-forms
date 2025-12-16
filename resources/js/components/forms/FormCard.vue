<script setup lang="ts">
import DashboardCard from '@/components/common/DashboardCard.vue';
import { CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Edit, Eye, Trash2, Calendar, CheckCircle2 } from 'lucide-vue-next';
import { useFormStatus } from '@/composables/useFormStatus';
import { useDateTime } from '@/composables/useDateTime';

interface Form {
  id: number;
  code: string;
  status: string;
  title: string;
  created_at: string;
  submissions_count?: number;
}

interface Props {
  form: Form;
}

defineProps<Props>();

const emit = defineEmits<{
  edit: [code: string];
  viewSubmissions: [code: string];
  delete: [code: string, title: string];
}>();

const { getStatusColor } = useFormStatus();
const { formatDate } = useDateTime();
</script>

<template>
  <DashboardCard hover>
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
          @click="emit('edit', form.code)"
          variant="default"
          size="sm"
          class="gap-2"
        >
          <Edit :size="16" />
          Edit
        </Button>
        <Button
          @click="emit('viewSubmissions', form.code)"
          variant="outline"
          size="sm"
          class="gap-2"
        >
          <Eye :size="16" />
          Submissions
        </Button>
        <Button
          @click="emit('delete', form.code, form.title)"
          variant="ghost"
          size="sm"
          class="gap-2 text-destructive hover:text-destructive"
        >
          <Trash2 :size="16" />
          Delete
        </Button>
      </div>
    </CardContent>
  </DashboardCard>
</template>