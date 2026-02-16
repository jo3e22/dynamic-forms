<script setup lang="ts">
import DashboardCard from '@/components/common/DashboardCard.vue';
import { CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Users } from 'lucide-vue-next';
import { useFormStatus } from '@/composables/useFormStatus';
import { useDateTime } from '@/composables/useDateTime';

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

interface Props {
  submission: Submission;
}

defineProps<Props>();

const emit = defineEmits<{
  click: [code: string];
}>();

const { getStatusColor } = useFormStatus();
const { formatDateTime } = useDateTime();
</script>

<template>
  <DashboardCard hover clickable @click="emit('click', submission.code)">
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
  </DashboardCard>
</template>